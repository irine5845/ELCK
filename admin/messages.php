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

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Please refresh the page.';
    } else {
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        try {
            if ($id <= 0) {
                throw new RuntimeException('Invalid message selected.');
            }

            if ($action === 'update_status') {
                $status = $_POST['status'] ?? '';
                $allowedStatuses = ['unread', 'read', 'replied'];

                if (!in_array($status, $allowedStatuses, true)) {
                    throw new RuntimeException('Invalid message status.');
                }

                $stmt = $pdo->prepare("
                    UPDATE contact_messages
                    SET status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$status, $id]);
                $message = 'Message status updated successfully.';
            }

            if ($action === 'delete') {
                $stmt = $pdo->prepare("
                    DELETE FROM contact_messages
                    WHERE id = ?
                ");
                $stmt->execute([$id]);
                $message = 'Message deleted successfully.';
            }
        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }
    }
}

// Load selected message
$selectedMessage = null;

if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("
        SELECT * FROM contact_messages
        WHERE id = ?
    ");
    $stmt->execute([(int)$_GET['view']]);
    $selectedMessage = $stmt->fetch();

    // Automatically mark unread messages as read
    if ($selectedMessage && $selectedMessage['status'] === 'unread') {
        $stmt = $pdo->prepare("
            UPDATE contact_messages
            SET status = 'read'
            WHERE id = ?
        ");
        $stmt->execute([$selectedMessage['id']]);
        $selectedMessage['status'] = 'read';
    }
}

// Search messages
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

if (!in_array($statusFilter, ['unread', 'read', 'replied'], true)) {
    $statusFilter = '';
}

$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (
        full_name LIKE ?
        OR email LIKE ?
        OR subject LIKE ?
        OR message LIKE ?
    )";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if ($statusFilter !== '') {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Message statistics
$totalMessages = (int)$pdo->query(
    "SELECT COUNT(*) FROM contact_messages"
)->fetchColumn();

$unreadMessages = (int)$pdo->query(
    "SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'"
)->fetchColumn();

$repliedMessages = (int)$pdo->query(
    "SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Management | ELCK Kisumu</title>

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
            text-align: center;
        }

        .main-content {
            margin-left: 250px;
            padding: 28px;
            min-height: 100vh;
        }

        .page-heading h2 {
            color: var(--purple);
            font-weight: 700;
        }

        .stat-card,
        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 3px 15px rgba(0,0,0,.04);
        }

        .stat-card {
            height: 100%;
            border-left: 4px solid var(--gold);
        }

        .stat-card h3 {
            color: var(--purple);
            font-weight: 700;
            margin-bottom: 0;
        }

        .panel {
            margin-bottom: 25px;
        }

        .btn-purple {
            background: var(--purple);
            color: #fff;
            border-color: var(--purple);
        }

        .btn-purple:hover,
        .btn-purple:focus {
            background: #241044;
            color: #fff;
            border-color: #241044;
        }

        .message-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.8;
        }

        .logout-link {
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,.15);
            padding-top: 20px !important;
        }

        .table thead th {
            color: var(--purple);
            white-space: nowrap;
        }

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
    <a href="messages.php" class="active">
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

