
<?php
require_once 'config/database.php';

$page_title = 'About Us | ELCK KISUMU';

$stmt = $pdo->query("SELECT * FROM church_info LIMIT 1");
$church = $stmt->fetch();

require_once 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">GET TO KNOW US</span>
        <h1>About Our Church</h1>
        <p>A family united in faith, love and service.</p>
        <div class="breadcrumb-custom">
            <a href="index.php">Home</a> / About Us
        </div>
    </div>
</section>

<!-- INTRODUCTION -->
<section class="about-intro">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6">
                <div class="about-page-image">
                    <img src="assets/images/church-about.jpg"
                         alt="Church congregation">
                </div>
            </div>

            <div class="col-lg-6">
                <span class="section-label">WHO WE ARE</span>

                <h2 class="section-title">
                    Welcome to ELCK KISUMU
                </h2>

                <p>
                    <?= nl2br(htmlspecialchars(
                        $church['about_text'] ??
                        'Welcome to ELCK KISUMU. We are committed to worship, fellowship and spiritual growth.'
                    )) ?>
                </p>

                <p>
                    We believe in building a strong Christian community
                    through worship, biblical teaching, prayer,
                    fellowship and service to others.
                </p>

                <a href="contact.php" class="btn btn-purple">
                    Visit Our Church
                </a>
            </div>

        </div>
    </div>
</section>

<!-- MISSION AND VISION -->
<section class="about-mission">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">OUR PURPOSE</span>
            <h2 class="section-title">Our Mission & Vision</h2>
        </div>

        <div class="row g-4">

            <div class="col-md-6">
                <div class="purpose-card">
                    <div class="purpose-icon">✦</div>
                    <h3>Our Mission</h3>

                    <p>
                        <?= nl2br(htmlspecialchars(
                            $church['mission'] ??
                            'Our mission will be updated soon.'
                        )) ?>
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="purpose-card">
                    <div class="purpose-icon">◎</div>
                    <h3>Our Vision</h3>

                    <p>
                        <?= nl2br(htmlspecialchars(
                            $church['vision'] ??
                            'Our vision will be updated soon.'
                        )) ?>
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CORE VALUES -->
<section class="values-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">WHAT GUIDES US</span>
            <h2 class="section-title">Our Core Values</h2>
            <p>Principles that shape our faith and daily lives.</p>
        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="value-card">
                    <span>✝</span>
                    <h4>Faith</h4>
                    <p>We put our trust in God and follow His Word.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="value-card">
                    <span>♡</span>
                    <h4>Love</h4>
                    <p>We demonstrate God's love through kindness and compassion.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="value-card">
                    <span>♧</span>
                    <h4>Service</h4>
                    <p>We serve God and our community with dedication.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="value-card">
                    <span>✧</span>
                    <h4>Integrity</h4>
                    <p>We uphold honesty, accountability and righteousness.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="value-card">
                    <span>◉</span>
                    <h4>Fellowship</h4>
                    <p>We encourage unity and meaningful Christian relationships.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="value-card">
                    <span>⌂</span>
                    <h4>Community</h4>
                    <p>We work together to support and uplift those around us.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="about-cta">
    <div class="container text-center">
        <h2>Become Part of Our Church Family</h2>

        <p>
            There is a place for you at ELCK KISUMU.
            We look forward to worshipping and growing together.
        </p>

        <a href="membership.php" class="btn btn-gold btn-lg">
            Join Our Church
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
