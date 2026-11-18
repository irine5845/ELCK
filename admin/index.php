
<?php
require_once 'auth.php';

// Get dashboard statistics.
function getCount(PDO $pdo, string $table): int {
    $allowedTables = [
        'members',
        'sermons',
        'events',
        'gallery',
        'prayer_requests',
        'announcements',
        'contact_messages'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    try {
        return (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return 0;
    }
}

$membersCount = getCount($pdo, 'members');
$sermonsCount = getCount($pdo, 'sermons');
$eventsCount = getCount($pdo, 'events');
$galleryCount = getCount($pdo, 'gallery');
$prayersCount = getCount($pdo, 'prayer_requests');
$announcementsCount = getCount($pdo, 'announcements');
$messagesCount = getCount($pdo, 'contact_messages');

try {
    $recentMembers = $pdo->query("
        SELECT full_name, phone, created_at
        FROM members
        ORDER BY id DESC
        LIMIT 5
    ")->fetchAll();

    $recentPrayers = $pdo->query("
        SELECT full_name, request, status, created_at
        FROM prayer_requests
        ORDER BY id DESC
        LIMIT 5
    ")->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $recentMembers = [];
    $recentPrayers = [];
}

function e($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | ELCK KISUMU</title>

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

        .topbar {
            background: #fff;
            padding: 18px 22px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 28px;
            box-shadow: 0 3px 15px rgba(0,0,0,.03);
        }

        .topbar h5 {
            color: var(--purple);
            font-weight: 700;
            margin: 0;
        }

        .welcome-text {
            color: #777;
            font-size: 13px;
            margin-top: 5px;
        }

        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 23px;
            height: 100%;
            box-shadow: 0 3px 15px rgba(0,0,0,.03);
        }

        .stat-card .stat-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #f1eaf8;
            color: var(--purple);
            font-size: 21px;
            margin-bottom: 17px;
        }

        .stat-card h3 {
            font-size: 29px;
            color: var(--purple);
            font-weight: 700;
            margin: 0 0 5px;
        }

        .stat-card p {
            color: #777;
            font-size: 13px;
            margin: 0;
        }

        .dashboard-panel {
            background: #fff;
            border-radius: 10px;
            padding: 23px;
            height: 100%;
            box-shadow: 0 3px 15px rgba(0,0,0,.03);
        }

        .dashboard-panel h5 {
            color: var(--purple);
            font-weight: 700;
            margin-bottom: 20px;
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

        .quick-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            color: var(--purple);
            text-decoration: none;
            border: 1px solid #eeeaf2;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
        }

        .quick-link:hover {
            background: #f8f5fb;
            border-color: #d6c5e8;
        }

        .quick-link i {
            font-size: 20px;
            color: var(--gold);
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

    <a href="index.php" class="active">
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

    <!--  <a href="prayer-requests.php">
        <i class="bi bi-heart"></i> Prayer Requests
    </a>
    -->

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

<main class="main-content">

    <div class="topbar">
        <div>
            <h5>Dashboard Overview</h5>
            <div class="welcome-text">
                Welcome back, <?= e($_SESSION['admin_name']) ?>.
            </div>
        </div>

        <div class="text-end">
            <span class="badge text-bg-light">
                <?= e(ucwords(str_replace('_', ' ', $_SESSION['admin_role'] ?? 'editor'))) ?>
            </span>
        </div>
    </div>

    <div class="mb-4">
        <h4 class="fw-bold" style="color:#32145c;">Church Website Overview</h4>
        <p class="text-muted">Monitor and manage your church website from one place.</p>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <h3><?= $membersCount ?></h3>
                <p>Registered Members</p>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-camera-video"></i></div>
                <h3><?= $sermonsCount ?></h3>
                <p>Published Sermons</p>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
                <h3><?= $eventsCount ?></h3>
                <p>Church Events</p>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-images"></i></div>
                <h3><?= $galleryCount ?></h3>
                <p>Gallery Images</p>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-heart"></i></div>
                <h3><?= $prayersCount ?></h3>
                <p>Prayer Requests</p>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-megaphone"></i></div>
                <h3><?= $announcementsCount ?></h3>
                <p>Announcements</p>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-envelope"></i></div>
                <h3><?= $messagesCount ?></h3>
                <p>Contact Messages</p>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="dashboard-panel">
                <h5><i class="bi bi-person-plus me-2"></i>Recent Membership Registrations</h5>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Date Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentMembers): ?>
                                <?php foreach ($recentMembers as $member): ?>
                                    <tr>
                                        <td><?= e($member['full_name']) ?></td>
                                        <td><?= e($member['phone']) ?></td>
                                        <td><?= e(date('d M Y', strtotime($member['created_at']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-muted text-center">
                                        No membership registrations yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="dashboard-panel">
                <h5><i class="bi bi-lightning me-2"></i>Quick Access</h5>

                <a href="members.php" class="quick-link">
                    <i class="bi bi-people"></i> Manage Membership
                </a>

                <a href="sermons.php" class="quick-link">
                    <i class="bi bi-camera-video"></i> Add a Sermon
                </a>

                <a href="events.php" class="quick-link">
                    <i class="bi bi-calendar-plus"></i> Create an Event
                </a>

                <a href="gallery.php" class="quick-link">
                    <i class="bi bi-cloud-upload"></i> Upload Gallery Images
                </a>

                <a href="prayer-requests.php" class="quick-link">
                    <i class="bi bi-heart"></i> View Prayer Requests
                </a>
            </div>
        </div>

        <div class="col-12">
            <div class="dashboard-panel">
                <h5><i class="bi bi-chat-heart me-2"></i>Recent Prayer Requests</h5>

                <?php if ($recentPrayers): ?>
                    <?php foreach ($recentPrayers as $prayer): ?>
                        <div class="border-bottom py-3">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <strong><?= e($prayer['full_name'] ?: 'Anonymous') ?></strong>
                                <span class="badge text-bg-light">
                                    <?= e(ucwords(str_replace('_', ' ', $prayer['status']))) ?>
                                </span>
                            </div>

                            <p class="text-muted mb-1 mt-2">
                                <?= e(mb_strimwidth($prayer['request'], 0, 180, '...')) ?>
                            </p>

                            <small class="text-muted">
                                <?= e(date('d M Y', strtotime($prayer['created_at']))) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No prayer requests have been submitted yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <p class="text-muted small mt-4">
        ELCK KISUMU Administration Portal &copy; <?= date('Y') ?>
    </p>

</main>

</body>
</html>
