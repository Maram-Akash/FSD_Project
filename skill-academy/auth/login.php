<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, email, password_hash, full_name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $redirect = $_POST['redirect'] ?? $redirect;
            $target = (strpos($redirect, '/') === 0 || strpos($redirect, 'http') === 0) ? $redirect : base_url($redirect);
            header('Location: ' . $target);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
$currentPage = '';
$user = null;
require_once __DIR__ . '/../includes/header.php';
?>

<main class="auth-page">
    <div class="auth-box">
        <h1>Login</h1>
        <div class="alert alert-success" style="margin-bottom: 1rem;">
            Demo logins:
            <div style="margin-top: 0.5rem; font-size: 0.9375rem;">
                <div><strong>Learner:</strong> learner@skillacademy.com / learner123</div>
                <div><strong>Instructor:</strong> instructor@skillacademy.com / instructor123</div>
                <div><strong>Admin:</strong> admin@skillacademy.com / admin123</div>
            </div>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <a href="<?= base_url('auth/forgot-password.php') ?>" style="font-size: 0.875rem; color: var(--primary); margin-bottom: 1rem; display: block;">Forgot password?</a>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="auth-links">Don't have an account? <a href="<?= base_url('auth/register.php') ?>">Sign Up</a></p>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
