<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if (empty($token)) {
    header('Location: ' . base_url('auth/forgot-password.php'));
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    setFlash('danger', 'Invalid or expired reset link.');
    header('Location: ' . base_url('auth/forgot-password.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $reset['user_id']]);
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$reset['id']]);
        setFlash('success', 'Password reset successful. Please login.');
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

$pageTitle = 'Reset Password';
$currentPage = '';
$user = null;
require_once __DIR__ . '/../includes/header.php';
?>

<main class="auth-page">
    <div class="auth-box">
        <h1>Reset Password</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
