    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <span class="brand-icon">📚</span>
                    <strong>Skill Learning Academy</strong>
                    <p>Master new skills. Advance your career.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <?php $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : ''; ?>
                    <a href="<?= $base ?>/index.php">Home</a>
                    <a href="<?= $base ?>/public/about.php">About</a>
                    <a href="<?= $base ?>/public/catalog.php">Browse Courses</a>
                    <a href="<?= $base ?>/public/contact.php">Contact</a>
                </div>
                <div class="footer-links">
                    <h4>Account</h4>
                    <a href="<?= $base ?>/auth/login.php">Login</a>
                    <a href="<?= $base ?>/auth/register.php">Register</a>
                    <a href="<?= $base ?>/auth/forgot-password.php">Forgot Password</a>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p>📧 support@skillacademy.com</p>
                    <p>📞 +1 (555) 123-4567</p>
                </div>
            </div>
            <hr>
            <p class="footer-copy">&copy; <?= date('Y') ?> Skill Learning Academy. All rights reserved.</p>
        </div>
    </footer>
    <script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
