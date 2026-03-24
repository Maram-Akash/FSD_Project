<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$courseId = (int)($_POST['course_id'] ?? $_GET['course_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$reviewText = trim($_POST['review_text'] ?? '');

if (!$courseId || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$pdo = getDBConnection();

// Must be enrolled to review
$check = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
$check->execute([$_SESSION['user_id'], $courseId]);
if (!$check->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Enroll first to review']);
    exit;
}

$stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $courseId]);
if ($stmt->fetch()) {
    $pdo->prepare("UPDATE reviews SET rating = ?, review_text = ? WHERE user_id = ? AND course_id = ?")->execute([$rating, $reviewText, $_SESSION['user_id'], $courseId]);
} else {
    $pdo->prepare("INSERT INTO reviews (user_id, course_id, rating, review_text) VALUES (?, ?, ?, ?)")->execute([$_SESSION['user_id'], $courseId, $rating, $reviewText]);
}

echo json_encode(['success' => true]);
?>
