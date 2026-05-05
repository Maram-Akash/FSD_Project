<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['instructor', 'admin']);

$pageTitle = 'Instructor Dashboard';
$currentPage = '';
$user = getCurrentUser();

$pdo = getDBConnection();
$instructorId = $user['role'] === 'admin' ? null : $user['id'];

$where = $instructorId ? "WHERE c.instructor_id = ?" : "";
$params = $instructorId ? [$instructorId] : [];

$stmt = $pdo->prepare("
    SELECT c.*, cat.name as category_name,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count
    FROM courses c
    JOIN categories cat ON c.category_id = cat.id
    $where
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Total students
$totalStudents = $pdo->prepare("SELECT COUNT(DISTINCT e.user_id) FROM enrollments e JOIN courses c ON e.course_id = c.id " . ($instructorId ? "WHERE c.instructor_id = ?" : ""));
if ($instructorId) $totalStudents->execute([$instructorId]); else $totalStudents->execute();
$totalStudents = $totalStudents->fetchColumn();

// Total earnings (from orders for this instructor's courses)
$earningsStmt = $pdo->prepare("
    SELECT COALESCE(SUM(oi.price), 0) FROM order_items oi
    JOIN courses c ON oi.course_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed' " . ($instructorId ? "AND c.instructor_id = ?" : "")
);
if ($instructorId) $earningsStmt->execute([$instructorId]); else $earningsStmt->execute();
$earnings = $earningsStmt->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1>Instructor Dashboard</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Manage your courses and view student stats.</p>

            <div class="dashboard-grid" style="margin-bottom: 2rem;">
                <div class="dash-card">
                    <h3>Total Courses</h3>
                    <p style="font-size: 2rem; font-weight: 700;"><?= count($courses) ?></p>
                </div>
                <div class="dash-card">
                    <h3>Total Students</h3>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $totalStudents ?></p>
                </div>
                <div class="dash-card">
                    <h3>Total Earnings</h3>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--accent);">$<?= number_format($earnings, 2) ?></p>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                <h2>My Courses</h2>
                <a href="<?= base_url('instructor/upload-course.php') ?>" class="btn btn-primary">+ Add Course</a>
            </div>

            <?php if (empty($courses)): ?>
            <p style="color: var(--text-muted); padding: 2rem;">No courses yet. <a href="<?= base_url('instructor/upload-course.php') ?>">Create your first course</a></p>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Course</th><th>Category</th><th>Students</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td><?= htmlspecialchars($c['category_name']) ?></td>
                            <td><?= $c['student_count'] ?></td>
                            <td><?= $c['is_published'] ? 'Published' : 'Draft' ?></td>
                            <td>
                                <a href="<?= base_url('instructor/edit-course.php?id=' . $c['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="<?= base_url('instructor/students.php?course_id=' . $c['id']) ?>" class="btn btn-outline btn-sm">Students</a>
                            </td>
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
