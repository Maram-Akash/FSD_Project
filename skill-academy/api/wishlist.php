<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$action = $_GET['action'] ?? '';
$courseId = (int)($_GET['course_id'] ?? 0);

if (!$courseId) {
    echo json_encode(['success' => false, 'error' => 'Invalid course']);
    exit;
}

$pdo = getDBConnection();

// Check course exists
$stmt = $pdo->prepare("SELECT 1 FROM courses WHERE id = ? AND is_published = 1");
$stmt->execute([$courseId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Course not found']);
    exit;
}

if ($action === 'toggle') {
    $stmt = $pdo->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND course_id = ?")->execute([$_SESSION['user_id'], $courseId]);
        echo json_encode(['success' => true, 'in_wishlist' => false]);
    } else {
        $pdo->prepare("INSERT INTO wishlists (user_id, course_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $courseId]);
        echo json_encode(['success' => true, 'in_wishlist' => true]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
