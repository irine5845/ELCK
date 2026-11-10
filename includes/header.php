
<?php
$page_title = $page_title ?? 'ELCK KISUMU | Welcome';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title) ?></title>

    <meta name="description" content="Welcome to ELCK KISUMU. Join us for worship, fellowship, spiritual growth and community service.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<!-- Top contact bar -->
<div class="top-bar">
    <div class="container d-flex justify-content-between flex-wrap">
        <div>
            <span>Welcome to ELCK KISUMU</span>
        </div>

        <div>
            <span>📍 Kisumu, Kenya</span>
            <span class="ms-3">✉ Contact the Church</span>
        </div>
    </div>
</div>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            <span class="brand-symbol">✝</span>
            <span>
                ELCK KISUMU
                <small>Worship • Faith • Fellowship</small>
            </span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>"
                       href="index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'about.php' ? 'active' : '' ?>"
                       href="about.php">About Us</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'leadership.php' ? 'active' : '' ?>"
                       href="leadership.php">Leadership</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'sermons.php' ? 'active' : '' ?>"
                       href="sermons.php">Sermons</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'events.php' ? 'active' : '' ?>"
                       href="events.php">Events</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'gallery.php' ? 'active' : '' ?>"
                       href="gallery.php">Gallery</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'contact.php' ? 'active' : '' ?>"
                       href="contact.php">Contact</a>
                </li>

                <li class="nav-item ms-lg-3">
                    <a href="giving.php" class="btn btn-gold">
                        Give Now
                    </a>
                </li>

            </ul>

        </div>
    </div>
</nav>

<main>
