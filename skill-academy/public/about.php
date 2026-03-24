<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'About Us';
$currentPage = 'about';
$user = getCurrentUser();

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="hero" style="padding: 3rem 2rem;">
        <div class="container">
            <h1>About Skill Learning Academy</h1>
            <p>Empowering learners worldwide to acquire in-demand skills and advance their careers.</p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 800px;">
            <h2 class="section-title">Our Mission</h2>
            <p style="font-size: 1.125rem; color: var(--text-muted); text-align: center;">
                To provide accessible, high-quality skill-based education that bridges the gap between learning and career success. We believe everyone deserves the opportunity to grow and excel.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Our Vision</h2>
            <div class="card-grid">
                <div class="course-card">
                    <div class="course-card-body">
                        <h3>🌐 Global Reach</h3>
                        <p>Connect learners and instructors from around the world through our unified marketplace.</p>
                    </div>
                </div>
                <div class="course-card">
                    <div class="course-card-body">
                        <h3>🎯 Quality Focus</h3>
                        <p>Curated courses with verified instructors and student-reviewed content.</p>
                    </div>
                </div>
                <div class="course-card">
                    <div class="course-card-body">
                        <h3>📈 Career Growth</h3>
                        <p>Practical skills that translate directly into job opportunities and advancement.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="background: var(--bg-card);">
        <div class="container">
            <h2 class="section-title">Our Team</h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">
                A dedicated team of educators, developers, and industry experts working to build the future of learning.
            </p>
            <div class="card-grid">
                <div class="course-card">
                    <div class="course-card-body" style="text-align: center;">
                        <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem; margin: 0 auto 0.5rem;">J</div>
                        <h3>Jane Doe</h3>
                        <p class="course-meta">CEO & Co-Founder</p>
                    </div>
                </div>
                <div class="course-card">
                    <div class="course-card-body" style="text-align: center;">
                        <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem; margin: 0 auto 0.5rem;">A</div>
                        <h3>Alex Smith</h3>
                        <p class="course-meta">Head of Product</p>
                    </div>
                </div>
                <div class="course-card">
                    <div class="course-card-body" style="text-align: center;">
                        <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem; margin: 0 auto 0.5rem;">M</div>
                        <h3>Maria Garcia</h3>
                        <p class="course-meta">Lead Instructor</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
