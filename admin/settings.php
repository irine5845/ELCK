
<?php
require_once 'auth.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$success = '';
$error = '';

// Save church settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Please refresh the page.';

    } else {

        $churchName = trim($_POST['church_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mission = trim($_POST['mission'] ?? '');
        $vision = trim($_POST['vision'] ?? '');
        $aboutText = trim($_POST['about_text'] ?? '');
        $paybill = trim($_POST['paybill'] ?? '');
        $tillNumber = trim($_POST['till_number'] ?? '');

        if ($churchName === '') {
            $error = 'Church name is required.';
        } elseif (strlen($churchName) > 150) {
            $error = 'Church name must not exceed 150 characters.';
        } elseif (strlen($address) > 255) {
            $error = 'Address must not exceed 255 characters.';
        } elseif (strlen($phone) > 30) {
            $error = 'Phone number must not exceed 30 characters.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($email) > 150) {
            $error = 'Email must not exceed 150 characters.';
        } elseif (strlen($paybill) > 50 || strlen($tillNumber) > 50) {
            $error = 'M-Pesa numbers must not exceed 50 characters.';
        }

        if ($error === '') {
            try {
                $stmt = $pdo->query("
                    SELECT id FROM church_info
                    ORDER BY id ASC LIMIT 1
                ");

                $existing = $stmt->fetch();

                if ($existing) {
                    $stmt = $pdo->prepare("
                        UPDATE church_info SET
                            church_name = ?,
                            address = ?,
                            phone = ?,
                            email = ?,
                            mission = ?,
                            vision = ?,
                            about_text = ?,
                            paybill = ?,
                            till_number = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $churchName,
                        $address,
                        $phone,
                        $email,
                        $mission,
                        $vision,
                        $aboutText,
                        $paybill,
                        $tillNumber,
                        $existing['id']
                    ]);

                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO church_info (
                            church_name, address, phone, email,
                            mission, vision, about_text,
                            paybill, till_number
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $churchName,
                        $address,
                        $phone,
                        $email,
                        $mission,
                        $vision,
                        $aboutText,
                        $paybill,
                        $tillNumber
                    ]);
                }

                $success = 'Church settings saved successfully.';

            } catch (PDOException $ex) {
                error_log($ex->getMessage());
                $error = 'Unable to save settings. Please try again.';
            }
        }
    }
}

