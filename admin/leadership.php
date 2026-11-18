<?php
require_once 'auth.php';

$uploadDir = __DIR__ . '/../uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function uploadLeaderPhoto($file, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Photo upload failed.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Photo must not exceed 5MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG and WEBP photos are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new RuntimeException('Could not save the photograph.');
    }

    return $filename;
}

$message = '';
$error = '';

// Handle save and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Please refresh the page.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'save') {
                $id = (int)($_POST['id'] ?? 0);
                $fullName = trim($_POST['full_name'] ?? '');
                $position = trim($_POST['position'] ?? '');
                $biography = trim($_POST['biography'] ?? '');

                if ($fullName === '' || strlen($fullName) > 150) {
                    throw new RuntimeException(
                        'Enter a leader name of up to 150 characters.'
                    );
                }

                if ($position === '' || strlen($position) > 150) {
                    throw new RuntimeException(
                        'Enter a leadership position of up to 150 characters.'
                    );
                }

                if ($id > 0) {
                    $stmt = $pdo->prepare(
                        "SELECT photo FROM leaders WHERE id = ?"
                    );
                    $stmt->execute([$id]);
                    $existing = $stmt->fetch();

                    if (!$existing) {
                        throw new RuntimeException('Leader profile not found.');
                    }

                    if (
                        isset($_FILES['photo']) &&
                        $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
                    ) {
                        $photo = uploadLeaderPhoto(
                            $_FILES['photo'],
                            $uploadDir
                        );
                    } else {
                        $photo = $existing['photo'];
                    }

                    $stmt = $pdo->prepare("\n                        UPDATE leaders\n                        SET full_name = ?, position = ?,\n                            biography = ?, photo = ?\n                        WHERE id = ?\n                    ");
                    $stmt->execute([
                        $fullName,
                        $position,
                        $biography,
                        $photo,
                        $id
                    ]);

                    $message = 'Leadership profile updated successfully.';
                } else {
                    $photo = uploadLeaderPhoto(
                        $_FILES['photo'] ?? null,
                        $uploadDir
                    );

                    $stmt = $pdo->prepare("\n                        INSERT INTO leaders\n                        (full_name, position, biography, photo)\n                        VALUES (?, ?, ?, ?)\n                    ");
                    $stmt->execute([
                        $fullName,
                        $position,
                        $biography,
                        $photo
                    ]);

                    $message = 'New leader added successfully.';
                }
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {
                    throw new RuntimeException('Invalid leader selected.');
                }

                $stmt = $pdo->prepare(
                    "DELETE FROM leaders WHERE id = ?"
                );
                $stmt->execute([$id]);

                // The image file is retained in uploads.
                $message = 'Leadership profile deleted successfully.';
            }
        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }
    }
}

// Load leader for editing
$editLeader = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM leaders WHERE id = ?"
    );
    $stmt->execute([(int)$_GET['edit']]);
    $editLeader = $stmt->fetch();
}

// Search leaders
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("\n        SELECT * FROM leaders\n        WHERE full_name LIKE ? OR position LIKE ?\n        ORDER BY created_at DESC\n    ");
    $term = "%$search%";
    $stmt->execute([$term, $term]);
} else {
    $stmt = $pdo->query("\n        SELECT * FROM leaders\n        ORDER BY created_at DESC\n    ");
}

