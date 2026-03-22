<?php
declare(strict_types=1);

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear = $month === 12 ? $year + 1 : $year;

$listHeadline = match (true) {
    $search !== '' => 'Suche',
    $mode === 'timeline' => 'Timeline',
    $mode === 'slideshow' => 'Slideshow',
    $mode === 'category' => (string) ($selectedCategoryName ?? 'Kategorie'),
    default => 'Neueste Bilder',
};

$listText = match (true) {
    $search !== '' => count($activeList) . ' Treffer für „' . $search . '“ · neueste zuerst.',
    $mode === 'timeline' => 'Klassische vertikale Timeline mit allen Bildern. Es werden zuerst nur die sichtbaren Einträge geladen.',
    $mode === 'slideshow' => 'Große Kartenansicht mit den neuesten Einträgen.',
    $mode === 'category' => 'Bilder aus der gewählten Kategorie.',
    default => 'Schnellzugriff auf die letzten Einträge.',
};

$baseParams = [];
if ($mode !== 'home') {
    $baseParams['page'] = $mode;
}
if ($selectedCategory) {
    $baseParams['id'] = (int) $selectedCategory;
    $baseParams['kat'] = (int) $selectedCategory;
}
if ($search !== '' && $mode !== 'calendar') {
    $baseParams['suche'] = $search;
}
?>
<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error"><?= e((string) $flashError) ?></div>
<?php endif; ?>

<section class="hero-strip">
    <div>
        <h1>Bing bilder Bing Hintergründe</h1>
        <p><?= (int) $imageCount ?> Bilder<?= $selectedCategoryName ? ' · Filter: ' . e((string) $selectedCategoryName) : '' ?></p>
    </div>
</section>

<div class="nav-search-row">
    <nav class="link-nav navline">
        <a href="<?= e(url()) ?>" class="<?= $mode === 'home' ? 'is-active active' : '' ?>">Home</a>
        <a href="<?= e(url() . '?page=timeline') ?>" class="<?= $mode === 'timeline' ? 'is-active active' : '' ?>">Timeline</a>
        <a href="<?= e(url() . '?page=slideshow') ?>" class="<?= $mode === 'slideshow' ? 'is-active active' : '' ?>">Slideshow</a>
        <a href="<?= e(url() . '?page=calendar') ?>" class="<?= $mode === 'calendar' ? 'is-active active' : '' ?>">Kalenderblatt</a>

        <?php foreach ($categories as $category): ?>
            <a
                href="<?= e(url() . '?page=category&id=' . (int) $category['id'] . '&kat=' . (int) $category['id']) ?>"
                class="<?= $mode === 'category' && $selectedCategory === (int) $category['id'] ? 'is-active active' : '' ?>"
            >
                <?= e((string) $category['name']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($mode !== 'calendar'): ?>
        <form method="get" action="<?= e(url()) ?>" class="searchbar">
            <?php if ($mode !== 'home'): ?>
                <input type="hidden" name="page" value="<?= e($mode) ?>">
            <?php endif; ?>
            <?php if ($selectedCategory): ?>
                <input type="hidden" name="id" value="<?= (int) $selectedCategory ?>">
                <input type="hidden" name="kat" value="<?= (int) $selectedCategory ?>">
            <?php endif; ?>
            <input type="search" name="suche" value="<?= e($search) ?>" placeholder="Suche ...">
            <button type="submit">Suchen</button>
        </form>
    <?php endif; ?>
</div>

<?php if ($mode === 'calendar'): ?>
<section class="panel">
    <form method="get" action="<?= e(url()) ?>" class="filter-bar">
        <input type="hidden" name="page" value="calendar">

        <div class="field">
            <label for="kat">Kategorie</label>
            <select id="kat" name="kat">
                <option value="">Alle Kategorien</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $selectedCategory === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e((string) $category['name']) ?> (<?= (int) ($categoryCounts[(int) $category['id']] ?? 0) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="monat">Monat</label>
            <select id="monat" name="monat">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= e(german_month_name($m)) ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="field">
            <label for="jahr">Jahr</label>
            <input id="jahr" type="number" name="jahr" value="<?= (int) $year ?>" min="1970" max="2100">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn">Anzeigen</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="calendar-nav">
        <a class="btn btn-small" href="<?= e(url() . preserve_query(['monat' => $prevMonth, 'jahr' => $prevYear])) ?>">← Vorheriger Monat</a>
        <h2><?= e(german_month_name($month)) ?> <?= (int) $year ?></h2>
        <a class="btn btn-small" href="<?= e(url() . preserve_query(['monat' => $nextMonth, 'jahr' => $nextYear])) ?>">Nächster Monat →</a>
    </div>

    <?= $calendar ?>
</section>

<?php elseif ($mode === 'timeline'): ?>
<section class="panel">
    <div class="timeline-page-head">
        <h2>Timeline</h2>
        <p>Klassische vertikale Timeline mit allen Bildern. Es werden zuerst nur die sichtbaren Einträge geladen.</p>
    </div>

    <div
        class="timeline timeline-lazy"
        id="timeline"
        data-total="<?= (int) $timelineTotal ?>"
        data-feed-url="<?= e(url('timeline-feed') . preserve_query(['offset' => null, 'limit' => null])) ?>"
    ></div>

    <div class="timeline-status muted" id="timelineStatus">Lade Timeline …</div>
    <div id="timelineSentinel" aria-hidden="true"></div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timeline = document.getElementById('timeline');
    const status = document.getElementById('timelineStatus');
    const sentinel = document.getElementById('timelineSentinel');

    if (!timeline || !status || !sentinel) {
        return;
    }

    const feedUrl = timeline.getAttribute('data-feed-url') || '';
    const total = Number(timeline.getAttribute('data-total') || '0');

    let offset = 0;
    let loading = false;
    let hasMore = total > 0;
    let estimatedItemHeight = 360;

    function getScreenBatchSize() {
        const firstItem = timeline.querySelector('.timeline-item');

        if (firstItem) {
            const rect = firstItem.getBoundingClientRect();
            if (rect.height > 140) {
                estimatedItemHeight = rect.height + 24;
            }
        }

        const viewportHeight = window.innerHeight || 900;
        return Math.max(4, Math.ceil(viewportHeight / estimatedItemHeight) + 1);
    }

    function updateStatus() {
        if (total <= 0) {
            status.textContent = 'Keine Bilder gefunden.';
            return;
        }

        if (offset >= total) {
            status.textContent = 'Alle ' + total + ' Bilder geladen.';
            return;
        }

        status.textContent = offset + ' von ' + total + ' Bildern geladen.';
    }

    function loadMore(limit) {
        if (loading || !hasMore || !feedUrl) {
            return Promise.resolve();
        }

        loading = true;
        status.textContent = 'Lade weitere Bilder …';

        const separator = feedUrl.indexOf('?') === -1 ? '?' : '&';

        return fetch(feedUrl + separator + 'offset=' + encodeURIComponent(offset) + '&limit=' + encodeURIComponent(limit), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store'
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            if (!data || !data.ok) {
                throw new Error('Timeline konnte nicht geladen werden.');
            }

            if (data.html) {
                timeline.insertAdjacentHTML('beforeend', data.html);
            }

            offset = Number(data.next_offset || offset);
            hasMore = Boolean(data.has_more);
            updateStatus();
        })
        .catch(function () {
            status.textContent = 'Fehler beim Laden der Timeline.';
        })
        .finally(function () {
            loading = false;
        });
    }

    function maybeLoadMore() {
        if (!hasMore || loading) {
            return;
        }

        const rect = sentinel.getBoundingClientRect();
        if (rect.top <= window.innerHeight + 300) {
            loadMore(getScreenBatchSize());
        }
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                maybeLoadMore();
            }
        });
    }, {
        root: null,
        rootMargin: '400px 0px',
        threshold: 0
    });

    observer.observe(sentinel);
    window.addEventListener('scroll', maybeLoadMore, { passive: true });
    window.addEventListener('resize', maybeLoadMore);

    loadMore(getScreenBatchSize()).then(function () {
        maybeLoadMore();
    });
});
</script>

