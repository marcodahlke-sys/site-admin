<?php
declare(strict_types=1);

class CalendarBuilder
{
    public function __construct(
        private Image $images,
        private Tag $tags,
        private Description $descriptions,
        private Like $likes,
        private Category $categories
    ) {
    }

    public function build(int $month, int $year, bool $adminMode = false, ?int $categoryFilter = null): string
    {
        $dayNames = weekday_short_names();
        $today = (int) date('d');
        $thisMonth = (int) date('n');
        $thisYear = (int) date('Y');

        $firstDayWeekIndex = (int) date('N', mktime(0, 0, 0, $month, 1, $year)) - 1;
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));

        $monthImages = $this->images->getByMonth($month, $year, $categoryFilter);
        $byDay = [];

        foreach ($monthImages as $image) {
            $day = (int) date('j', (int) $image['entrytime']);
            $byDay[$day][] = $image;
        }

        $categories = $this->categories->indexed();

        $html = '<div class="calendar">';
        $html .= '<div class="calendar-header">';

        foreach ($dayNames as $dayName) {
            $html .= '<div class="calendar-weekday">' . e($dayName) . '</div>';
        }

        $html .= '</div>';
        $html .= '<div class="calendar-grid">';

        for ($i = 0; $i < $firstDayWeekIndex; $i++) {
            $html .= '<div class="calendar-day calendar-day-empty"></div>';
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $isToday = ($day === $today && $month === $thisMonth && $year === $thisYear);
            $html .= '<div class="calendar-day' . ($isToday ? ' is-today' : '') . '">';
            $html .= '<div class="calendar-day-number">' . sprintf('%02d', $day) . '</div>';

            $items = $byDay[$day] ?? [];

            if ($items) {
                foreach ($items as $image) {
                    $filename = (string) $image['name'];
                    create_thumbnail_if_needed($filename);

                    $imageId = (int) $image['id'];
                    $sizeInfo = @getimagesize(uploads_path($filename));
                    $fileSize = is_file(uploads_path($filename)) ? (int) filesize(uploads_path($filename)) : 0;
                    $time = date('H:i:s', (int) $image['entrytime']);
                    $date = date('d.m.Y', (int) $image['entrytime']);

                    $imageCategories = [];
                    foreach ($this->images->categoriesForImage($image) as $catId) {
                        $imageCategories[] = $categories[$catId] ?? ('Kategorie ' . $catId);
                    }

                    $description = $this->descriptions->getByImageId($imageId);
                    $tags = $this->tags->forImage($imageId);
                    $likes = $this->likes->countByImage($imageId);

                    $html .= '<div class="calendar-card">';
                    $html .= '<a class="calendar-thumb-link" href="/bild?id=' . $imageId . '">';
                    $html .= '<img class="calendar-thumb" src="' . e(thumb_url($filename)) . '" alt="' . e($filename) . '">';
                    $html .= '</a>';
                    $html .= '<div class="calendar-meta"><strong>Name:</strong><br>' . e($filename) . '</div>';
                    $html .= '<div class="calendar-meta"><strong>Kategorien:</strong><br>' . e(implode(', ', $imageCategories)) . '</div>';
                    $html .= '<div class="calendar-meta"><strong>Größe:</strong><br>' . e(format_bytes($fileSize)) . '</div>';
                    $html .= '<div class="calendar-meta"><strong>Bildbreite:</strong><br>' . e((string) ($sizeInfo[0] ?? 0)) . ' Pixel</div>';
                    $html .= '<div class="calendar-meta"><strong>Bildhöhe:</strong><br>' . e((string) ($sizeInfo[1] ?? 0)) . ' Pixel</div>';
                    $html .= '<div class="calendar-meta"><strong>Datum:</strong><br>' . e($date) . '</div>';
                    $html .= '<div class="calendar-meta"><strong>Zeit:</strong><br>' . e($time) . '</div>';

                    if (!empty($description['beschreibung'])) {
                        $html .= '<div class="calendar-meta"><strong>Beschreibung:</strong><br>' . nl2br(e((string) $description['beschreibung'])) . '</div>';
                    }

                    if ($tags) {
                        $html .= '<div class="tag-list">';
                        foreach ($tags as $tag) {
                            $html .= '<span class="tag-chip">' . e((string) $tag['tag']) . '</span>';
                        }
                        $html .= '</div>';
                    }

                    $html .= '<div class="like-count">Likes: ' . $likes . '</div>';

                    if ($adminMode) {
                        $currentMonth = current_month();
                        $currentYear = current_year();

                        $html .= '<div class="admin-actions">';
                        $html .= '<a class="btn btn-small" href="/admin/edit?id=' . $imageId . '&monat=' . $currentMonth . '&jahr=' . $currentYear . '">Bearbeiten</a>';
                        $html .= '<form method="post" action="/admin/delete" onsubmit="return confirm(\'Bild wirklich löschen?\');">';
                        $html .= csrf_field();
                        $html .= '<input type="hidden" name="id" value="' . $imageId . '">';
                        $html .= '<button class="btn btn-small btn-danger" type="submit">Löschen</button>';
                        $html .= '</form>';
                        $html .= '</div>';
                    }

                    $html .= '</div>';
                }
            } elseif ($adminMode) {
                $dateStart = mktime(0, 0, 0, $month, $day, $year);
                $html .= '<div class="calendar-empty-action">';
                $html .= '<a class="btn btn-small" href="/admin/upload?datum=' . $dateStart . '">Bild hochladen</a>';
                $html .= '</div>';
            } else {
                $html .= '<div class="calendar-no-entry">Kein Bild vorhanden</div>';
            }

            $html .= '</div>';
        }

        $filledCells = $firstDayWeekIndex + $daysInMonth;
        $remaining = (7 - ($filledCells % 7)) % 7;

        for ($i = 0; $i < $remaining; $i++) {
            $html .= '<div class="calendar-day calendar-day-empty"></div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}