// Load current church information
$stmt = $pdo->query("
    SELECT * FROM church_info
    ORDER BY id ASC LIMIT 1
");

$settings = $stmt->fetch();

if (!$settings) {
    $settings = [
        'church_name' => 'ELCK KISUMU',
        'address' => '',
        'phone' => '',
        'email' => '',
        'mission' => '',
        'vision' => '',
        'about_text' => '',
        'paybill' => '',
        'till_number' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Church Settings | ELCK Kisumu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --purple: #32145c;
            --purple-light: #684594;
            --gold: #d6a448;
            --background: #f5f4f8;
            --text: #383341;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 250px;
            padding: 25px 16px;
            background: var(--purple);
            color: #fff;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 5px 12px 28px;
            border-bottom: 1px solid rgba(255,255,255,.15);
            margin-bottom: 24px;
        }

        .sidebar-brand h4 {
            font-weight: 700;
            margin: 0;
            color: #fff;
        }

        .sidebar-brand small {
            color: #d8c9e7;
        }

        .sidebar-label {
            color: #c9b8dc;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 0 12px;
            margin: 20px 0 10px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #eee7f5;
            text-decoration: none;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 5px;
            font-size: 14px;
            transition: .2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255,255,255,.15);
            color: #fff;
        }

        .sidebar a.active {
            border-left: 3px solid var(--gold);
            padding-left: 9px;
        }

        .sidebar a i {
            font-size: 17px;
            width: 20px;
            text-align: center;
        }

        .logout-link {
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,.15);
            padding-top: 20px !important;
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 28px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-header h2 {
            color: var(--purple);
            font-weight: 700;
        }

        /* Settings cards */
        .settings-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
            margin-bottom: 25px;
            border: 1px solid #eeeaf3;
        }

        .settings-card h5 {
            color: var(--purple);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .settings-card h5 i {
            color: var(--gold);
            margin-right: 8px;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control {
            padding: 11px;
            border-color: #ded8e7;
        }

        .form-control:focus {
            border-color: var(--purple-light);
            box-shadow: 0 0 0 .2rem rgba(104,69,148,.13);
        }

        .btn-purple {
            background: var(--purple);
            color: white;
            padding: 11px 25px;
            border: 1px solid var(--purple);
            font-weight: 600;
        }

        .btn-purple:hover {
            background: #241044;
            border-color: #241044;
            color: white;
        }

        .helper-text {
            color: #777080;
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 850px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                min-height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 18px;
            }

            .sidebar-brand {
                padding-bottom: 15px;
            }
        }

        @media (max-width: 576px) {
            .settings-card {
                padding: 18px;
            }

            .page-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<!-- Sidebar -->
<aside class="sidebar">

    <div class="sidebar-brand">
        <h4>✝ ELCK KISUMU</h4>
        <small>Administration Portal</small>
    </div>

    <p class="small text-white-50 px-2">
        Welcome, <?= e($_SESSION['admin_name'] ?? 'Administrator') ?>
    </p>

    <div class="sidebar-label">MAIN MENU</div>

    <a href="index.php">
        <i class="bi bi-grid"></i> Dashboard
    </a>

    <a href="../index.php" target="_blank" rel="noopener">
        <i class="bi bi-globe"></i> View Website
    </a>

    <div class="sidebar-label">WEBSITE MANAGEMENT</div>

    <a href="members.php">
        <i class="bi bi-people"></i> Membership
    </a>

    <a href="sermons.php">
        <i class="bi bi-camera-video"></i> Sermons
    </a>

    <a href="events.php">
        <i class="bi bi-calendar-event"></i> Events
    </a>

    <a href="announcements.php">
        <i class="bi bi-megaphone"></i> Announcements
    </a>

    <a href="gallery.php">
        <i class="bi bi-images"></i> Gallery
    </a>

    <a href="leadership.php">
        <i class="bi bi-person-badge"></i> Church Leadership
    </a>

    <div class="sidebar-label">COMMUNICATION</div>

    <a href="messages.php">
        <i class="bi bi-envelope"></i> Contact Messages
    </a>

    <div class="sidebar-label">SETTINGS</div>

    <a href="settings.php" class="active" aria-current="page">
        <i class="bi bi-gear"></i> Church Settings
    </a>

    <a href="logout.php" class="logout-link">
        <i class="bi bi-box-arrow-right"></i> Sign Out
    </a>

</aside>

<!-- Main content -->
<main class="main-content">

    <div class="page-header">
        <h2>Church Settings</h2>
        <p class="text-muted mb-0">
            Update the information displayed across the church website.
        </p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <?= e($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= e($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden"
               name="csrf_token"
               value="<?= e($_SESSION['csrf_token']) ?>">

        <!-- General information -->
        <section class="settings-card">

            <h5>
                <i class="bi bi-building"></i>
                General Church Information
            </h5>

            <div class="mb-3">
                <label class="form-label">Church Name *</label>

                <input type="text"
                       name="church_name"
                       class="form-control"
                       maxlength="150"
                       value="<?= e($settings['church_name']) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Physical Address</label>

                <input type="text"
                       name="address"
                       class="form-control"
                       maxlength="255"
                       value="<?= e($settings['address']) ?>"
                       placeholder="Enter church location">
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Church Phone Number</label>

                    <input type="text"
                           name="phone"
                           class="form-control"
                           maxlength="30"
                           value="<?= e($settings['phone']) ?>"
                           placeholder="+254...">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Church Email Address</label>

                    <input type="email"
                           name="email"
                           class="form-control"
                           maxlength="150"
                           value="<?= e($settings['email']) ?>"
                           placeholder="church@example.com">
                </div>

            </div>

        </section>

        <!-- About church -->
        <section class="settings-card">

            <h5>
                <i class="bi bi-info-circle"></i>
                About the Church
            </h5>

            <div class="mb-3">
                <label class="form-label">About Text</label>

                <textarea name="about_text"
                          class="form-control"
                          rows="6"
                          placeholder="Write a description of ELCK Kisumu..."><?= e($settings['about_text']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Church Mission</label>

                <textarea name="mission"
                          class="form-control"
                          rows="4"
                          placeholder="Enter the church mission..."><?= e($settings['mission']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Church Vision</label>

                <textarea name="vision"
                          class="form-control"
                          rows="4"
                          placeholder="Enter the church vision..."><?= e($settings['vision']) ?></textarea>
            </div>

        </section>

        <!-- M-Pesa -->
        <section class="settings-card">

            <h5>
                <i class="bi bi-phone"></i>
                M-Pesa Giving Information
            </h5>

            <p class="helper-text">
                These details will be displayed on the church giving page.
                Enter only the official church payment details.
            </p>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">M-Pesa Paybill Number</label>

                    <input type="text"
                           name="paybill"
                           class="form-control"
                           maxlength="50"
                           value="<?= e($settings['paybill']) ?>"
                           placeholder="Enter official Paybill number">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">M-Pesa Till Number</label>

                    <input type="text"
                           name="till_number"
                           class="form-control"
                           maxlength="50"
                           value="<?= e($settings['till_number']) ?>"
                           placeholder="Enter official Till number">
                </div>

            </div>

        </section>

        <div class="d-flex flex-wrap gap-2 mb-5">

            <button type="submit" class="btn btn-purple">
                <i class="bi bi-save me-2"></i>
                Save Church Settings
            </button>

            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Dashboard
            </a>

        </div>

    </form>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
