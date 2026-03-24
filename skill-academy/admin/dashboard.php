<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';
$currentPage = '';
$user = getCurrentUser();

$pdo = getDBConnection();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

// Recent users
$recentUsers = $pdo->query("SELECT id, email, full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent courses
$recentCourses = $pdo->query("
    SELECT c.id, c.title, c.is_published, u.full_name as instructor, c.created_at
    FROM courses c JOIN users u ON c.instructor_id = u.id
    ORDER BY c.created_at DESC LIMIT 5
")->fetchAll();

// Contact submissions
$contacts = $pdo->query("SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT 10")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1>Admin Dashboard</h1>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Platform overview and management.</p>

            <div class="dashboard-grid" style="margin-bottom: 2rem;">
                <div class="dash-card">
                    <h3>Total Users</h3>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $totalUsers ?></p>
                </div>
                <div class="dash-card">
                    <h3>Total Courses</h3>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $totalCourses ?></p>
                </div>
                <div class="dash-card">
                    <h3>Enrollments</h3>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $totalEnrollments ?></p>
                </div>
                <div class="dash-card">
                    <h3>Revenue</h3>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--accent);">$<?= number_format($revenue, 2) ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="dash-card">
                    <h3>Recent Users</h3>
                    <table>
                        <thead><tr><th>Name</th><th>Role</th><th>Joined</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                <td><?= $u['role'] ?></td>
                                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="dash-card">
                    <h3>Recent Courses</h3>
                    <table>
                        <thead><tr><th>Title</th><th>Instructor</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentCourses as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['title']) ?></td>
                                <td><?= htmlspecialchars($c['instructor']) ?></td>
                                <td><?= $c['is_published'] ? 'Published' : 'Draft' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dash-card" style="margin-top: 2rem;">
                <h3>Recent Contact Messages</h3>
                <?php if (empty($contacts)): ?><p class="course-meta">No messages yet.</p>
                <?php else: ?>
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($contacts as $ct): ?>
                        <tr>
                            <td><?= htmlspecialchars($ct['name']) ?></td>
                            <td><?= htmlspecialchars($ct['email']) ?></td>
                            <td><?= htmlspecialchars($ct['subject']) ?></td>
                            <td><?= date('M j, Y', strtotime($ct['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
