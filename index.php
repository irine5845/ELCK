
<?php
require_once 'config/database.php';

$page_title = 'Home | ELCK KISUMU';

require_once 'includes/header.php';

// Get church information
$stmt = $pdo->query("SELECT * FROM church_info LIMIT 1");
$church = $stmt->fetch();

// Get latest sermons
$stmt = $pdo->query("
    SELECT * FROM sermons
    ORDER BY sermon_date DESC
    LIMIT 3
");
$sermons = $stmt->fetchAll();

// Get upcoming events
$stmt = $pdo->query("
    SELECT * FROM events
    WHERE event_date >= NOW()
    ORDER BY event_date ASC
    LIMIT 3
");
$events = $stmt->fetchAll();

// Get church leaders
$stmt = $pdo->query("
    SELECT * FROM leaders
    ORDER BY id ASC
    LIMIT 3
");
$leaders = $stmt->fetchAll();
?>

<!-- HERO SECTION -->
<section class="hero-section">

    <div class="hero-overlay"></div>

    <div class="container hero-content">

        <span class="hero-label">
            WELCOME TO ELCK KISUMU
        </span>

        <h1>
            A Place of Faith,<br>
            Hope and <span>Love.</span>
        </h1>

        <p>
            Discover God's love, grow in faith and experience
            the joy of fellowship with our church family.
        </p>

        <div class="hero-buttons">

            <a href="about.php" class="btn btn-gold btn-lg">
                Discover Our Church
            </a>

            <a href="contact.php" class="btn btn-outline-light btn-lg">
                Join Us
            </a>

        </div>

    </div>

    <div class="hero-bottom">
        <div class="container">
            <span>ONE FAITH. ONE FAMILY. ONE PURPOSE.</span>
        </div>
    </div>

</section>


<!-- SERVICE INFORMATION -->
<section class="service-section">

    <div class="container">

        <div class="row g-4">

            <div class="col-md-4">

                <div class="service-card">

                    <div class="service-icon">✝</div>

                    <h4>Worship With Us</h4>

                    <p>
                        Join our church family as we worship
                        and celebrate God's goodness.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="service-card">

                    <div class="service-icon">♡</div>

                    <h4>Grow In Faith</h4>

                    <p>
                        Strengthen your spiritual journey
                        through God's Word and fellowship.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="service-card">

                    <div class="service-icon">♧</div>

                    <h4>Serve Together</h4>

                    <p>
                        Use your gifts to make a difference
                        in the church and our community.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ABOUT SECTION -->
<section class="about-section">

    <div class="container">

        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <div class="about-image">
                    <img
                        src="assets/images/church-about.jpg"
                        alt="ELCK KISUMU church fellowship"
                        onerror="this.style.display='none';"
                    >

                    <div class="image-caption">
                        <h4>Faith. Fellowship. Service.</h4>
                        <p>Growing together in God's love.</p>
                    </div>
                </div>

            </div>

            <div class="col-lg-6">

                <span class="section-label">ABOUT OUR CHURCH</span>

                <h2 class="section-title">
                    A Church Built on Faith and Love
                </h2>

                <p>
                    <?= htmlspecialchars(
                        $church['about_text'] ??
                        'Welcome to ELCK KISUMU. We are committed to worship, fellowship and spiritual growth.'
                    ) ?>
                </p>

                <div class="about-points">

                    <p>✓ Christ-centred worship</p>

                    <p>✓ Biblical teaching and discipleship</p>

                    <p>✓ Community outreach and service</p>

                    <p>✓ A welcoming church family</p>

                </div>

                <a href="about.php" class="btn btn-purple">
                    Learn More About Us
                </a>

            </div>

        </div>

    </div>

</section>


<!-- CHURCH MISSION AND VISION -->
<section class="mission-section">

    <div class="container">

        <div class="row g-4">

            <div class="col-md-6">

                <div class="mission-card">

                    <span class="mission-icon">✦</span>

                    <h3>Our Mission</h3>

                    <p>
                        <?= htmlspecialchars(
                            $church['mission'] ??
                            'To be updated with our official church mission.'
                        ) ?>
                    </p>

                </div>

            </div>

            <div class="col-md-6">

                <div class="mission-card">

                    <span class="mission-icon">◎</span>

                    <h3>Our Vision</h3>

                    <p>
                        <?= htmlspecialchars(
                            $church['vision'] ??
                            'To be updated with our official church vision.'
                        ) ?>
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- LEADERSHIP SECTION -->
<section class="leadership-section">

    <div class="container">

        <div class="section-heading text-center">

            <span class="section-label">OUR LEADERSHIP</span>

            <h2 class="section-title">
                Meet Our Church Leaders
            </h2>

            <p>
                Dedicated servants committed to spiritual growth,
                guidance and service.
            </p>

        </div>

        <div class="row g-4">

            <?php if ($leaders): ?>

                <?php foreach ($leaders as $leader): ?>

                    <div class="col-md-4">

                        <div class="leader-card">

                            <?php if (!empty($leader['photo'])): ?>

                                <img
                                    src="uploads/<?= htmlspecialchars($leader['photo']) ?>"
                                    alt="<?= htmlspecialchars($leader['full_name']) ?>"
                                    class="leader-image"
                                >

                            <?php else: ?>

                                <div class="leader-placeholder">
                                    ✝
                                </div>

                            <?php endif; ?>

                            <div class="leader-details">

                                <h4>
                                    <?= htmlspecialchars($leader['full_name']) ?>
                                </h4>

                                <span>
                                    <?= htmlspecialchars($leader['position']) ?>
                                </span>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12 text-center">
                    <p>Church leadership profiles will be available soon.</p>
                </div>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- SERMONS SECTION -->
<section class="sermons-section">

    <div class="container">

        <div class="section-heading text-center">

            <span class="section-label">SPIRITUAL GROWTH</span>

            <h2 class="section-title">
                Latest Sermons
            </h2>

            <p>
                Be encouraged and strengthened through God's Word.
            </p>

        </div>

        <div class="row g-4">

            <?php if ($sermons): ?>

                <?php foreach ($sermons as $sermon): ?>

                    <div class="col-md-4">

                        <div class="sermon-card">

                            <div class="sermon-image">
                                <span>▶</span>
                            </div>

                            <div class="sermon-content">

                                <small>
                                    <?= !empty($sermon['sermon_date'])
                                        ? date('d M Y', strtotime($sermon['sermon_date']))
                                        : 'Sermon' ?>
                                </small>

                                <h4>
                                    <?= htmlspecialchars($sermon['title']) ?>
                                </h4>

                                <p>
                                    <?= htmlspecialchars($sermon['preacher'] ?? '') ?>
                                </p>

                                <a href="sermons.php">
                                    Watch Sermon →
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12 text-center">
                    <p>Our latest sermons will be published here.</p>
                </div>

            <?php endif; ?>

        </div>

        <div class="text-center mt-5">

            <a href="sermons.php" class="btn btn-purple">
                View All Sermons
            </a>

        </div>

    </div>

</section>


<!-- EVENTS SECTION -->
<section class="events-section">

    <div class="container">

        <div class="section-heading text-center">

            <span class="section-label">CHURCH ACTIVITIES</span>

            <h2 class="section-title">
                Upcoming Events
            </h2>

            <p>
                Stay connected and be part of what God is doing
                in our church community.
            </p>

        </div>

        <div class="row g-4">

            <?php if ($events): ?>

                <?php foreach ($events as $event): ?>

                    <div class="col-md-4">

                        <div class="event-card">

                            <div class="event-date">

                                <?= date('d', strtotime($event['event_date'])) ?>

                                <small>
                                    <?= date('M', strtotime($event['event_date'])) ?>
                                </small>

                            </div>

                            <div class="event-content">

                                <h4>
                                    <?= htmlspecialchars($event['title']) ?>
                                </h4>

                                <p>
                                    <?= htmlspecialchars($event['description'] ?? '') ?>
                                </p>

                                <small>
                                    📍 <?= htmlspecialchars($event['location'] ?? 'Kisumu') ?>
                                </small>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12 text-center">
                    <p>Upcoming church events will be announced here.</p>
                </div>

            <?php endif; ?>

        </div>

        <div class="text-center mt-5">

            <a href="events.php" class="btn btn-purple">
                Explore All Events
            </a>

        </div>

    </div>

</section>


<!-- GIVING SECTION -->
<section class="giving-section">

    <div class="container text-center">

        <span class="section-label light-label">
            PARTNER WITH US
        </span>

        <h2>
            Give With a Grateful Heart
        </h2>

        <p>
            Your generosity helps support ministry,
            outreach and community development.
        </p>

        <a href="giving.php" class="btn btn-gold btn-lg">
            Support Our Ministry
        </a>

    </div>

</section>


<!-- MEMBERSHIP SECTION -->
<section class="membership-section">

    <div class="container text-center">

        <h2>Become Part of Our Church Family</h2>

        <p>
            We would love to welcome you into our fellowship.
            Take the next step and register with us.
        </p>

        <a href="membership.php" class="btn btn-purple">
            Register as a Member
        </a>

    </div>

</section>

<?php require_once 'includes/footer.php'; ?>

