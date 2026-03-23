<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);
            // In production: send email with link. For demo we show the link.
            $resetLink = SITE_URL . '/auth/reset-password.php?token=' . $token;
            // Simulate send - in production use mail() or PHPMailer
            setFlash('info', 'Password reset link: ' . $resetLink);
        }
        $sent = true; // Always show success for security (don't reveal if email exists)
    }
}

$pageTitle = 'Forgot Password';
$currentPage = '';
$user = getCurrentUser();
require_once __DIR__ . '/../includes/header.php';
?>

<main class="auth-page">
    <div class="auth-box">
        <h1>Forgot Password</h1>
        <?php if ($sent): ?>
        <div class="alert alert-success">If an account exists with that email, you will receive a password reset link. Check your inbox.</div>
        <p class="auth-links"><a href="<?= base_url('auth/login.php') ?>">Back to Login</a></p>
        <?php else: ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </form>
        <p class="auth-links"><a href="<?= base_url('auth/login.php') ?>">Back to Login</a></p>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