$leaders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leadership Management | ELCK Kisumu</title>

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
            font-size: 20px;
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
            transition: background .2s ease, color .2s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255,255,255,.15);
            color: #fff;
        }

        .sidebar a i {
            font-size: 17px;
            width: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 28px;
        }

        .page-heading h2 {
            color: var(--purple);
            font-weight: 700;
        }

        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
            margin-bottom: 25px;
        }

        .panel h5 {
            color: var(--purple);
            font-weight: 700;
        }

        .btn-purple {
            background: var(--purple);
            color: #fff;
            border: 1px solid var(--purple);
        }

        .btn-purple:hover,
        .btn-purple:focus {
            background: var(--purple-light);
            border-color: var(--purple-light);
            color: #fff;
        }

        .leader-photo {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #eee;
        }

        .photo-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }

        .logout-link {
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,.15);
            padding-top: 20px !important;
        }

        @media (max-width: 850px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 20px 16px;
            }

            .sidebar-brand {
                padding-bottom: 15px;
            }

            .main-content {
                margin-left: 0;
                padding: 18px;
            }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <h4>✝ ELCK KISUMU</h4>
        <small>Administration Portal</small>
        <div class="small text-white-50 mt-2">
            Welcome, <?= e($_SESSION['admin_name'] ?? 'Administrator') ?>
        </div>
    </div>

    <div class="sidebar-label">MAIN MENU</div>
    <a href="index.php"><i class="bi bi-grid"></i> Dashboard</a>
    <a href="../index.php" target="_blank"><i class="bi bi-globe"></i> View Website</a>

    <div class="sidebar-label">WEBSITE MANAGEMENT</div>
    <a href="members.php"><i class="bi bi-people"></i> Membership</a>
    <a href="sermons.php"><i class="bi bi-camera-video"></i> Sermons</a>
    <a href="events.php"><i class="bi bi-calendar-event"></i> Events</a>
    <a href="announcements.php"><i class="bi bi-megaphone"></i> Announcements</a>
    <a href="gallery.php"><i class="bi bi-images"></i> Gallery</a>
    <a href="leadership.php" class="active"><i class="bi bi-person-badge"></i> Church Leadership</a>

    <div class="sidebar-label">COMMUNICATION</div>
    <a href="messages.php"><i class="bi bi-envelope"></i> Contact Messages</a>

    <div class="sidebar-label">SETTINGS</div>
    <a href="settings.php"><i class="bi bi-gear"></i> Church Settings</a>
    <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
</aside>

<main class="main-content">
    <div class="page-heading d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2>Leadership Management</h2>
            <p class="text-muted mb-0">Manage pastors and church leadership profiles.</p>
        </div>

        <a href="../leadership.php" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-box-arrow-up-right me-1"></i> View Public Leadership
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success" role="alert">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <h5 class="mb-4">
            <?= $editLeader ? 'Edit Leader Profile' : 'Add Church Leader' ?>
        </h5>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e($editLeader['id'] ?? 0) ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" maxlength="150" required
                           value="<?= e($editLeader['full_name'] ?? '') ?>"
                           placeholder="Enter leader's full name">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Leadership Position *</label>
                    <input type="text" name="position" class="form-control" maxlength="150" required
                           value="<?= e($editLeader['position'] ?? '') ?>"
                           placeholder="e.g. Senior Pastor">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Profile Photograph</label>
                    <input type="file" name="photo" class="form-control"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                           <?= $editLeader ? '' : 'required' ?>>
                    <small class="text-muted">JPG, PNG or WEBP. Maximum 5MB.</small>

                    <?php if (!empty($editLeader['photo'])): ?>
                        <div class="mt-3">
                            <img src="../uploads/<?= e(basename($editLeader['photo'])) ?>"
                                 class="photo-preview" alt="Current leader photo">
                            <p class="small text-muted mt-1">Current profile photograph</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Biography</label>
                    <textarea name="biography" class="form-control" rows="5"
                              placeholder="Write a short biography about this church leader..."><?= e($editLeader['biography'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-purple">
                <i class="bi bi-save me-1"></i>
                <?= $editLeader ? 'Update Profile' : 'Add Church Leader' ?>
            </button>

            <?php if ($editLeader): ?>
                <a href="leadership.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h5 class="mb-0">Church Leadership Team</h5>

            <form method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Search leaders..." value="<?= e($search) ?>">
                <button class="btn btn-purple" type="submit">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <a href="leadership.php" class="btn btn-outline-secondary">Reset</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Full Name</th>
                        <th>Position</th>
                        <th>Biography</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($leaders): ?>
                    <?php foreach ($leaders as $leader): ?>
                        <tr>
                            <td>
                                <?php if (!empty($leader['photo'])): ?>
                                    <img src="../uploads/<?= e(basename($leader['photo'])) ?>"
                                         class="leader-photo" alt="Leader photograph">
                                <?php else: ?>
                                    <div class="leader-photo bg-light d-flex align-items-center justify-content-center">
                                        <span class="text-muted">N/A</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($leader['full_name']) ?></strong></td>
                            <td><?= e($leader['position']) ?></td>
                            <td>
                                <?= e(substr($leader['biography'] ?? '', 0, 90)) ?>
                                <?= strlen($leader['biography'] ?? '') > 90 ? '...' : '' ?>
                            </td>
                            <td>
                                <a href="leadership.php?edit=<?= (int)$leader['id'] ?>"
                                   class="btn btn-sm btn-warning mb-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this leader profile?')">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$leader['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger mb-1">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No church leaders have been added yet.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
