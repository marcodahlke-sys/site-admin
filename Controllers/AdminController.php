<?php
declare(strict_types=1);

class AdminController extends App\Core\Controller
{
    public function dashboard(): void
    {
        $this->requireAdmin();

        $month = current_month();
        $year = current_year();

        $categoryModel = new Category($this->db);
        $imageModel = new Image($this->db);

        $calendar = (new CalendarBuilder(
            $imageModel,
            new Tag($this->db),
            new Description($this->db),
            new Like($this->db),
            $categoryModel
        ))->build($month, $year, true, null);

        $this->view('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'flashSuccess' => flash('success'),
            'flashError' => flash('error'),
            'month' => $month,
            'year' => $year,
            'calendar' => $calendar,
            'counterValue' => (new Counter($this->db))->getCount(),
            'categories' => $categoryModel->all(),
            'user' => $this->auth->user(),
        ]);
    }

    public function uploadForm(): void
    {
        $this->requireAdmin();

        $datum = (int) query('datum', now());

        $this->view('admin/upload', [
            'pageTitle' => 'Bild hochladen',
            'flashSuccess' => flash('success'),
            'flashError' => flash('error'),
            'categories' => (new Category($this->db))->all(),
            'defaultDate' => date('Y-m-d', $datum),
        ]);
    }

    public function upload(): void
    {
        $this->requireAdmin();
        verify_csrf();

        $date = (string) input('date', '');
        $time = trim((string) input('time', '12:00'));
        $mainCategory = (int) input('to_kat', 0);
        $extraCategories = selected_categories_from_post();
        $description = trim((string) input('beschreibung', ''));
        $tagsInput = trim((string) input('tags', ''));

        with_old($_POST);

        if ($date === '' || $mainCategory <= 0) {
            $this->session->flash('error', 'Datum und Hauptkategorie sind erforderlich.');
            $this->redirect('/admin/upload');
        }

        $entrytime = strtotime($date . ' ' . ($time !== '' ? $time : '12:00') . ':00');

        if ($entrytime === false) {
            $this->session->flash('error', 'Ungültiges Datum oder Uhrzeit.');
            $this->redirect('/admin/upload');
        }

        try {
            $imageModel = new Image($this->db);
            $imageId = $imageModel->createFromUpload($_FILES['image'] ?? [], $entrytime, $mainCategory, $extraCategories);

            (new Description($this->db))->upsert($imageId, $description);

            $tags = array_filter(array_map('trim', preg_split('/[,;\n]+/', $tagsInput) ?: []));
            (new Tag($this->db))->replaceForImage($imageId, $tags);

            clear_old();
            $this->session->flash('success', 'Bild erfolgreich hochgeladen.');
            $this->redirect('/admin');
        } catch (RuntimeException $e) {
            $this->session->flash('error', $e->getMessage());
            $this->redirect('/admin/upload');
        }
    }

    public function editForm(): void
    {
        $this->requireAdmin();

        $id = (int) query('id', 0);
        $imageModel = new Image($this->db);
        $image = $imageModel->getById($id);

        if (!$image) {
            $this->session->flash('error', 'Bild nicht gefunden.');
            $this->redirect('/admin');
        }

        create_thumbnail_if_needed((string) $image['name']);

        $tags = (new Tag($this->db))->forImage($id);
        $description = (new Description($this->db))->getByImageId($id);

        $this->view('admin/edit', [
            'pageTitle' => 'Bild bearbeiten',
            'flashSuccess' => flash('success'),
            'flashError' => flash('error'),
            'image' => $image,
            'categories' => (new Category($this->db))->all(),
            'selectedCategories' => $imageModel->categoriesForImage($image),
            'tagsString' => implode(', ', array_map(static fn ($tag) => $tag['tag'], $tags)),
            'descriptionText' => $description['beschreibung'] ?? '',
            'dateValue' => date('Y-m-d', (int) $image['entrytime']),
            'timeValue' => date('H:i', (int) $image['entrytime']),
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        verify_csrf();

        $id = (int) input('id', 0);
        $date = (string) input('date', '');
        $time = trim((string) input('time', '12:00'));
        $mainCategory = (int) input('to_kat', 0);
        $extraCategories = selected_categories_from_post();
        $description = trim((string) input('beschreibung', ''));
        $tagsInput = trim((string) input('tags', ''));

        if ($id <= 0 || $date === '' || $mainCategory <= 0) {
            $this->session->flash('error', 'Ungültige Eingaben.');
            $this->redirect('/admin');
        }

        $entrytime = strtotime($date . ' ' . ($time !== '' ? $time : '12:00') . ':00');

        if ($entrytime === false) {
            $this->session->flash('error', 'Ungültiges Datum oder Uhrzeit.');
            $this->redirect('/admin/edit?id=' . $id);
        }

        try {
            $imageModel = new Image($this->db);
            $imageModel->updateImage($id, $entrytime, $mainCategory, $extraCategories);

            (new Description($this->db))->upsert($id, $description);

            $tags = array_filter(array_map('trim', preg_split('/[,;\n]+/', $tagsInput) ?: []));
            (new Tag($this->db))->replaceForImage($id, $tags);

            $this->session->flash('success', 'Bild erfolgreich aktualisiert.');
            $this->redirect('/admin');
        } catch (RuntimeException $e) {
            $this->session->flash('error', $e->getMessage());
            $this->redirect('/admin/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->requireAdmin();
        verify_csrf();

        $id = (int) input('id', 0);

        if ($id <= 0) {
            $this->session->flash('error', 'Bild nicht gefunden.');
            $this->redirect('/admin');
        }

        (new Tag($this->db))->replaceForImage($id, []);
        (new Description($this->db))->deleteByImageId($id);
        (new Image($this->db))->deleteImage($id);

        $stmt = $this->db->pdo()->prepare('DELETE FROM likes WHERE datei_id = :id');
        $stmt->execute(['id' => $id]);

        $this->session->flash('success', 'Bild erfolgreich gelöscht.');
        $this->redirect('/admin');
    }
}