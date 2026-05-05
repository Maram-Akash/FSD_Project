<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ' . base_url('auth/login.php') . '?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? base_url('public/catalog.php')));
    exit;
}

$courseId = (int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? base_url('public/catalog.php');

if (!$courseId) {
    header('Location: ' . $redirect);
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $courseId]);
if ($stmt->fetch()) {
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND course_id = ?")->execute([$_SESSION['user_id'], $courseId]);
    setFlash('info', 'Removed from wishlist.');
} else {
    $pdo->prepare("INSERT IGNORE INTO wishlists (user_id, course_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $courseId]);
    setFlash('success', 'Added to wishlist!');
}

header('Location: ' . $redirect);
exit;
