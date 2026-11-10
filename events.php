
<?php
require_once 'config/database.php';

$page_title = 'Events & Announcements | ELCK KISUMU';

// Fetch upcoming events
$eventStmt = $pdo->prepare("
    SELECT *
    FROM events
    WHERE event_date >= NOW()
    ORDER BY event_date ASC
");
$eventStmt->execute();
$events = $eventStmt->fetchAll();

// Fetch recent announcements
$announcementStmt = $pdo->query("
    SELECT *
    FROM announcements
    ORDER BY created_at DESC
    LIMIT 10
");
$announcements = $announcementStmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">LIFE AT ELCK KISUMU</span>
        <h1>Events & Announcements</h1>
        <p>Stay informed and be part of what is happening in our church family.</p>
    </div>
</section>

<!-- UPCOMING EVENTS -->
<section class="events-page-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">MARK YOUR CALENDAR</span>
            <h2 class="section-title">Upcoming Church Events</h2>
            <p>Join us for worship, fellowship and special church activities.</p>
        </div>

        <div class="row g-4">

            <?php if (!empty($events)): ?>

                <?php foreach ($events as $event): ?>
                    <?php
                    $eventTimestamp = strtotime($event['event_date']);
                    $eventImage = !empty($event['image'])
                        ? 'uploads/' . basename($event['image'])
                        : '';
                    ?>

                    <div class="col-lg-4 col-md-6">
                        <article class="event-card">

                            <?php if ($eventImage): ?>
                                <div class="event-image-wrapper">
                                    <img
                                        src="<?= htmlspecialchars($eventImage) ?>"
                                        alt="<?= htmlspecialchars($event['title']) ?>"
                                        class="event-image"
                                    >
                                </div>
                            <?php else: ?>
                                <div class="event-image-placeholder">
                                    <span>✝</span>
                                    <p>ELCK KISUMU</p>
                                </div>
                            <?php endif; ?>

                            <div class="event-content">

                                <div class="event-date">
                                    <span class="event-day">
                                        <?= date('d', $eventTimestamp) ?>
                                    </span>
                                    <span class="event-month">
                                        <?= date('M Y', $eventTimestamp) ?>
                                    </span>
                                </div>

                                <h3><?= htmlspecialchars($event['title']) ?></h3>

                                <p class="event-time">
                                    <span>🕒</span>
                                    <?= date('l, F j, Y \a\t g:i A', $eventTimestamp) ?>
                                </p>

                                <?php if (!empty($event['location'])): ?>
                                    <p class="event-location">
                                        <span>📍</span>
                                        <?= htmlspecialchars($event['location']) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($event['description'])): ?>
                                    <p class="event-description">
                                        <?= nl2br(htmlspecialchars($event['description'])) ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </article>
                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">
                    <div class="events-empty">
                        <div class="empty-icon">📅</div>
                        <h3>No Upcoming Events</h3>
                        <p>
                            There are no upcoming events published at the moment.
                            Please check back for updates.
                        </p>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>

<!-- ANNOUNCEMENTS -->
<section class="announcements-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">CHURCH UPDATES</span>
            <h2 class="section-title">Latest Announcements</h2>
            <p>Important messages and updates from our church.</p>
        </div>

        <div class="announcement-list">

            <?php if (!empty($announcements)): ?>

                <?php foreach ($announcements as $announcement): ?>

                    <article class="announcement-card">
                        <div class="announcement-icon">✦</div>

                        <div class="announcement-content">
                            <h3>
                                <?= htmlspecialchars($announcement['title']) ?>
                            </h3>

                            <p class="announcement-date">
                                Posted on
                                <?= date(
                                    'F j, Y',
                                    strtotime($announcement['created_at'])
                                ) ?>
                            </p>

                            <p>
                                <?= nl2br(htmlspecialchars($announcement['content'])) ?>
                            </p>
                        </div>
                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="events-empty">
                    <h3>No Announcements Yet</h3>
                    <p>Church announcements will appear here when published.</p>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="events-cta">
    <div class="container text-center">
        <span class="section-label light-label">YOU ARE WELCOME</span>
        <h2>Be Part of Our Church Family</h2>
        <p>
            We look forward to worshipping, learning and growing together with you.
        </p>
        <a href="membership.php" class="btn btn-gold">Join Our Church</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
