
<?php
require_once 'auth.php';

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['members_csrf'])) {
    $_SESSION['members_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = 'success';

// Handle update and delete actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['members_csrf'], $token)) {
        $message = 'Your session has expired. Refresh the page and try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        $memberId = filter_var($_POST['member_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$memberId || $memberId < 1) {
            $message = 'Invalid member selected.';
            $messageType = 'danger';
        } elseif ($action === 'delete') {
            try {
                $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
                $stmt->execute([$memberId]);

                $message = $stmt->rowCount()
                    ? 'Member registration deleted successfully.'
                    : 'The selected member could not be found.';
                $messageType = $stmt->rowCount() ? 'success' : 'warning';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $message = 'Unable to delete this registration.';
                $messageType = 'danger';
            }
        } elseif ($action === 'update') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $gender = trim($_POST['gender'] ?? '');
            $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
            $address = trim($_POST['address'] ?? '');

            $validGender = in_array(
                $gender,
                ['Male', 'Female', 'Prefer not to say'],
                true
            );

            $validDate = true;

            if ($dateOfBirth !== '') {
                $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)
                    && checkdate(
                        (int)substr($dateOfBirth, 5, 2),
                        (int)substr($dateOfBirth, 8, 2),
                        (int)substr($dateOfBirth, 0, 4)
                    )
                    && $dateOfBirth <= date('Y-m-d');
            }

            if (
                $fullName === '' ||
                $phone === '' ||
                !$validGender ||
                !$validDate ||
                strlen($fullName) > 150 ||
                strlen($phone) > 30 ||
                strlen($address) > 255 ||
                ($email !== '' && (
                    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                    strlen($email) > 150
                ))
            ) {
                $message = 'Please check the member details. Some information is missing or invalid.';
                $messageType = 'danger';
            } else {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE members
                        SET full_name = ?,
                            email = ?,
                            phone = ?,
                            gender = ?,
                            date_of_birth = ?,
                            address = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $fullName,
                        $email !== '' ? $email : null,
                        $phone,
                        $gender,
                        $dateOfBirth !== '' ? $dateOfBirth : null,
                        $address !== '' ? $address : null,
                        $memberId
                    ]);

                    $message = 'Member information updated successfully.';
                    $messageType = 'success';

                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $message = 'Unable to update member information.';
                    $messageType = 'danger';
                }
            }
        } else {
            $message = 'Invalid action.';
            $messageType = 'danger';
        }
    }

    $_SESSION['members_csrf'] = bin2hex(random_bytes(32));
}

// Search members.
$search = trim($_GET['search'] ?? '');

