
<?php
require_once 'config/database.php';

$page_title = 'Church Gallery | ELCK KISUMU';

// Fetch gallery images
$stmt = $pdo->query("
    SELECT *
    FROM gallery
    ORDER BY created_at DESC, id DESC
");

$images = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">OUR CHURCH FAMILY</span>
        <h1>Church Gallery</h1>
        <p>Moments of worship, fellowship, celebration and service.</p>
    </div>
</section>

<!-- GALLERY SECTION -->
<section class="gallery-page-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">OUR MEMORIES</span>
            <h2 class="section-title">Life at ELCK KISUMU</h2>
            <p>
                Explore photographs capturing our church activities,
                worship services and community fellowship.
            </p>
        </div>

        <?php if (!empty($images)): ?>

            <div class="row g-4">

                <?php foreach ($images as $image): ?>

                    <?php
                    // Only use a filename, not a path supplied from the database.
                    $filename = basename($image['image']);
                    $imagePath = 'uploads/' . $filename;
                    ?>

                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-card">

                            <button
                                type="button"
                                class="gallery-image-button"
                                data-bs-toggle="modal"
                                data-bs-target="#galleryModal"
                                data-image="<?= htmlspecialchars($imagePath) ?>"
                                data-title="<?= htmlspecialchars($image['title'] ?? 'ELCK KISUMU') ?>"
                                data-description="<?= htmlspecialchars($image['description'] ?? '') ?>"
                                aria-label="View <?= htmlspecialchars($image['title'] ?? 'gallery image') ?>"
                            >
                                <img
                                    src="<?= htmlspecialchars($imagePath) ?>"
                                    alt="<?= htmlspecialchars($image['title'] ?? 'Church photograph') ?>"
                                    class="gallery-image"
                                    loading="lazy"
                                >

                                <span class="gallery-overlay">
                                    <span class="gallery-zoom">+</span>
                                </span>
                            </button>

                            <div class="gallery-caption">
                                <h3><?= htmlspecialchars($image['title'] ?? 'Church Moments') ?></h3>

                                <?php if (!empty($image['description'])): ?>
                                    <p><?= htmlspecialchars($image['description']) ?></p>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="gallery-empty">
                <div class="gallery-empty-icon">📷</div>
                <h3>Our Gallery Is Coming Soon</h3>
                <p>
                    Church photographs will be published here.
                    Please visit again soon.
                </p>
            </div>

        <?php endif; ?>

    </div>
</section>

<!-- IMAGE VIEWER MODAL -->
<div
    class="modal fade"
    id="galleryModal"
    tabindex="-1"
    aria-labelledby="galleryModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content gallery-modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalLabel">
                    ELCK KISUMU Gallery
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <div class="modal-body text-center">
                <img
                    src=""
                    id="galleryModalImage"
                    class="gallery-modal-image"
                    alt="Selected church photograph"
                >

                <p id="galleryModalDescription" class="mt-3 mb-0"></p>
            </div>

        </div>
    </div>
</div>

<!-- CALL TO ACTION -->
<section class="gallery-cta">
    <div class="container text-center">
        <h2>Be Part of Our Story</h2>
        <p>
            Join us in worship, fellowship and serving our community.
        </p>
        <a href="membership.php" class="btn btn-gold">
            Join Our Church Family
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
