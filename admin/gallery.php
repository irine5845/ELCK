
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

function uploadGalleryImage($file, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Please select an image to upload.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must not exceed 5MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG and WEBP images are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new RuntimeException('Could not save the image.');
    }

    return $filename;
}

$message = '';
$error = '';

// Handle gallery operations
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
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if (strlen($title) > 200) {
                    throw new RuntimeException('Title must not exceed 200 characters.');
                }

                if ($id > 0) {
                    $stmt = $pdo->prepare(
                        "SELECT image FROM gallery WHERE id = ?"
                    );
                    $stmt->execute([$id]);
                    $existing = $stmt->fetch();

                    if (!$existing) {
                        throw new RuntimeException('Gallery image not found.');
                    }

                    if (
                        isset($_FILES['image']) &&
                        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                    ) {
                        $image = uploadGalleryImage($_FILES['image'], $uploadDir);
                    } else {
                        $image = $existing['image'];
                    }

                    $stmt = $pdo->prepare("
                        UPDATE gallery
                        SET title = ?, description = ?, image = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([$title, $description, $image, $id]);

                    $message = 'Gallery image updated successfully.';

                } else {
                    $image = uploadGalleryImage($_FILES['image'] ?? null, $uploadDir);

                    $stmt = $pdo->prepare("
                        INSERT INTO gallery (title, image, description)
                        VALUES (?, ?, ?)
                    ");

                    $stmt->execute([$title, $image, $description]);

                    $message = 'Image uploaded successfully.';
                }
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {
                    throw new RuntimeException('Invalid gallery image selected.');
                }

                $stmt = $pdo->prepare(
                    "DELETE FROM gallery WHERE id = ?"
                );
                $stmt->execute([$id]);

                // Keep the physical file in uploads to avoid deleting
                // a file that may be shared elsewhere.
                $message = 'Gallery entry deleted successfully.';
            }

        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }
    }
}

// Load selected image for editing
$editImage = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editImage = $stmt->fetch();
}

// Search gallery
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM gallery
        WHERE title LIKE ? OR description LIKE ?
        ORDER BY created_at DESC
    ");

    $term = "%$search%";
    $stmt->execute([$term, $term]);
} else {
    $stmt = $pdo->query("
        SELECT * FROM gallery
        ORDER BY created_at DESC
    ");
}

$images = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gallery Management | ELCK Kisumu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --purple: #32145c;
            --purple-light: #684594;
            --gold: #d6a448;
            --background: #f5f4f8;
            --text: #383341;
        }

        * { box-sizing: border-box; }

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
        }

        .sidebar-brand small { color: #d8c9e7; }

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
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255,255,255,.15);
            color: #fff;
        }

        .sidebar a i { font-size: 17px; width: 20px; }

        .main-content {
            margin-left: 250px;
            padding: 28px;
        }

        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
            margin-bottom: 25px;
        }

        .btn-purple {
            background: var(--purple);
            color: #fff;
            border: 1px solid var(--purple);
        }

        .btn-purple:hover {
            background: var(--purple-light);
            border-color: var(--purple-light);
            color: #fff;
        }

        .gallery-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            height: 100%;
        }

        .gallery-card img {
            width: 100%;
            height: 190px;
            object-fit: cover;
        }

        .gallery-card-body { padding: 15px; }

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
                bottom: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 18px;
            }

            .sidebar-brand { padding-bottom: 15px; }
        }
    </style>
</head>

<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <h4>✝ ELCK KISUMU</h4>
        <small>Administration Portal</small>
    </div>

    <div class="sidebar-label">MAIN MENU</div>
    <a href="index.php"><i class="bi bi-grid"></i> Dashboard</a>
    <a href="../index.php" target="_blank"><i class="bi bi-globe"></i> View Website</a>

    <div class="sidebar-label">WEBSITE MANAGEMENT</div>
    <a href="members.php"><i class="bi bi-people"></i> Membership</a>
    <a href="sermons.php"><i class="bi bi-camera-video"></i> Sermons</a>
    <a href="events.php"><i class="bi bi-calendar-event"></i> Events</a>
    <a href="announcements.php"><i class="bi bi-megaphone"></i> Announcements</a>
    <a href="gallery.php" class="active"><i class="bi bi-images"></i> Gallery</a>
    <a href="leadership.php"><i class="bi bi-person-badge"></i> Church Leadership</a>

    <div class="sidebar-label">COMMUNICATION</div>
    <a href="messages.php"><i class="bi bi-envelope"></i> Contact Messages</a>

    <div class="sidebar-label">SETTINGS</div>
    <a href="settings.php"><i class="bi bi-gear"></i> Church Settings</a>
    <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
