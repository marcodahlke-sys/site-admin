<?php
declare(strict_types=1);

class HomeController extends App\Core\Controller
{
    public function index(): void
    {
        (new Counter($this->db))->increment();

        $month = current_month();
        $year = current_year();
        $categoryId = (int) query('kat', 0);
        $categoryId = $categoryId > 0 ? $categoryId : null;

        $categoryModel = new Category($this->db);
        $imageModel = new Image($this->db);

        $calendar = (new CalendarBuilder(
            $imageModel,
            new Tag($this->db),
            new Description($this->db),
            new Like($this->db),
            $categoryModel
        ))->build($month, $year, false, $categoryId);

        $latest = $imageModel->latest(24, $categoryId);

        $this->view('home/index', [
            'pageTitle' => 'Bilder-Webseite',
            'categories' => $categoryModel->all(),
            'selectedCategory' => $categoryId,
            'month' => $month,
            'year' => $year,
            'calendar' => $calendar,
            'latest' => $latest,
            'flashSuccess' => flash('success'),
            'flashError' => flash('error'),
        ]);
    }

    public function show(): void
    {
        (new Counter($this->db))->increment();

        $id = (int) query('id', 0);

        if ($id <= 0) {
            $this->session->flash('error', 'Bild nicht gefunden.');
            $this->redirect('/');
        }

        $imageModel = new Image($this->db);
        $image = $imageModel->getById($id);

        if (!$image) {
            $this->session->flash('error', 'Bild nicht gefunden.');
            $this->redirect('/');
        }

        create_thumbnail_if_needed((string) $image['name']);

        $description = (new Description($this->db))->getByImageId($id);
        $tags = (new Tag($this->db))->forImage($id);
        $likeModel = new Like($this->db);
        $likes = $likeModel->countByImage($id);
        $liked = $likeModel->existsForImageAndIp($id, client_ip());

        $categories = (new Category($this->db))->indexed();
        $imageCategories = array_map(
            static fn (int $catId): string => $categories[$catId] ?? ('Kategorie ' . $catId),
            $imageModel->categoriesForImage($image)
        );

        $this->view('home/show', [
            'pageTitle' => 'Bild ansehen',
            'image' => $image,
            'description' => $description,
            'tags' => $tags,
            'likes' => $likes,
            'liked' => $liked,
            'imageCategories' => $imageCategories,
            'flashSuccess' => flash('success'),
            'flashError' => flash('error'),
        ]);
    }
}