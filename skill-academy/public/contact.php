<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Contact Us';
$currentPage = 'contact';
$user = getCurrentUser();

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $sent = true;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container" style="max-width: 600px;">
            <h1 class="section-title">Contact Us</h1>
            
            <?php if ($sent): ?>
            <div class="alert alert-success">Thank you! Your message has been sent. We'll get back to you soon.</div>
            <?php else: ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            
            <form method="POST" class="auth-box" style="max-width: 100%;">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?= htmlspecialchars($_POST['name'] ?? $user['full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required 
                           value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
            <?php endif; ?>

            <div style="margin-top: 2rem; text-align: center; color: var(--text-muted);">
                <p>📧 support@skillacademy.com</p>
                <p>📞 +1 (555) 123-4567</p>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
