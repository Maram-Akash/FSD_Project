<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($pageTitle)) $pageTitle = 'Skill Learning Academy';
if (!isset($currentPage)) $currentPage = '';
$user = isset($user) ? $user : (function_exists('getCurrentUser') ? getCurrentUser() : null);
$base = rtrim(BASE_PATH, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Skill Learning Academy</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <a href="<?= $base ?>/index.php" class="nav-brand">
            <span class="brand-icon">📚</span>
            Skill Academy
        </a>
        <ul class="nav-links">
            <li><a href="<?= $base ?>/index.php" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= $base ?>/public/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a></li>
            <li><a href="<?= $base ?>/public/catalog.php" class="<?= $currentPage === 'catalog' ? 'active' : '' ?>">Courses</a></li>
            <li><a href="<?= $base ?>/public/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'learner'): ?>
                <li><a href="<?= $base ?>/learner/dashboard.php">Dashboard</a></li>
                <li><a href="<?= $base ?>/learner/wishlist.php">Wishlist</a></li>
                <?php elseif ($user['role'] === 'instructor'): ?>
                <li><a href="<?= $base ?>/instructor/dashboard.php">Instructor</a></li>
                <?php elseif ($user['role'] === 'admin'): ?>
                <li><a href="<?= $base ?>/admin/dashboard.php">Admin</a></li>
                <?php endif; ?>
                <li class="nav-user">
                    <a href="<?= $base ?>/learner/profile.php" class="user-link" title="Profile">
                        <span class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
                        <?= htmlspecialchars($user['full_name']) ?>
                    </a>
                    <a href="<?= $base ?>/auth/logout.php" class="btn btn-outline btn-sm">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="<?= $base ?>/auth/login.php">Login</a></li>
                <li><a href="<?= $base ?>/auth/register.php" class="btn btn-primary btn-sm">Sign Up</a></li>
            <?php endif; ?>
        </ul>
        <button class="nav-toggle" aria-label="Toggle menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <?php 
    $flash = function_exists('getFlash') ? getFlash() : null;
    if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
        <?= htmlspecialchars($flash['message']) ?>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php endif; ?>