<?php elseif ($mode === 'slideshow'): ?>
<section class="panel">
    <div class="timeline-page-head">
        <h2>Slideshow</h2>
        <p><?= e($listText) ?></p>
    </div>

    <div class="slideshow-list">
        <?php foreach ($activeList as $image): ?>
            <?php create_thumbnail_if_needed((string) $image['name']); ?>
            <article class="slideshow-card">
                <a class="slideshow-image" href="<?= e(url('bild') . '?id=' . (int) $image['id']) ?>">
                    <img src="<?= e(thumb_url((string) $image['name'])) ?>" alt="<?= e((string) $image['name']) ?>">
                </a>
                <div class="slideshow-meta">
                    <div class="entry-date"><?= e(date('d.m.Y', (int) $image['entrytime'])) ?></div>
                    <h3><?= e((string) $image['name']) ?></h3>
                    <div class="entry-actions">
                        <a href="<?= e(url('bild') . '?id=' . (int) $image['id']) ?>">Details</a>
                        <a href="<?= e(image_url((string) $image['name'])) ?>" target="_blank" rel="noopener">Download</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php elseif ($mode === 'category' || $search !== ''): ?>
<section class="panel">
    <div class="timeline-page-head">
        <h2><?= e($listHeadline) ?></h2>
        <p><?= e($listText) ?></p>
    </div>

    <div class="gallery-grid">
        <?php foreach ($activeList as $image): ?>
            <?php create_thumbnail_if_needed((string) $image['name']); ?>
            <a class="gallery-card" href="<?= e(url('bild') . '?id=' . (int) $image['id']) ?>">
                <img src="<?= e(thumb_url((string) $image['name'])) ?>" alt="<?= e((string) $image['name']) ?>">
                <span><?= e(date('d.m.Y', (int) $image['entrytime'])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php else: ?>
<section class="panel">
    <div class="panel-header">
        <div>
            <h2>Neueste Bilder</h2>
            <p>Schnellzugriff auf die letzten Einträge.</p>
        </div>
    </div>

    <div class="gallery-grid">
        <?php foreach ($activeList as $image): ?>
            <?php create_thumbnail_if_needed((string) $image['name']); ?>
            <a class="gallery-card" href="<?= e(url('bild') . '?id=' . (int) $image['id']) ?>">
                <img src="<?= e(thumb_url((string) $image['name'])) ?>" alt="<?= e((string) $image['name']) ?>">
                <span><?= e(date('d.m.Y', (int) $image['entrytime'])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>