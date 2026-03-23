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

$stmt = $pdo->prepare("SELECT c.*, cat.name as category_name, u.full_name as instructor_name FROM courses c JOIN categories cat ON c.category_id = cat.id JOIN users u ON c.instructor_id = u.id WHERE c.id = ? AND c.is_published = 1");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Course not found.');
    header('Location: ' . base_url('public/catalog.php'));
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

$pageTitle = 'Checkout';
$currentPage = '';

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($course['price'] > 0) {
        // Dummy payment - in production integrate Stripe/PayPal
        $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, payment_reference) VALUES (?, ?, 'completed', 'dummy', ?)");
        $orderStmt->execute([$user['id'], $course['price'], 'DUMMY-' . uniqid()]);
        $orderId = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO order_items (order_id, course_id, price) VALUES (?, ?, ?)")->execute([$orderId, $courseId, $course['price']]);
    }
    $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)")->execute([$user['id'], $courseId]);
    setFlash('success', 'Enrollment successful! You can now access the course.');
    header('Location: ' . base_url('learner/course.php?id=' . $courseId));
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container" style="max-width: 500px;">
            <h1>Checkout</h1>
            <div class="dash-card">
                <h3><?= htmlspecialchars($course['title']) ?></h3>
                <p class="course-meta">By <?= htmlspecialchars($course['instructor_name']) ?></p>
                <p class="course-price" style="font-size: 1.5rem;"><?= $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free' ?></p>
                <form method="POST">
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <?php if ($course['price'] > 0): ?>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">This is a demo. Payment will be processed via dummy gateway.</p>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><?= $course['price'] > 0 ? 'Complete Purchase' : 'Enroll for Free' ?></button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
