<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'learner';

    if (empty($email) || empty($password) || empty($fullName)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['learner', 'instructor'])) {
        $error = 'Invalid role.';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $hash, $fullName, $role]);
            setFlash('success', 'Registration successful! Please login.');
            header('Location: ' . base_url('auth/login.php'));
            exit;
        }
    }
}

$pageTitle = 'Register';
$currentPage = '';
$user = null;
require_once __DIR__ . '/../includes/header.php';
?>

<main class="auth-page">
    <div class="auth-box">
        <h1>Create Account</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="role">I want to</label>
                <select id="role" name="role">
                    <option value="learner" <?= ($_POST['role'] ?? '') === 'learner' ? 'selected' : '' ?>>Learn courses</option>
                    <option value="instructor" <?= ($_POST['role'] ?? '') === 'instructor' ? 'selected' : '' ?>>Teach courses</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="auth-links">Already have an account? <a href="<?= base_url('auth/login.php') ?>">Login</a></p>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
