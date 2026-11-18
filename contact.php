
<?php
session_start();

require_once 'config/database.php';

$page_title = 'Contact Us | ELCK KISUMU';

$churchStmt = $pdo->query("SELECT * FROM church_info LIMIT 1");
$church = $churchStmt->fetch() ?: [];

$successMessage = '';
$errorMessage = '';

if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['contact_csrf'], $token)) {
        $errorMessage = 'Your form session has expired. Please refresh the page and try again.';
    } elseif (!empty($_POST['website'])) {
        // Honeypot field: silently reject automated spam.
        $errorMessage = 'Unable to submit your message. Please try again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (
            $fullName === '' ||
            $email === '' ||
            $subject === '' ||
            $message === ''
        ) {
            $errorMessage = 'Please complete all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        } elseif (
            strlen($fullName) > 150 ||
            strlen($email) > 150 ||
            strlen($phone) > 30 ||
            strlen($subject) > 200 ||
            strlen($message) > 10000
        ) {
            $errorMessage = 'Some of your entries are too long. Please shorten them and try again.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO contact_messages
                    (full_name, email, phone, subject, message)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $fullName,
                    $email,
                    $phone,
                    $subject,
                    $message
                ]);

                $successMessage = 'Thank you for contacting ELCK KISUMU. Your message has been received.';

                // Generate a fresh token after successful submission.
                $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errorMessage = 'We could not submit your message at this time. Please try again later.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">WE WOULD LOVE TO HEAR FROM YOU</span>
        <h1>Contact Us</h1>
        <p>Get in touch with our church family. We are happy to hear from you.</p>
    </div>
</section>

<!-- CONTACT INFORMATION -->
<section class="contact-info-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">GET IN TOUCH</span>
            <h2 class="section-title">We Are Here for You</h2>
            <p>
                Whether you have a question, need assistance or would like
                to visit us, please reach out.
            </p>
        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="contact-info-card">
                    <div class="contact-icon">📍</div>
                    <h3>Our Location</h3>
                    <p>
                        <?= htmlspecialchars($church['address'] ?: 'Kisumu, Kenya') ?>
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="contact-info-card">
                    <div class="contact-icon">☎</div>
                    <h3>Call Us</h3>

                    <?php if (!empty($church['phone'])): ?>
                        <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $church['phone'])) ?>">
                            <?= htmlspecialchars($church['phone']) ?>
                        </a>
                    <?php else: ?>
                        <p>Church phone number to be provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="contact-info-card">
                    <div class="contact-icon">✉</div>
                    <h3>Email Us</h3>

                    <?php if (!empty($church['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($church['email']) ?>">
                            <?= htmlspecialchars($church['email']) ?>
                        </a>
                    <?php else: ?>
                        <p>Church email address to be provided.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CONTACT FORM -->
<section class="contact-form-section">
    <div class="container">
        <div class="row g-5 align-items-start">

            <div class="col-lg-5">
                <span class="section-label">SEND US A MESSAGE</span>
                <h2 class="section-title">Let's Start a Conversation</h2>

                <p class="contact-intro">
                    Fill in the form and send us your message.
                    Our church team can review your enquiry and get back to you.
                </p>

                <div class="contact-note">
                    <span>✝</span>
                    <div>
                        <h4>You Are Welcome</h4>
                        <p>
                            We welcome questions, feedback, prayer enquiries
                            and requests for information about our church.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">

                <div class="contact-form-card">

                    <?php if ($successMessage): ?>
                        <div class="alert alert-success" role="status">
                            <?= htmlspecialchars($successMessage) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['contact_csrf']) ?>"
                        >

                        <!-- Honeypot spam field -->
                        <div class="contact-honeypot" aria-hidden="true">
                            <label for="website">Leave this field empty</label>
                            <input
                                type="text"
                                id="website"
                                name="website"
                                tabindex="-1"
                                autocomplete="off"
                            >
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="full_name">Full Name *</label>
                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    class="form-control"
                                    maxlength="150"
                                    autocomplete="name"
                                    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="email">Email Address *</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    maxlength="150"
                                    autocomplete="email"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="phone">Phone Number</label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    maxlength="30"
                                    autocomplete="tel"
                                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="subject">Subject *</label>
                                <input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    class="form-control"
                                    maxlength="200"
                                    value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                                    required
                                >
                            </div>

                            <div class="col-12">
                                <label for="message">Your Message *</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    class="form-control"
                                    rows="6"
                                    maxlength="10000"
                                    required
                                ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-gold contact-submit">
                                    Send Message →
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- LOCATION SECTION -->
<section class="contact-location-section">
    <div class="container text-center">
        <span class="section-label">FIND US</span>
        <h2 class="section-title">Visit ELCK KISUMU</h2>
        <p>
            We look forward to welcoming you. Contact the church for
            directions and confirmed service information.
        </p>
        <div class="location-placeholder">
            <span>📍</span>
            <h3>Kisumu, Kenya</h3>
            <p>
                The church's precise location and map will be added
                once the official address is confirmed.
            </p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

