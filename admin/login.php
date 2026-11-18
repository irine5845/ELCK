
<?php
session_start();

require_once '../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if (empty($_SESSION['admin_login_csrf'])) {
    $_SESSION['admin_login_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!hash_equals($_SESSION['admin_login_csrf'], $token)) {
        $error = 'Your session has expired. Refresh the page and try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT id, full_name, email, password, role
                FROM admins
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];

                unset($_SESSION['admin_login_csrf']);

                header('Location: index.php');
                exit;
            } else {
                $error = 'Incorrect email or password.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Login is temporarily unavailable. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | ELCK KISUMU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #32145c, #63418a);
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .login-card {
            background: #fff;
            width: 100%;
            max-width: 430px;
            padding: 42px 35px;
            border-radius: 14px;
            box-shadow: 0 15px 45px rgba(0,0,0,.18);
        }

        .login-logo {
            width: 65px;
            height: 65px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #f5e9c9;
            color: #32145c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .login-card h2 {
            color: #32145c;
            font-weight: 700;
        }

        .login-card .form-control {
            min-height: 49px;
        }

        .login-button {
            width: 100%;
            background: #32145c;
            color: white;
            padding: 13px;
            border: 0;
            border-radius: 6px;
            font-weight: 600;
        }

        .login-button:hover {
            background: #4b2675;
            color: white;
        }

        .back-link {
            color: #63418a;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="login-card">

    <div class="text-center">
        <div class="login-logo">✝</div>
        <h2>ELCK KISUMU</h2>
        <p class="text-muted mb-4">Administrator Portal</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['admin_login_csrf']) ?>"
        >

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input
                type="email"
                name="email"
                class="form-control"
                autocomplete="username"
                required
            >
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="login-button">
            Sign In
        </button>

    </form>

    <div class="text-center mt-4">
        <a href="../index.php" class="back-link">← Back to Church Website</a>
    </div>

</div>

</body>
</html>