</aside>

<main class="main-content">

        <div class="d-flex flex-wrap justify-content-between
                    align-items-center mb-4 gap-3">

            <div>
                <h2>Gallery Management</h2>
                <p class="text-muted mb-0">
                    Manage photographs of church activities and events.
                </p>
            </div>

            <a href="../gallery.php" target="_blank"
               class="btn btn-outline-secondary">
                View Public Gallery
            </a>

        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Upload form -->
        <div class="panel">

            <h5 class="mb-4">
                <?= $editImage ? 'Edit Gallery Image' : 'Upload New Image' ?>
            </h5>

            <form method="POST" enctype="multipart/form-data">

                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <input type="hidden" name="action" value="save">

                <input type="hidden" name="id"
                       value="<?= e($editImage['id'] ?? 0) ?>">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Image Title</label>

                        <input type="text"
                               name="title"
                               class="form-control"
                               maxlength="200"
                               value="<?= e($editImage['title'] ?? '') ?>"
                               placeholder="e.g. Sunday Worship Service">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Choose Photograph *</label>

                        <input type="file"
                               name="image"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               <?= $editImage ? '' : 'required' ?>>

                        <small class="text-muted">
                            JPG, PNG or WEBP. Maximum 5MB.
                        </small>

                        <?php if (!empty($editImage['image'])): ?>
                            <div class="mt-2">
                                <img src="../uploads/<?= e(basename($editImage['image'])) ?>"
                                     width="100" height="70"
                                     style="object-fit:cover;border-radius:6px;"
                                     alt="Current gallery image">
                                <small class="text-muted">Current image</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Description</label>

                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Describe this photograph..."><?= e($editImage['description'] ?? '') ?></textarea>
                    </div>

                </div>

                <button type="submit" class="btn btn-purple">
                    <?= $editImage ? 'Update Image' : 'Upload Image' ?>
                </button>

                <?php if ($editImage): ?>
                    <a href="gallery.php" class="btn btn-secondary">
                        Cancel
                    </a>
                <?php endif; ?>

            </form>

        </div>

        <!-- Gallery images -->
        <div class="panel">

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-3 mb-4">

                <h5 class="mb-0">Church Photo Collection</h5>

                <form method="GET" class="d-flex gap-2">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search gallery..."
                           value="<?= e($search) ?>">

                    <button class="btn btn-purple">Search</button>

                    <a href="gallery.php"
                       class="btn btn-outline-secondary">Reset</a>

                </form>

            </div>

            <div class="row g-4">

                <?php if ($images): ?>

                    <?php foreach ($images as $image): ?>

                        <div class="col-sm-6 col-lg-4 col-xl-3">

                            <div class="gallery-card">

                                <img src="../uploads/<?= e(basename($image['image'])) ?>"
                                     alt="<?= e($image['title'] ?? 'Church photograph') ?>">

                                <div class="gallery-card-body">

                                    <h6>
                                        <?= e($image['title'] ?: 'Untitled Image') ?>
                                    </h6>

                                    <p class="small text-muted">
                                        <?= e(substr($image['description'] ?? '', 0, 90)) ?>
                                    </p>

                                    <div class="d-flex gap-2">

                                        <a href="gallery.php?edit=<?= (int)$image['id'] ?>"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              onsubmit="return confirm('Delete this gallery entry?')">

                                            <input type="hidden"
                                                   name="csrf_token"
                                                   value="<?= e($_SESSION['csrf_token']) ?>">

                                            <input type="hidden"
                                                   name="action"
                                                   value="delete">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= (int)$image['id'] ?>">

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger">
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="col-12 text-center text-muted py-5">
                        <h5>No photographs uploaded yet.</h5>
                        <p>Upload your first church photograph above.</p>
                    </div>

                <?php endif; ?>

            </div>

        </div>

</main>

</body>
</html>