try {
    if ($search !== '') {
        $stmt = $pdo->prepare("
            SELECT *
            FROM members
            WHERE full_name LIKE ?
               OR email LIKE ?
               OR phone LIKE ?
            ORDER BY id DESC
        ");

        $term = "%$search%";
        $stmt->execute([$term, $term, $term]);
        $members = $stmt->fetchAll();
    } else {
        $members = $pdo->query("
            SELECT *
            FROM members
            ORDER BY id DESC
        ")->fetchAll();
    }

    $totalMembers = (int)$pdo->query(
        "SELECT COUNT(*) FROM members"
    )->fetchColumn();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $members = [];
    $totalMembers = 0;
    $message = 'Unable to load membership records.';
    $messageType = 'danger';
}

// Load selected member for editing or viewing.
$selectedMember = null;
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
$viewId = filter_var($_GET['view'] ?? null, FILTER_VALIDATE_INT);

$selectedId = $editId ?: $viewId;

if ($selectedId && $selectedId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$selectedId]);
    $selectedMember = $stmt->fetch();

    if (!$selectedMember) {
        $message = 'The selected member could not be found.';
        $messageType = 'warning';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membership Management | ELCK KISUMU</title>

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
            color: #fff;
            overflow-y: auto;
        }

        .admin-sidebar h4 {
            font-size: 20px;
            font-weight: 700;
        }

        .admin-sidebar small {
            color: #d8c9e7;
        }

        .admin-sidebar .menu-heading {
            color: #c9b8dc;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 25px 12px 10px;
        }

        .admin-sidebar a {
            display: flex;
            gap: 12px;
            align-items: center;
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
            color: #fff;
        }

        .admin-main {
            margin-left: 245px;
            padding: 30px;
        }

        .topbar,
        .content-card {
            background: #fff;
            border-radius: 10px;
            padding: 23px;
            box-shadow: 0 3px 15px rgba(0,0,0,.03);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }

        .topbar h4,
        .content-card h5 {
            color: var(--purple);
            font-weight: 700;
        }

        .stat-box {
            background: #fff;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,.03);
            margin-bottom: 25px;
        }

        .stat-box h3 {
            color: var(--purple);
            font-size: 28px;
            font-weight: 700;
        }

        .stat-box p {
            color: #777;
            margin: 0;
        }

        .search-form .form-control {
            min-height: 43px;
        }

        .btn-purple {
            background: var(--purple);
            color: #fff;
            border: none;
        }

        .btn-purple:hover {
            background: #4b2675;
            color: #fff;
        }

        .table th {
            font-size: 12px;
            color: #777;
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

        .form-label {
            font-weight: 600;
            color: var(--purple);
            font-size: 14px;
        }

        .form-control,
        .form-select {
            min-height: 45px;
        }

        @media(max-width: 850px) {
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
    <a href="members.php" class="active"><i class="bi bi-people"></i> Membership</a>
    <a href="sermons.php"><i class="bi bi-camera-video"></i> Sermons</a>
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
            <h4>Membership Management</h4>
            <small class="text-muted">View and manage church membership registrations.</small>
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

    <div class="stat-box">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background:#f1eaf8;color:#32145c;font-size:25px;">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <h3 class="mb-0"><?= $totalMembers ?></h3>
                <p>Total Registered Members</p>
            </div>
        </div>
    </div>

    <?php if ($selectedMember): ?>

        <div class="content-card mb-4">
            <?php if ($editId): ?>

                <h5 class="mb-4">Edit Member Information</h5>

                <form method="POST" action="members.php">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['members_csrf']) ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="member_id" value="<?= (int)$selectedMember['id'] ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control"
                                   maxlength="150" value="<?= e($selectedMember['full_name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control"
                                   maxlength="150" value="<?= e($selectedMember['email']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number *</label>
                            <input type="text" name="phone" class="form-control"
                                   maxlength="30" value="<?= e($selectedMember['phone']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male" <?= $selectedMember['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $selectedMember['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Prefer not to say" <?= $selectedMember['gender'] === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control"
                                   max="<?= date('Y-m-d') ?>" value="<?= e($selectedMember['date_of_birth']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Residential Address</label>
                            <input type="text" name="address" class="form-control"
                                   maxlength="255" value="<?= e($selectedMember['address']) ?>">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-purple">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                            <a href="members.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>

            <?php else: ?>

                <h5 class="mb-4">Member Details</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Full Name</strong>
                        <p><?= e($selectedMember['full_name']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email</strong>
                        <p><?= e($selectedMember['email'] ?: 'Not provided') ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Phone</strong>
                        <p><?= e($selectedMember['phone']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Gender</strong>
                        <p><?= e($selectedMember['gender'] ?: 'Not provided') ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date of Birth</strong>
                        <p><?= e($selectedMember['date_of_birth'] ?: 'Not provided') ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Address</strong>
                        <p><?= e($selectedMember['address'] ?: 'Not provided') ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Registration Date</strong>
                        <p><?= e($selectedMember['created_at']) ?></p>
                    </div>
                </div>

                <a href="members.php?edit=<?= (int)$selectedMember['id'] ?>" class="btn btn-purple">
                    <i class="bi bi-pencil"></i> Edit Member
                </a>

                <a href="members.php" class="btn btn-outline-secondary">Back to Members</a>

            <?php endif; ?>
        </div>

    <?php endif; ?>

    <div class="content-card">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h5 class="mb-0">Registered Members</h5>

            <form method="GET" class="search-form d-flex gap-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Search name, email or phone"
                       value="<?= e($search) ?>">
                <button class="btn btn-purple" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>

                <?php if ($search !== ''): ?>
                    <a href="members.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($members): ?>
                        <?php foreach ($members as $index => $member): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= e($member['full_name']) ?></strong></td>
                                <td><?= e($member['email'] ?: '—') ?></td>
                                <td><?= e($member['phone']) ?></td>
                                <td><?= e($member['gender'] ?: '—') ?></td>
                                <td><?= e(date('d M Y', strtotime($member['created_at']))) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="members.php?view=<?= (int)$member['id'] ?>"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="members.php?edit=<?= (int)$member['id'] ?>"
                                           class="btn btn-sm btn-outline-success" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form method="POST" action="members.php"
                                              onsubmit="return confirm('Are you sure you want to delete this member registration?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['members_csrf']) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="member_id" value="<?= (int)$member['id'] ?>">

                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No membership registrations found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <small class="text-muted">
            Showing <?= count($members) ?> registration(s).
        </small>

    </div>

    <p class="text-muted small mt-4">
        ELCK KISUMU Administration Portal &copy; <?= date('Y') ?>
    </p>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
