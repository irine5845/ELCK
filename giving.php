
<?php
require_once 'config/database.php';

$page_title = 'Giving | ELCK KISUMU';

$stmt = $pdo->query("SELECT * FROM church_info LIMIT 1");
$church = $stmt->fetch();

$paybill = $church['paybill'] ?? '';


require_once 'includes/header.php';
?>

<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">GIVING WITH A GRATEFUL HEART</span>
        <h1>Give & Support Our Ministry</h1>
        <p>Your generosity helps us serve, worship and support our community.</p>
    </div>
</section>

<section class="giving-intro">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-label">SUPPORT THE WORK OF GOD</span>
                <h2 class="section-title">Your Giving Makes a Difference</h2>
                <p>
                    Giving is an opportunity to express gratitude, support the
                    ministry and contribute towards the work of the church.
                </p>
                <p>
                    We appreciate every contribution and the generosity of
                    everyone who supports our church family.
                </p>
            </div>

            <div class="col-lg-6">
                <div class="giving-verse">
                    <span class="verse-icon">✝</span>
                    <blockquote>
                        "Each of you should give what you have decided in your
                        heart to give, not reluctantly or under compulsion,
                        for God loves a cheerful giver."
                    </blockquote>
                    <span class="verse-reference">2 Corinthians 9:7</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="giving-method-section">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-label">SIMPLE & CONVENIENT</span>
            <h2 class="section-title">Give Through M-Pesa</h2>
            <p>Use the official church payment details below when making your contribution.</p>
        </div>

        <div class="row justify-content-center g-4">
            <div class="col-lg-5 col-md-6">
                <div class="mpesa-card">
                    <div class="mpesa-logo">M-PESA</div>
                    <h3>Paybill Number</h3>

                    <?php if ($paybill): ?>
                        <div class="payment-number">
                            <?= htmlspecialchars($paybill) ?>
                        </div>
                    <?php else: ?>
                        <div class="payment-pending">
                            To be provided by the church
                        </div>
                    <?php endif; ?>

                    <p>Business Number</p>
                </div>
            </div>

            

        <div class="giving-instructions">
            <h3>How to Give Using M-Pesa</h3>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="instruction-item">
                        <span>01</span>
                        <h4>Open M-Pesa</h4>
                        <p>Open the M-Pesa menu on your mobile phone.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="instruction-item">
                        <span>02</span>
                        <h4>Select Payment Option</h4>
                        <p>Choose Lipa na M-Pesa, then Paybill.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="instruction-item">
                        <span>03</span>
                        <h4>Confirm Payment</h4>
                        <p>Enter the verified church details and amount, then confirm carefully.</p>
                    </div>
                </div>
            </div>

            <p class="payment-warning">
                <strong>Important:</strong> Confirm the official church payment
                details before sending money. Do not make a payment using
                unverified numbers.
            </p>
        </div>
    </div>
</section>

<section class="giving-categories">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-label">WAYS TO CONTRIBUTE</span>
            <h2 class="section-title">Your Gift Supports Our Ministry</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="giving-category-card">
                    <div class="category-icon">♡</div>
                    <h3>Tithes</h3>
                    <p>Support the ministry through faithful giving.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="giving-category-card">
                    <div class="category-icon">✝</div>
                    <h3>Offerings</h3>
                    <p>Contribute towards worship and the ongoing work of the church.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="giving-category-card">
                    <div class="category-icon">🤝</div>
                    <h3>Donations</h3>
                    <p>Support church projects and community outreach initiatives.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="giving-cta">
    <div class="container text-center">
        <h2>Thank You for Your Generosity</h2>
        <p>May God bless you as you continue to support His work.</p>
        <a href="contact.php" class="btn btn-gold">Contact the Church</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
