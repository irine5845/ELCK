
<?php
require_once 'auth.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$error = '';

// Handle add, edit and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'save') {
                $id = (int)($_POST['id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');

                if ($title === '' || strlen($title) > 200) {
                    throw new RuntimeException(
                        'Enter an announcement title of up to 200 characters.'
                    );
                }

                if ($content === '') {
                    throw new RuntimeException(
                        'Announcement content is required.'
                    );
                }

                if ($id > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE announcements
                        SET title = ?, content = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([$title, $content, $id]);
                    $message = 'Announcement updated successfully.';
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO announcements (title, content)
                        VALUES (?, ?)
                    ");

                    $stmt->execute([$title, $content]);
                    $message = 'Announcement published successfully.';
                }
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {
                    throw new RuntimeException('Invalid announcement selected.');
                }

                $stmt = $pdo->prepare(
                    "DELETE FROM announcements WHERE id = ?"
                );

                $stmt->execute([$id]);
                $message = 'Announcement deleted successfully.';
            }

        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }
    }
}

// Load announcement for editing
$editAnnouncement = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM announcements WHERE id = ?"
    );

    $stmt->execute([(int)$_GET['edit']]);
    $editAnnouncement = $stmt->fetch();
}

// Search announcements
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM announcements
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY created_at DESC
    ");

    $term = "%$search%";
    $stmt->execute([$term, $term]);
} else {
    $stmt = $pdo->query("
        SELECT * FROM announcements
        ORDER BY created_at DESC
    ");
}

$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Announcements Management | ELCK Kisumu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
            min-width: 0;
        }

        .panel {
            background: #fff;
            border-radius: 10px;
            padding: 23px;
            box-shadow: 0 3px 15px rgba(0,0,0,.03);
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

        .btn-purple:hover {
            background: var(--purple-light);
            border-color: var(--purple-light);
            color: #fff;
        }

        .announcement-preview {
            white-space: pre-wrap;
            max-width: 500px;
            overflow-wrap: anywhere;
        }

        .table th {
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
        }

        .table td {
            font-size: 13px;
            vertical-align: middle;
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

    <a href="index.php">
        <i class="bi bi-grid"></i> Dashboard
    </a>

    <a href="../index.php" target="_blank">
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

    <a href="announcements.php" class="active">
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

    <a href="settings.php">
        <i class="bi bi-gear"></i> Church Settings
    </a>

    <a href="logout.php" class="logout-link">
        <i class="bi bi-box-arrow-right"></i> Sign Out
    </a>

</aside>

<!-- Main content -->
<main class="main-content">

        <div class="d-flex flex-wrap justify-content-between
                    align-items-center mb-4 gap-3">

            <div>
                <h2>Announcements Management</h2>
                <p class="text-muted mb-0">
                    Publish and manage church announcements.
                </p>
            </div>

            <a href="../events.php" target="_blank"
               class="btn btn-outline-secondary">
                View Public Announcements
            </a>

        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <!-- Announcement form -->
        <div class="panel">

            <h5 class="mb-4">
                <?= $editAnnouncement
                    ? 'Edit Announcement'
                    : 'Publish New Announcement' ?>
            </h5>

            <form method="POST">

                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <input type="hidden" name="action" value="save">

                <input type="hidden" name="id"
                       value="<?= e($editAnnouncement['id'] ?? 0) ?>">

                <div class="mb-3">
                    <label class="form-label">Announcement Title *</label>

                    <input type="text"
                           name="title"
                           class="form-control"
                           maxlength="200"
                           required
                           value="<?= e($editAnnouncement['title'] ?? '') ?>"
                           placeholder="Enter announcement title">
                </div>

                <div class="mb-3">
                    <label class="form-label">Announcement Content *</label>

                    <textarea name="content"
                              class="form-control"
                              rows="6"
                              required
                              placeholder="Write your announcement here..."><?= e($editAnnouncement['content'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-purple">
                    <?= $editAnnouncement
                        ? 'Update Announcement'
                        : 'Publish Announcement' ?>
                </button>

                <?php if ($editAnnouncement): ?>
                    <a href="announcements.php"
                       class="btn btn-secondary">
                        Cancel
                    </a>
                <?php endif; ?>

            </form>

        </div>

        <!-- Announcements list -->
        <div class="panel">

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-3 mb-4">

                <h5 class="mb-0">Published Announcements</h5>

                <form method="GET" class="d-flex gap-2">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search announcements..."
                           value="<?= e($search) ?>">

                    <button class="btn btn-purple">Search</button>

                    <a href="announcements.php"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>

                </form>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Date Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($announcements): ?>

                        <?php foreach ($announcements as $announcement): ?>

                            <tr>

                                <td><?= (int)$announcement['id'] ?></td>

                                <td>
                                    <strong>
                                        <?= e($announcement['title']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <div class="announcement-preview">
                                        <?= e(substr($announcement['content'], 0, 140)) ?>
                                        <?= strlen($announcement['content']) > 140 ? '...' : '' ?>
                                    </div>
                                </td>

                                <td>
                                    <?= e(date(
                                        'd M Y, h:i A',
                                        strtotime($announcement['created_at'])
                                    )) ?>
                                </td>

                                <td>

                                    <a href="announcements.php?edit=<?= (int)$announcement['id'] ?>"
                                       class="btn btn-sm btn-warning mb-1">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this announcement?')">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= e($_SESSION['csrf_token']) ?>">

                                        <input type="hidden"
                                               name="action"
                                               value="delete">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int)$announcement['id'] ?>">

                                        <button type="submit"
                                                class="btn btn-sm btn-danger mb-1">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5"
                                class="text-center text-muted py-4">
                                No announcements found.
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
