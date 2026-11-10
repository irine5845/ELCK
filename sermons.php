
<?php
require_once 'config/database.php';

$page_title = 'Sermons | ELCK KISUMU';

/**
 * Convert supported YouTube links into embed URLs.
 */
function getYouTubeEmbedUrl($url)
{
    $url = trim($url);

    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{11})~', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    return '';
}

$stmt = $pdo->query("
    SELECT *
    FROM sermons
    ORDER BY sermon_date DESC, id DESC
");

$sermons = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">GROWING IN THE WORD</span>
        <h1>Our Sermons</h1>
        <p>Listen, learn and grow through the teaching of God's Word.</p>
    </div>
</section>

<!-- SERMONS SECTION -->
<section class="sermons-page-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">WATCH & LISTEN</span>
            <h2 class="section-title">Messages of Faith and Hope</h2>
            <p>
                Explore our collection of sermons and receive encouragement
                through God's Word.
            </p>
        </div>

        <div class="row g-4">

            <?php if (!empty($sermons)): ?>

                <?php foreach ($sermons as $sermon): ?>

                    <?php
                    $embedUrl = getYouTubeEmbedUrl(
                        $sermon['video_url'] ?? ''
                    );
                    ?>

                    <div class="col-lg-4 col-md-6">
                        <article class="sermon-card">

                            <?php if ($embedUrl): ?>

                                <div class="sermon-video">
                                    <iframe
                                        src="<?= htmlspecialchars($embedUrl) ?>"
                                        title="<?= htmlspecialchars($sermon['title']) ?>"
                                        loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen>
                                    </iframe>
                                </div>

                            <?php else: ?>

                                <div class="sermon-video-placeholder">
                                    <span>✝</span>
                                    <p>Video coming soon</p>
                                </div>

                            <?php endif; ?>

                            <div class="sermon-content">

                                <span class="sermon-category">
                                    SERMON MESSAGE
                                </span>

                                <h3>
                                    <?= htmlspecialchars($sermon['title']) ?>
                                </h3>

                                <?php if (!empty($sermon['preacher'])): ?>
                                    <p class="sermon-preacher">
                                        <strong>Preacher:</strong>
                                        <?= htmlspecialchars($sermon['preacher']) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($sermon['sermon_date'])): ?>
                                    <p class="sermon-date">
                                        <span>📅</span>
                                        <?= date(
                                            'F j, Y',
                                            strtotime($sermon['sermon_date'])
                                        ) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($sermon['description'])): ?>
                                    <p class="sermon-description">
                                        <?= nl2br(htmlspecialchars($sermon['description'])) ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </article>
                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">
                    <div class="sermons-empty">
                        <div class="empty-icon">✝</div>
                        <h3>No Sermons Available Yet</h3>
                        <p>
                            Our sermons will be published here soon.
                            Please check back for inspiring messages.
                        </p>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="sermon-cta">
    <div class="container text-center">
        <span class="section-label light-label">STAY CONNECTED</span>
        <h2>Keep Growing in Faith</h2>
        <p>
            Join us for worship, fellowship and the teaching of God's Word.
        </p>
        <a href="contact.php" class="btn btn-gold">
            Visit Our Church
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
