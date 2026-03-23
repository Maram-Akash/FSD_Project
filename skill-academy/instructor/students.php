<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('instructor');

$courseId = (int)($_GET['course_id'] ?? 0);
if (!$courseId) {
    header('Location: ' . base_url('instructor/dashboard.php'));
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Course not found.');
    header('Location: ' . base_url('instructor/dashboard.php'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.*, u.full_name, u.email, e.progress, e.enrolled_at
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    WHERE e.course_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$courseId]);
$students = $stmt->fetchAll();

$pageTitle = 'Students';
require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1>Students: <?= htmlspecialchars($course['title']) ?></h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;"><a href="<?= base_url('instructor/dashboard.php') ?>">← Back to Dashboard</a></p>

            <?php if (empty($students)): ?>
            <p style="color: var(--text-muted); padding: 2rem;">No students enrolled yet.</p>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Progress</th><th>Enrolled</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= $s['progress'] ?>%</td>
                            <td><?= date('M j, Y', strtotime($s['enrolled_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
