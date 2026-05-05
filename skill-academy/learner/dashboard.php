<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('learner');

$pageTitle = 'Learner Dashboard';
$currentPage = '';
$user = getCurrentUser();

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT e.*, c.title, c.thumbnail_url, c.duration_hours, cat.name as category_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN categories cat ON c.category_id = cat.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$user['id']]);
$enrollments = $stmt->fetchAll();

// Progress: count completed lessons
foreach ($enrollments as &$e) {
    $lc = $pdo->prepare("SELECT COUNT(*) FROM lesson_completions lc JOIN lessons l ON lc.lesson_id = l.id WHERE lc.user_id = ? AND l.course_id = ?");
    $lc->execute([$user['id'], $e['course_id']]);
    $completed = (int)$lc->fetchColumn();
    $total = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $total->execute([$e['course_id']]);
    $total = (int)$total->fetchColumn();
    $e['progress_pct'] = $total > 0 ? round($completed / $total * 100) : 0;
}
unset($e);

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1>Welcome, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Track your learning progress and continue your journey.</p>

            <h2 class="section-title">My Courses</h2>
            <?php if (empty($enrollments)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 3rem;">You haven't enrolled in any courses yet. <a href="<?= base_url('public/catalog.php') ?>">Browse courses</a></p>
            <?php else: ?>
            <div class="card-grid">
                <?php foreach ($enrollments as $e): ?>
                <article class="course-card">
                    <div class="course-card-image">
                        <?= $e['thumbnail_url'] ? '<img src="' . htmlspecialchars($e['thumbnail_url']) . '" alt="" style="width:100%;height:100%;object-fit:cover">' : '📖' ?>
                    </div>
                    <div class="course-card-body">
                        <span class="course-meta"><?= htmlspecialchars($e['category_name']) ?></span>
                        <h3><a href="<?= base_url('learner/course.php?id=' . $e['course_id']) ?>"><?= htmlspecialchars($e['title']) ?></a></h3>
                        <div class="progress-bar"><div class="progress-fill" style="width: <?= $e['progress_pct'] ?>%"></div></div>
                        <p class="course-meta" style="margin-top: 0.5rem;"><?= $e['progress_pct'] ?>% complete</p>
                        <a href="<?= base_url('learner/course.php?id=' . $e['course_id']) ?>" class="btn btn-primary btn-sm" style="margin-top: 0.5rem">Continue Learning</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