<main class="main-content">
    <div class="page-heading mb-4">
        <h2>Contact Messages</h2>
        <p class="text-muted mb-0">
            Manage messages submitted through the church website.
        </p>
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

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <p class="text-muted mb-1">Total Messages</p>
                <h3><?= $totalMessages ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <p class="text-muted mb-1">Unread Messages</p>
                <h3><?= $unreadMessages ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <p class="text-muted mb-1">Replied Messages</p>
                <h3><?= $repliedMessages ?></h3>
            </div>
        </div>
    </div>

    <!-- Selected message details -->
    <?php if ($selectedMessage): ?>
        <div class="panel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <h5 class="mb-0">Message Details</h5>
                <a href="messages.php" class="btn btn-outline-secondary btn-sm">
                    Back to Messages
                </a>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <strong>Sender:</strong>
                    <p><?= e($selectedMessage['full_name']) ?></p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Email:</strong>
                    <p>
                        <a href="mailto:<?= e($selectedMessage['email']) ?>">
                            <?= e($selectedMessage['email']) ?>
                        </a>
                    </p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Phone:</strong>
                    <p><?= e($selectedMessage['phone'] ?: 'Not provided') ?></p>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Date Received:</strong>
                    <p>
                        <?= e(date(
                            'd M Y, h:i A',
                            strtotime($selectedMessage['created_at'])
                        )) ?>
                    </p>
                </div>

                <div class="col-12 mb-3">
                    <strong>Subject:</strong>
                    <p><?= e($selectedMessage['subject']) ?></p>
                </div>

                <div class="col-12 mb-3">
                    <strong>Message:</strong>
                    <div class="message-content bg-light p-3 rounded mt-2">
                        <?= e($selectedMessage['message']) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <strong>Current Status:</strong>
                    <p class="mt-2">
                        <?php if ($selectedMessage['status'] === 'unread'): ?>
                            <span class="badge bg-danger">Unread</span>
                        <?php elseif ($selectedMessage['status'] === 'read'): ?>
                            <span class="badge bg-primary">Read</span>
                        <?php else: ?>
                            <span class="badge bg-success">Replied</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="mailto:<?= e($selectedMessage['email']) ?>?subject=Re: <?= rawurlencode($selectedMessage['subject']) ?>"
                   class="btn btn-purple">
                    <i class="bi bi-reply"></i> Reply by Email
                </a>

                <form method="POST" class="d-flex flex-wrap gap-2">
                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id"
                           value="<?= (int)$selectedMessage['id'] ?>">

                    <select name="status" class="form-select">
                        <option value="unread"
                            <?= $selectedMessage['status'] === 'unread' ? 'selected' : '' ?>>
                            Unread
                        </option>
                        <option value="read"
                            <?= $selectedMessage['status'] === 'read' ? 'selected' : '' ?>>
                            Read
                        </option>
                        <option value="replied"
                            <?= $selectedMessage['status'] === 'replied' ? 'selected' : '' ?>>
                            Replied
                        </option>
                    </select>

                    <button class="btn btn-success" type="submit">
                        Update Status
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Message list -->
    <div class="panel">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h5 class="mb-0">All Messages</h5>

            <form method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Search messages..."
                       value="<?= e($search) ?>">

                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="unread"
                        <?= $statusFilter === 'unread' ? 'selected' : '' ?>>
                        Unread
                    </option>
                    <option value="read"
                        <?= $statusFilter === 'read' ? 'selected' : '' ?>>
                        Read
                    </option>
                    <option value="replied"
                        <?= $statusFilter === 'replied' ? 'selected' : '' ?>>
                        Replied
                    </option>
                </select>

                <button class="btn btn-purple" type="submit">
                    <i class="bi bi-funnel"></i> Filter
                </button>

                <a href="messages.php" class="btn btn-outline-secondary">
                    Reset
                </a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td>
                                <strong><?= e($msg['full_name']) ?></strong>
                                <div class="small text-muted">
                                    <?= e($msg['email']) ?>
                                </div>
                            </td>

                            <td><?= e($msg['subject']) ?></td>

                            <td>
                                <?= e(date(
                                    'd M Y',
                                    strtotime($msg['created_at'])
                                )) ?>
                            </td>

                            <td>
                                <?php if ($msg['status'] === 'unread'): ?>
                                    <span class="badge bg-danger">Unread</span>
                                <?php elseif ($msg['status'] === 'read'): ?>
                                    <span class="badge bg-primary">Read</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Replied</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a href="messages.php?view=<?= (int)$msg['id'] ?>"
                                   class="btn btn-sm btn-purple mb-1">
                                    <i class="bi bi-eye"></i> View
                                </a>

                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Permanently delete this message?')">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id"
                                           value="<?= (int)$msg['id'] ?>">

                                    <button type="submit"
                                            class="btn btn-sm btn-danger mb-1">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No contact messages found.
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
