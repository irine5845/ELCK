
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">

    <div class="sidebar-brand">
        <h4>✝ ELCK KISUMU</h4>
        <small>Administration Portal</small>
    </div>

    <div class="sidebar-label">MAIN MENU</div>

    <a href="index.php"
       class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-grid"></i> Dashboard
    </a>

    <a href="../index.php" target="_blank">
        <i class="bi bi-globe"></i> View Website
    </a>

    <div class="sidebar-label">WEBSITE MANAGEMENT</div>

    <a href="members.php"
       class="<?= $currentPage === 'members.php' ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Membership
    </a>

    <a href="sermons.php"
       class="<?= $currentPage === 'sermons.php' ? 'active' : '' ?>">
        <i class="bi bi-camera-video"></i> Sermons
    </a>

    <a href="events.php"
       class="<?= $currentPage === 'events.php' ? 'active' : '' ?>">
        <i class="bi bi-calendar-event"></i> Events
    </a>

    <a href="announcements.php"
       class="<?= $currentPage === 'announcements.php' ? 'active' : '' ?>">
        <i class="bi bi-megaphone"></i> Announcements
    </a>

    <a href="gallery.php"
       class="<?= $currentPage === 'gallery.php' ? 'active' : '' ?>">
        <i class="bi bi-images"></i> Gallery
    </a>

    <a href="leadership.php"
       class="<?= $currentPage === 'leadership.php' ? 'active' : '' ?>">
        <i class="bi bi-person-badge"></i> Church Leadership
    </a>

    <div class="sidebar-label">COMMUNICATION</div>

    <a href="messages.php"
       class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>">
        <i class="bi bi-envelope"></i> Contact Messages
    </a>

    <div class="sidebar-label">SETTINGS</div>

    <a href="settings.php"
       class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
        <i class="bi bi-gear"></i> Church Settings
    </a>

    <a href="logout.php" class="logout-link">
        <i class="bi bi-box-arrow-right"></i> Sign Out
    </a>

</aside>
