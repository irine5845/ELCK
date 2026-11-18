
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

function uploadEventImage($file, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
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
        throw new RuntimeException('Could not save the uploaded image.');
    }

    return $filename;
}

$message = '';
$error = '';
$editEvent = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'save') {
                $id = (int)($_POST['id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $eventDate = trim($_POST['event_date'] ?? '');
                $location = trim($_POST['location'] ?? '');

                if ($title === '' || strlen($title) > 200) {
                    throw new RuntimeException('Enter an event title of up to 200 characters.');
                }

                if ($location !== '' && strlen($location) > 200) {
                    throw new RuntimeException('Location must not exceed 200 characters.');
                }

                $dateObject = DateTime::createFromFormat('Y-m-d\TH:i', $eventDate);

                if (!$dateObject || $dateObject->format('Y-m-d\TH:i') !== $eventDate) {
                    throw new RuntimeException('Enter a valid event date and time.');
                }

                $eventDate = $dateObject->format('Y-m-d H:i:s');

                $currentImage = null;

                if ($id > 0) {
                    $stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
                    $stmt->execute([$id]);
                    $existing = $stmt->fetch();

                    if (!$existing) {
                        throw new RuntimeException('Event not found.');
                    }

                    $currentImage = $existing['image'];
                }

                $newImage = uploadEventImage(
                    $_FILES['image'] ?? null,
                    $uploadDir
                );

                $image = $newImage ?: $currentImage;

                if ($id > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE events
                        SET title = ?, description = ?, event_date = ?,
                            location = ?, image = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $title,
                        $description,
                        $eventDate,
                        $location,
                        $image,
                        $id
                    ]);

                    $message = 'Event updated successfully.';
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO events
                        (title, description, event_date, location, image)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $title,
                        $description,
                        $eventDate,
                        $location,
                        $image
                    ]);

                    $message = 'Event added successfully.';
                }
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {
                    throw new RuntimeException('Invalid event selected.');
                }

                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$id]);

                $message = 'Event deleted successfully.';
            }
        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }
    }
}

// Load event for editing
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editEvent = $stmt->fetch();
}

// Search events
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM events
        WHERE title LIKE ? OR location LIKE ?
        ORDER BY event_date DESC
    ");
    $term = "%$search%";
    $stmt->execute([$term, $term]);
} else {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
}

$events = $stmt->fetchAll();

function datetimeLocal($value) {
    if (!$value) {
        return '';
    }

    return date('Y-m-d\TH:i', strtotime($value));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management | ELCK Kisumu</title>

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

        .sidebar a i {
            font-size: 17px;
            width: 20px;
        }

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
            background: var(--purple-light);
            color: #fff;
            border: 1px solid var(--purple-light);
        }

        .btn-purple:hover {
            background: var(--purple);
            border-color: var(--purple);
            color: #fff;
        }

        .event-image {
            width: 65px;
            height: 55px;
            object-fit: cover;
            border-radius: 6px;
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
    <a href="events.php" class="active"><i class="bi bi-calendar-event"></i> Events</a>
    <a href="announcements.php"><i class="bi bi-megaphone"></i> Announcements</a>
    <a href="gallery.php"><i class="bi bi-images"></i> Gallery</a>
    <a href="leadership.php"><i class="bi bi-person-badge"></i> Church Leadership</a>

    <div class="sidebar-label">COMMUNICATION</div>
    <a href="messages.php"><i class="bi bi-envelope"></i> Contact Messages</a>

    <div class="sidebar-label">SETTINGS</div>
    <a href="settings.php"><i class="bi bi-gear"></i> Church Settings</a>
    <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
</aside>

<main class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Events Management</h2>
                <p class="text-muted mb-0">Manage church events and activities.</p>
            </div>
            <a href="../events.php" target="_blank" class="btn btn-outline-secondary">
                View Public Events
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="panel">
            <h5 class="mb-4">
                <?= $editEvent ? 'Edit Event' : 'Add New Event' ?>
            </h5>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= e($editEvent['id'] ?? 0) ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Title *</label>
                        <input type="text" name="title" class="form-control"
                               maxlength="200" required
                               value="<?= e($editEvent['title'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Location</label>
                        <input type="text" name="location" class="form-control"
                               maxlength="200"
                               value="<?= e($editEvent['location'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Date and Time *</label>
                        <input type="datetime-local" name="event_date"
                               class="form-control" required
                               value="<?= e(datetimeLocal($editEvent['event_date'] ?? '')) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Poster / Image</label>
                        <input type="file" name="image" class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                        <small class="text-muted">JPG, PNG or WEBP. Maximum 5MB.</small>

                        <?php if (!empty($editEvent['image'])): ?>
                            <div class="mt-2">
                                <img src="../uploads/<?= e(basename($editEvent['image'])) ?>"
                                     class="event-image" alt="Current event image">
                                <small class="text-muted">Current image</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Event Description</label>
                        <textarea name="description" class="form-control"
                                  rows="4"><?= e($editEvent['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-purple">
                    <?= $editEvent ? 'Update Event' : 'Save Event' ?>
                </button>

                <?php if ($editEvent): ?>
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="panel">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <h5>All Church Events</h5>

                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search events..."
                           value="<?= e($search) ?>">
                    <button class="btn btn-purple">Search</button>
                    <a href="events.php" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Event</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($events): ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($event['image'])): ?>
                                        <img src="../uploads/<?= e(basename($event['image'])) ?>"
                                             class="event-image" alt="Event image">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <strong><?= e($event['title']) ?></strong>
                                    <div class="small text-muted">
                                        <?= e(substr($event['description'] ?? '', 0, 65)) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= e(date('d M Y, h:i A', strtotime($event['event_date']))) ?>
                                </td>

                                <td><?= e($event['location'] ?? '') ?></td>

                                <td>
                                    <a href="events.php?edit=<?= (int)$event['id'] ?>"
                                       class="btn btn-sm btn-warning mb-1">Edit</a>

                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this event?')">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= e($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$event['id'] ?>">
                                        <button class="btn btn-sm btn-danger mb-1">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No events found. Add your first church event above.
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
