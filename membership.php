
<?php
session_start();

require_once 'config/database.php';

$page_title = 'Membership Registration | ELCK KISUMU';

$successMessage = '';
$errorMessage = '';

if (empty($_SESSION['membership_csrf'])) {
    $_SESSION['membership_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['membership_csrf'], $token)) {
        $errorMessage = 'Your session has expired. Please refresh the page.';
    } else {

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (
            $fullName === '' ||
            $phone === '' ||
            $gender === ''
        ) {
            $errorMessage = 'Please complete all required fields.';
        } elseif (strlen($fullName) > 150 || strlen($phone) > 30 || strlen($address) > 255) {
            $errorMessage = 'Some entries are too long. Please check your information.';
        } elseif ($email !== '' && (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 150
        )) {
            $errorMessage = 'Please enter a valid email address.';
        } elseif (!in_array($gender, ['Male', 'Female', 'Prefer not to say'], true)) {
            $errorMessage = 'Please select a valid gender option.';
        } elseif (
            $dateOfBirth !== '' &&
            (
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth) ||
                !checkdate(
                    (int)substr($dateOfBirth, 5, 2),
                    (int)substr($dateOfBirth, 8, 2),
                    (int)substr($dateOfBirth, 0, 4)
                ) ||
                $dateOfBirth > date('Y-m-d')
            )
        ) {
            $errorMessage = 'Please enter a valid date of birth.';
        } else {

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO members
                    (full_name, email, phone, gender, date_of_birth, address)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $fullName,
                    $email !== '' ? $email : null,
                    $phone,
                    $gender,
                    $dateOfBirth !== '' ? $dateOfBirth : null,
                    $address !== '' ? $address : null
                ]);

                $successMessage = 'Your membership registration has been submitted successfully. Thank you for joining our church family!';

                $_SESSION['membership_csrf'] = bin2hex(random_bytes(32));

                // Clear submitted values after successful registration.
                $_POST = [];

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errorMessage = 'We could not process your registration. Please try again later.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <div class="container text-center">
        <span class="section-label light-label">BECOME PART OF OUR FAMILY</span>
        <h1>Membership Registration</h1>
        <p>We welcome you to join the ELCK KISUMU church family.</p>
    </div>
</section>

<!-- MEMBERSHIP INTRO -->
<section class="membership-intro">
    <div class="container text-center">
        <span class="section-label">WELCOME HOME</span>
        <h2 class="section-title">Grow With Us in Faith</h2>
        <p>
            We believe in worship, fellowship, spiritual growth and serving
            our community. Register below to express your interest in joining
            our church family.
        </p>
    </div>
</section>

<!-- REGISTRATION FORM -->
<section class="membership-form-section">
    <div class="container">

        <div class="membership-form-card">

            <div class="form-heading">
                <span class="section-label">JOIN ELCK KISUMU</span>
                <h2>Membership Registration Form</h2>
                <p>Please provide your information below.</p>
            </div>

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

            <form method="POST" action="membership.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['membership_csrf']) ?>"
                >

                <div class="row g-4">

                    <div class="col-md-6">
                        <label for="full_name">Full Name *</label>
                        <input
                            type="text"
                            name="full_name"
                            id="full_name"
                            class="form-control"
                            maxlength="150"
                            autocomplete="name"
                            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            maxlength="150"
                            autocomplete="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="phone">Phone Number *</label>
                        <input
                            type="tel"
                            name="phone"
                            id="phone"
                            class="form-control"
                            maxlength="30"
                            autocomplete="tel"
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                            placeholder="e.g. 07XXXXXXXX"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="gender">Gender *</label>
                        <select
                            name="gender"
                            id="gender"
                            class="form-select"
                            required
                        >
                            <option value="">Select Gender</option>
                            <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Prefer not to say" <?= (($_POST['gender'] ?? '') === 'Prefer not to say') ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="date_of_birth">Date of Birth</label>
                        <input
                            type="date"
                            name="date_of_birth"
                            id="date_of_birth"
                            class="form-control"
                            max="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="address">Residential Address</label>
                        <input
                            type="text"
                            name="address"
                            id="address"
                            class="form-control"
                            maxlength="255"
                            autocomplete="street-address"
                            value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
                            placeholder="Enter your area or residential address"
                        >
                    </div>

                    <div class="col-12">
                        <div class="membership-consent">
                            <p>
                                By submitting this form, you agree that the
                                church may use the information provided to
                                contact you regarding membership and church
                                activities.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-gold membership-submit">
                            Submit Registration →
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</section>

<!-- MEMBERSHIP CTA -->
<section class="membership-cta">
    <div class="container text-center">
        <h2>There Is a Place for You Here</h2>
        <p>
            We look forward to welcoming you into our church family.
        </p>
        <a href="contact.php" class="btn btn-gold">Contact Us</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
