
<?php
require_once 'auth.php';

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['sermons_csrf'])) {
    $_SESSION['sermons_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = 'success';

function validYouTubeUrl(string $url): bool {
    if ($url === '') {
        return true;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

    return in_array($host, [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'youtu.be',
        'www.youtube-nocookie.com',
        'youtube-nocookie.com'
    ], true);
}

// Handle add, edit and delete.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['sermons_csrf'], $token)) {
        $message = 'Your session has expired. Refresh the page and try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            $id = filter_var($_POST['sermon_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$id || $id < 1) {
                $message = 'Invalid sermon selected.';
                $messageType = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("DELETE FROM sermons WHERE id = ?");
                    $stmt->execute([$id]);

                    $message = $stmt->rowCount()
                        ? 'Sermon deleted successfully.'
                        : 'Sermon not found.';

                    $messageType = $stmt->rowCount() ? 'success' : 'warning';
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $message = 'Unable to delete this sermon.';
                    $messageType = 'danger';
                }
            }
        } elseif (in_array($action, ['create', 'update'], true)) {
            $title = trim($_POST['title'] ?? '');
            $preacher = trim($_POST['preacher'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $videoUrl = trim($_POST['video_url'] ?? '');
            $sermonDate = trim($_POST['sermon_date'] ?? '');
            $id = filter_var($_POST['sermon_id'] ?? null, FILTER_VALIDATE_INT);

            $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $sermonDate)
                && checkdate(
                    (int)substr($sermonDate, 5, 2),
                    (int)substr($sermonDate, 8, 2),
                    (int)substr($sermonDate, 0, 4)
                );

            if (
                $title === '' ||
                strlen($title) > 200 ||
                strlen($preacher) > 150 ||
                strlen($videoUrl) > 500 ||
                !$validDate ||
                !validYouTubeUrl($videoUrl)
            ) {
                $message = 'Please check the sermon title, preacher, date and YouTube URL.';
                $messageType = 'danger';
            } elseif ($action === 'update' && (!$id || $id < 1)) {
                $message = 'Invalid sermon selected for editing.';
                $messageType = 'danger';
            } else {
                try {
                    if ($action === 'create') {
                        $stmt = $pdo->prepare("
                            INSERT INTO sermons
                            (title, preacher, description, video_url, sermon_date)
                            VALUES (?, ?, ?, ?, ?)
                        ");

                        $stmt->execute([
                            $title,
                            $preacher ?: null,
                            $description ?: null,
                            $videoUrl ?: null,
                            $sermonDate
                        ]);

                        $message = 'Sermon added successfully.';
                    } else {
                        $stmt = $pdo->prepare("
                            UPDATE sermons
                            SET title = ?,
                                preacher = ?,
                                description = ?,
                                video_url = ?,
                                sermon_date = ?
                            WHERE id = ?
                        ");

                        $stmt->execute([
                            $title,
                            $preacher ?: null,
                            $description ?: null,
                            $videoUrl ?: null,
                            $sermonDate,
                            $id
                        ]);

                        $message = 'Sermon updated successfully.';
                    }

                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $message = 'Unable to save the sermon. Please try again.';
                    $messageType = 'danger';
                }
            }
        } else {
            $message = 'Invalid action.';
            $messageType = 'danger';
        }
    }

    $_SESSION['sermons_csrf'] = bin2hex(random_bytes(32));
}

// Selected sermon for editing.
$editSermon = null;
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);

if ($editId && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM sermons WHERE id = ?");
    $stmt->execute([$editId]);
    $editSermon = $stmt->fetch();

    if (!$editSermon) {
        $message = 'Sermon not found.';
        $messageType = 'warning';
    }
}

// Search sermons.
$search = trim($_GET['search'] ?? '');

