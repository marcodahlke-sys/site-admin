<?php
declare(strict_types=1);

class HomeController extends App\Core\Controller
{
    public function index(): void
    {
        (new Counter($this->db))->increment();

        $month = current_month();
        $year = current_year();
        $categoryId = (int) query('kat', (int) query('id', 0));
        $categoryId = $categoryId > 0 ? $categoryId : null;
        $mode = (string) query('page', 'home');
        $mode = in_array($mode, ['home', 'timeline', 'slideshow', 'category', 'calendar'], true) ? $mode : 'home';
        $search = trim((string) query('suche', ''));

        $categoryModel = new Category($this->db);
        $imageModel = new Image($this->db);
        $tagModel = new Tag($this->db);
        $descriptionModel = new Description($this->db);
        $likeModel = new Like($this->db);

        if ($mode === 'category' && $categoryId === null) {
            $mode = 'home';
        }

        $calendar = (new CalendarBuilder(
            $imageModel,
            $tagModel,
            $descriptionModel,
            $likeModel,
            $categoryModel
        ))->build($month, $year, false, $categoryId);

        $selectedCategoryData = $categoryId ? $categoryModel->find($categoryId) : null;
        $selectedCategoryName = $selectedCategoryData['name'] ?? null;

        $latest = $imageModel->latest(24, $categoryId);
        $slideshow = $imageModel->timelinePaged(0, 18, $categoryId, $search);
        $activeList = match (true) {
            $search !== '' => $imageModel->timelinePaged(0, 24, $categoryId, $search),
            $mode === 'home' => $latest,
            $mode === 'slideshow' => $slideshow,
            $mode === 'category' => $imageModel->timelinePaged(0, 24, $categoryId, ''),
            default => [],
        };

        $categoryCounts = [];
        foreach ($categoryModel->all() as $category) {
            $id = (int) $category['id'];
            $categoryCounts[$id] = $imageModel->countByCategory($id);
        }

        $timelineTotal = $imageModel->countTimeline($categoryId, $search);

        $this->view('home/index', [
            'pageTitle' => match ($mode) {
                'timeline' => 'Timeline',
                'slideshow' => 'Slideshow',
                'calendar' => 'Kalenderblatt',
                'category' => (string) ($selectedCategoryName ?? 'Kategorie'),
                default => 'Bing bilder',
            },
            'categories' => $categoryModel->all(),
            'categoryCounts' => $categoryCounts,
            'selectedCategory' => $categoryId,
            'selectedCategoryName' => $selectedCategoryName,
            'month' => $month,
            'year' => $year,
            'mode' => $mode,
            'search' => $search,
            'calendar' => $calendar,
            'latest' => $latest,
            'activeList' => $activeList,
            'timelineTotal' => $timelineTotal,
            'imageCount' => $imageModel->countAll(),
            'flashSuccess' => flash('success'),
            'flashError' => flash('error'),
        ]);
    }

    public function timelineFeed(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $offset = max(0, (int) query('offset', 0));
        $limit = max(1, min(100, (int) query('limit', 12)));
        $categoryId = (int) query('kat', (int) query('id', 0));
        $categoryId = $categoryId > 0 ? $categoryId : null;
        $search = trim((string) query('suche', ''));

        $imageModel = new Image($this->db);
        $items = $imageModel->timelinePaged($offset, $limit, $categoryId, $search);
        $total = $imageModel->countTimeline($categoryId, $search);

        $html = '';
        foreach ($items as $image) {
            create_thumbnail_if_needed((string) $image['name']);

            $description = trim((string) ($image['beschreibung'] ?? ''));
            $categoryName = trim((string) ($image['category_name'] ?? ''));
            $detailUrl = url('bild') . '?id=' . (int) $image['id'];
            $downloadUrl = image_url((string) $image['name']);

            $html .= '<article class="timeline-item">';
            $html .= '<div class="timeline-date">';
            $html .= '<span class="date-badge">' . e(date('d.m.Y', (int) $image['entrytime'])) . '</span>';
            $html .= '</div>';

            $html .= '<div class="timeline-dot" aria-hidden="true"></div>';

            $html .= '<div class="timeline-card">';
            $html .= '<div class="timeline-card-inner">';

            $html .= '<a class="timeline-thumb-link" href="' . e($detailUrl) . '">';
            $html .= '<img class="timeline-thumb" src="' . e(thumb_url((string) $image['name'])) . '" alt="' . e((string) $image['name']) . '" loading="lazy">';
            $html .= '</a>';

            $html .= '<div class="timeline-content">';
            $html .= '<h2 class="timeline-title"><a href="' . e($detailUrl) . '">' . e((string) $image['name']) . '</a></h2>';

            if ($categoryName !== '') {
                $html .= '<p class="muted timeline-category-text">' . e($categoryName) . '</p>';
            }

            if ($description !== '') {
                $html .= '<div class="timeline-desc">' . nl2br(e($description)) . '</div>';
            }

            $html .= '<div class="entry-actions timeline-actions">';
            $html .= '<a href="' . e($detailUrl) . '">Details</a>';
            $html .= '<a href="' . e($downloadUrl) . '" target="_blank" rel="noopener">Download</a>';
            $html .= '</div>';

            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</article>';
        }

        echo json_encode([
            'ok' => true,
            'html' => $html,
            'next_offset' => $offset + count($items),
            'has_more' => ($offset + count($items)) < $total,
            'total' => $total,
        ], JSON_UNESCAPED_UNICODE);

        exit;
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