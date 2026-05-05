<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('learner');

$courseId = (int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
if (!$courseId) {
    header('Location: ' . base_url('public/catalog.php'));
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT id, title, price FROM courses WHERE id = ? AND is_published = 1");
$stmt->execute([$courseId]);
$course = $stmt->fetch();
if (!$course) {
    setFlash('danger', 'Course not found.');
    header('Location: ' . base_url('public/catalog.php'));
    exit;
}

// Paid courses should use checkout flow
if ((float)$course['price'] > 0) {
    header('Location: ' . base_url('learner/checkout.php?course_id=' . $courseId));
    exit;
}

// Already enrolled?
$check = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
$check->execute([$user['id'], $courseId]);
if ($check->fetch()) {
    setFlash('info', 'You are already enrolled in this course.');
    header('Location: ' . base_url('learner/course.php?id=' . $courseId));
    exit;
}

// Enroll (free)
$pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)")->execute([$user['id'], $courseId]);
setFlash('success', 'Course enrolled successfully! You can start learning now.');
header('Location: ' . base_url('learner/course.php?id=' . $courseId));
exit;

