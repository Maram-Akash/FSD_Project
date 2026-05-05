<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('learner');

$pageTitle = 'Wishlist';
$currentPage = '';
$user = getCurrentUser();

$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT c.*, cat.name as category_name, u.full_name as instructor_name,
           COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
    FROM wishlists w
    JOIN courses c ON w.course_id = c.id
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN reviews r ON c.id = r.course_id
    WHERE w.user_id = ? AND c.is_published = 1
    GROUP BY c.id
");
$stmt->execute([$user['id']]);
$wishlist = $stmt->fetchAll();

// Remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $cid = (int)$_POST['remove'];
    $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND course_id = ?")->execute([$user['id'], $cid]);
    setFlash('success', 'Removed from wishlist.');
    header('Location: ' . base_url('learner/wishlist.php'));
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1>My Wishlist</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Courses you want to take later.</p>

            <?php if (empty($wishlist)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 3rem;">Your wishlist is empty. <a href="<?= base_url('public/catalog.php') ?>">Browse courses</a></p>
            <?php else: ?>
            <div class="card-grid">
                <?php foreach ($wishlist as $course): ?>
                <article class="course-card">
                    <div class="course-card-image">
                        <?= $course['thumbnail_url'] ? '<img src="' . htmlspecialchars($course['thumbnail_url']) . '" alt="" style="width:100%;height:100%;object-fit:cover">' : '📖' ?>
                    </div>
                    <div class="course-card-body">
                        <span class="course-meta"><?= htmlspecialchars($course['category_name']) ?></span>
                        <h3><a href="<?= base_url('public/course-detail.php?id=' . $course['id']) ?>"><?= htmlspecialchars($course['title']) ?></a></h3>
                        <p class="course-meta">By <?= htmlspecialchars($course['instructor_name']) ?></p>
                        <p class="course-price"><?= $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free' ?></p>
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                            <?php if ((float)$course['price'] > 0): ?>
                                <a href="<?= base_url('learner/checkout.php?course_id=' . $course['id']) ?>" class="btn btn-primary btn-sm">Enroll Now</a>
                            <?php else: ?>
                                <a href="<?= base_url('learner/enroll.php?course_id=' . $course['id']) ?>" class="btn btn-primary btn-sm">Enroll Now</a>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="remove" value="<?= $course['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm">Remove</button>
                            </form>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
