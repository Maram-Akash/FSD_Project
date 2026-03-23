<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Home';
$currentPage = 'home';
$user = getCurrentUser();

$pdo = getDBConnection();

// Featured courses (published, limit 6)
$stmt = $pdo->query("
    SELECT c.*, cat.name as category_name, u.full_name as instructor_name,
           COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
    FROM courses c
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN reviews r ON c.id = r.course_id
    WHERE c.is_published = 1
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 6
");
$featuredCourses = $stmt->fetchAll();

// Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="hero">
        <div class="container">
            <h1>Master New Skills. Advance Your Career.</h1>
            <p>Join thousands of learners on our platform. Discover courses in Programming, Design, Marketing, and more from expert instructors.</p>
            <a href="<?= base_url('public/catalog.php') ?>" class="btn btn-primary btn-lg">Browse Courses</a>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Categories</h2>
            <div class="category-pills">
                <?php foreach ($categories as $cat): ?>
                <a href="<?= base_url('public/catalog.php') ?>?category=<?= urlencode($cat['slug']) ?>" class="category-pill"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Courses</h2>
            <div class="card-grid">
                <?php foreach ($featuredCourses as $course): ?>
                <article class="course-card">
                    <div class="course-card-image">
                        <?= $course['thumbnail_url'] ? '<img src="' . htmlspecialchars($course['thumbnail_url']) . '" alt="" style="width:100%;height:100%;object-fit:cover">' : '📖' ?>
                    </div>
                    <div class="course-card-body">
                        <span class="course-meta"><?= htmlspecialchars($course['category_name']) ?> • <?= htmlspecialchars($course['level']) ?></span>
                        <h3><a href="<?= base_url('public/course-detail.php') ?>?id=<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></a></h3>
                        <p class="course-meta">By <?= htmlspecialchars($course['instructor_name']) ?></p>
                        <div class="course-meta">
                            <?php if ($course['review_count'] > 0): ?>
                            <span class="stars">★ <?= number_format($course['avg_rating'], 1) ?></span>
                            <span>(<?= $course['review_count'] ?> reviews)</span>
                            <?php endif; ?>
                        </div>
                        <p class="course-price"><?= $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free' ?></p>
                        <a href="<?= base_url('public/course-detail.php') ?>?id=<?= $course['id'] ?>" class="btn btn-primary btn-sm" style="margin-top:0.5rem">View Course</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <p style="text-align:center; margin-top:2rem">
                <a href="<?= base_url('public/catalog.php') ?>" class="btn btn-outline">View All Courses</a>
            </p>
        </div>
    </section>

    <section class="section" style="background: var(--bg-card); border-top: 1px solid var(--border);">
        <div class="container">
            <h2 class="section-title">Success Stories</h2>
            <div class="card-grid">
                <div class="course-card">
                    <div class="course-card-body">
                        <p>"This platform transformed my career. The quality of instruction is outstanding."</p>
                        <strong>— Sarah M., Developer</strong>
                    </div>
                </div>
                <div class="course-card">
                    <div class="course-card-body">
                        <p>"I completed three courses and landed my dream job. Highly recommend!"</p>
                        <strong>— James K., Designer</strong>
                    </div>
                </div>
                <div class="course-card">
                    <div class="course-card-body">
                        <p>"Best investment I've made in my professional development."</p>
                        <strong>— Emily R., Marketer</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