try {
    if ($search !== '') {
        $stmt = $pdo->prepare("
            SELECT * FROM sermons
            WHERE title LIKE ? OR preacher LIKE ?
            ORDER BY sermon_date DESC, id DESC
        ");

        $term = "%$search%";
        $stmt->execute([$term, $term]);
        $sermons = $stmt->fetchAll();
    } else {
        $sermons = $pdo->query("
            SELECT * FROM sermons
            ORDER BY sermon_date DESC, id DESC
        ")->fetchAll();
    }

    $totalSermons = (int)$pdo->query(
        "SELECT COUNT(*) FROM sermons"
    )->fetchColumn();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $sermons = [];
    $totalSermons = 0;
    $message = 'Unable to load sermons.';
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sermons Management | ELCK KISUMU</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --purple: #32145c;
            --gold: #d6a448;
            --background: #f5f4f8;
        }

        body {
            background: var(--background);
            font-family: Arial, sans-serif;
            color: #383341;
        }

        .admin-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 245px;
            background: var(--purple);
            padding: 25px 16px;
            color: white;
            overflow-y: auto;
        }

        .admin-sidebar h4 {
            font-size: 20px;
            font-weight: 700;
        }

        .admin-sidebar small {
            color: #d8c9e7;
        }

        .menu-heading {
            color: #c9b8dc;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 25px 12px 10px;
        }

        .admin-sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 5px;
            border-radius: 7px;
            color: #eee7f5;
            text-decoration: none;
            font-size: 14px;
        }

        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: rgba(255,255,255,.15);
            color: white;
        }

        .admin-main {
            margin-left: 245px;
            padding: 30px;
        }

        .topbar,
        .content-card,
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 23px;
            box-shadow: 0 3px 15px rgba(0,0,0,.03);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topbar h4,
        .content-card h5 {
            color: var(--purple);
            font-weight: 700;
        }

        .stat-card {
            margin-bottom: 25px;
        }

        .stat-card h3 {
            color: var(--purple);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .stat-card p {
            color: #777;
            margin: 5px 0 0;
        }

        .btn-purple {
            background: var(--purple);
            color: white;
            border: none;
        }

        .btn-purple:hover {
            background: #4b2675;
            color: white;
        }

        .form-label {
            color: var(--purple);
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            min-height: 45px;
        }

        textarea.form-control {
            min-height: 110px;
        }

        .table th {
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table td {
            font-size: 13px;
            vertical-align: middle;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
        }

        @media(max-width:850px) {
            .admin-sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .admin-main {
                margin-left: 0;
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<aside class="admin-sidebar">
    <div class="mb-4 px-2">
        <h4>✝ ELCK KISUMU</h4>
        <small>Administration Portal</small>
    </div>

    <div class="menu-heading">MAIN MENU</div>
    <a href="index.php"><i class="bi bi-grid"></i> Dashboard</a>

    <div class="menu-heading">WEBSITE MANAGEMENT</div>
    <a href="members.php"><i class="bi bi-people"></i> Membership</a>
    <a href="sermons.php" class="active"><i class="bi bi-camera-video"></i> Sermons</a>
    <a href="events.php"><i class="bi bi-calendar-event"></i> Events</a>
    <a href="announcements.php"><i class="bi bi-megaphone"></i> Announcements</a>
    <a href="gallery.php"><i class="bi bi-images"></i> Gallery</a>
    <a href="leadership.php"><i class="bi bi-person-badge"></i> Leadership</a>

    <div class="menu-heading">COMMUNICATION</div>
    <!--<a href="prayer-requests.php"><i class="bi bi-heart"></i> Prayer Requests</a>-->
    <a href="messages.php"><i class="bi bi-envelope"></i> Contact Messages</a>

    <div class="menu-heading">SETTINGS</div>
    <a href="settings.php"><i class="bi bi-gear"></i> Church Settings</a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
</aside>

<main class="admin-main">

    <div class="topbar">
        <div>
            <h4>Sermons Management</h4>
            <small class="text-muted">Manage church sermons and YouTube videos.</small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= e($messageType) ?> alert-dismissible fade show">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="stat-card">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 rounded-3" style="background:#f1eaf8;color:#32145c;font-size:25px;">
                <i class="bi bi-camera-video"></i>
            </div>
            <div>
                <h3><?= $totalSermons ?></h3>
                <p>Total Sermons</p>
            </div>
        </div>
    </div>

    <div class="content-card mb-4">
        <h5 class="mb-4">
            <?= $editSermon ? 'Edit Sermon' : 'Add New Sermon' ?>
        </h5>

        <form method="POST" action="sermons.php<?= $editSermon ? '?edit=' . (int)$editSermon['id'] : '' ?>">

            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['sermons_csrf']) ?>">
            <input type="hidden" name="action" value="<?= $editSermon ? 'update' : 'create' ?>">

            <?php if ($editSermon): ?>
                <input type="hidden" name="sermon_id" value="<?= (int)$editSermon['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Sermon Title *</label>
                    <input type="text" name="title" class="form-control"
                           maxlength="200" required
                           value="<?= e($editSermon['title'] ?? '') ?>"
                           placeholder="Enter sermon title">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Preacher's Name</label>
                    <input type="text" name="preacher" class="form-control"
                           maxlength="150"
                           value="<?= e($editSermon['preacher'] ?? '') ?>"
                           placeholder="Enter preacher's name">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sermon Date *</label>
                    <input type="date" name="sermon_date" class="form-control"
                           required
                           value="<?= e($editSermon['sermon_date'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">YouTube Video URL</label>
                    <input type="url" name="video_url" class="form-control"
                           maxlength="500"
                           value="<?= e($editSermon['video_url'] ?? '') ?>"
                           placeholder="https://www.youtube.com/watch?v=...">
                    <small class="text-muted">Paste the full YouTube video link.</small>
                </div>

                <div class="col-12">
                    <label class="form-label">Sermon Description</label>
                    <textarea name="description" class="form-control"
                              placeholder="Write a short description of the sermon..."><?= e($editSermon['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-purple">
                        <i class="bi bi-save"></i>
                        <?= $editSermon ? 'Update Sermon' : 'Save Sermon' ?>
                    </button>

                    <?php if ($editSermon): ?>
                        <a href="sermons.php" class="btn btn-outline-secondary">Cancel</a>
                    <?php endif; ?>
                </div>

            </div>
        </form>
    </div>

    <div class="content-card">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h5 class="mb-0">All Sermons</h5>

            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Search sermons..."
                       value="<?= e($search) ?>">
                <button class="btn btn-purple" type="submit">
                    <i class="bi bi-search"></i>
                </button>
                <?php if ($search !== ''): ?>
                    <a href="sermons.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sermon Title</th>
                        <th>Preacher</th>
                        <th>Date</th>
                        <th>Video</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($sermons): ?>
                        <?php foreach ($sermons as $index => $sermon): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= e($sermon['title']) ?></strong></td>
                                <td><?= e($sermon['preacher'] ?: 'Not specified') ?></td>
                                <td><?= e(date('d M Y', strtotime($sermon['sermon_date']))) ?></td>
                                <td>
                                    <?php if (!empty($sermon['video_url'])): ?>
                                        <a href="<?= e($sermon['video_url']) ?>"
                                           target="_blank" rel="noopener noreferrer"
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-youtube"></i> Watch
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No video</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="sermons.php?edit=<?= (int)$sermon['id'] ?>"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form method="POST" action="sermons.php"
                                              onsubmit="return confirm('Delete this sermon permanently?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['sermons_csrf']) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="sermon_id" value="<?= (int)$sermon['id'] ?>">

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No sermons found. Add your first sermon above.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <small class="text-muted">
            Showing <?= count($sermons) ?> sermon(s).
        </small>
    </div>

    <p class="text-muted small mt-4">
        ELCK KISUMU Administration Portal &copy; <?= date('Y') ?>
    </p>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
