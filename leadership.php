
<?php
require_once 'config/database.php';

$page_title = 'Our Leadership | ELCK KISUMU';

$stmt = $pdo->query("
    SELECT * FROM leaders
    ORDER BY id ASC
");

$leaders = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">SERVING WITH PURPOSE</span>
        <h1>Our Church Leadership</h1>
        <p>Meet the people dedicated to serving our church family.</p>
    </div>
</section>

<section class="leadership-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">OUR TEAM</span>
            <h2 class="section-title">Meet Our Leaders</h2>
            <p>Guided by faith and committed to serving God's people.</p>
        </div>

        <div class="row g-4">

            <?php if ($leaders): ?>

                <?php foreach ($leaders as $leader): ?>

                    <div class="col-md-4">

                        <div class="leader-card">

                            <?php if (!empty($leader['photo'])): ?>

                                <img
                                    src="uploads/<?= htmlspecialchars($leader['photo']) ?>"
                                    class="leader-image"
                                    alt="<?= htmlspecialchars($leader['full_name']) ?>"
                                >

                            <?php else: ?>

                                <div class="leader-placeholder">✝</div>

                            <?php endif; ?>

                            <div class="leader-details">

                                <h4>
                                    <?= htmlspecialchars($leader['full_name']) ?>
                                </h4>

                                <span>
                                    <?= htmlspecialchars($leader['position']) ?>
                                </span>

                                <p class="mt-3">
                                    <?= htmlspecialchars($leader['biography'] ?? '') ?>
                                </p>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12 text-center">
                    <p>Leadership profiles will be published soon.</p>
                </div>

            <?php endif; ?>

        </div>

    </div>
</section>

<section class="about-cta">
    <div class="container text-center">
        <h2>Growing Together in Faith</h2>
        <p>Join our church family and be part of our journey.</p>
        <a href="contact.php" class="btn btn-gold">Get In Touch</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
