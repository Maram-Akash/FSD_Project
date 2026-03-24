<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Profile';
$currentPage = '';
$user = getCurrentUser();

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        if (empty($fullName)) {
            $error = 'Full name is required.';
        } else {
            $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, bio = ? WHERE id = ?")->execute([$fullName, $phone, $bio, $user['id']]);
            $message = 'Profile updated successfully.';
            $profile = array_merge($profile, ['full_name' => $fullName, 'phone' => $phone, 'bio' => $bio]);
        }
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($current) || empty($new)) {
            $error = 'All password fields are required.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!password_verify($current, $profile['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $user['id']]);
            $message = 'Password updated successfully.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container" style="max-width: 600px;">
            <h1>Profile Settings</h1>
            <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <div class="dash-card" style="margin-bottom: 1.5rem;">
                <h3>Personal Info</h3>
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required value="<?= htmlspecialchars($profile['full_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" value="<?= htmlspecialchars($profile['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <div class="dash-card">
                <h3>Change Password</h3>
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>

            <?php if ($user['role'] === 'instructor'): ?>
            <p style="margin-top: 1.5rem;"><a href="<?= base_url('instructor/dashboard.php') ?>" class="btn btn-outline">Go to Instructor Dashboard</a></p>
            <?php elseif ($user['role'] === 'admin'): ?>
            <p style="margin-top: 1.5rem;"><a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline">Go to Admin Dashboard</a></p>
            <?php else: ?>
            <div class="dash-card" style="margin-top: 1.5rem;">
                <h3>My Learning</h3>
                <?php
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
                $countStmt->execute([$user['id']]);
                $totalEnrolled = (int)$countStmt->fetchColumn();

                $enStmt = $pdo->prepare("
                    SELECT e.course_id, e.progress, e.enrolled_at, c.title
                    FROM enrollments e
                    JOIN courses c ON e.course_id = c.id
                    WHERE e.user_id = ?
                    ORDER BY e.enrolled_at DESC
                    LIMIT 10
                ");
                $enStmt->execute([$user['id']]);
                $enrolledCourses = $enStmt->fetchAll();
                ?>
                <p class="course-meta" style="margin-bottom: 1rem;">Total enrolled courses: <strong><?= $totalEnrolled ?></strong></p>
                <?php if (empty($enrolledCourses)): ?>
                    <p class="course-meta">You haven’t enrolled in any courses yet. <a href="<?= base_url('public/catalog.php') ?>">Browse courses</a></p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($enrolledCourses as $c): ?>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <div style="display:flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                                    <div>
                                        <strong><?= htmlspecialchars($c['title']) ?></strong>
                                        <div class="course-meta" style="margin-top: 0.25rem;">Progress: <?= (int)$c['progress'] ?>%</div>
                                    </div>
                                    <div style="display:flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                        <a class="btn btn-primary btn-sm" href="<?= base_url('learner/course.php?id=' . (int)$c['course_id']) ?>">Continue Learning</a>
                                        <a class="btn btn-outline btn-sm" href="<?= base_url('learner/quiz.php?id=' . (int)$c['course_id']) ?>">Test</a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="dash-card" style="margin-top: 1.5rem;">
                <h3>Enrollment History</h3>
                <?php
                $hist = $pdo->prepare("SELECT e.enrolled_at, c.title FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ? ORDER BY e.enrolled_at DESC LIMIT 10");
                $hist->execute([$user['id']]);
                $history = $hist->fetchAll();
                ?>
                <?php if (empty($history)): ?><p class="course-meta">No enrollments yet.</p>
                <?php else: ?>
                <ul style="list-style: none;">
                    <?php foreach ($history as $h): ?>
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);"><?= htmlspecialchars($h['title']) ?> - <?= date('M j, Y', strtotime($h['enrolled_at'])) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
