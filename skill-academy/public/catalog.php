<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Browse Courses';
$currentPage = 'catalog';
$user = getCurrentUser();

$pdo = getDBConnection();

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$price = $_GET['price'] ?? ''; // free, paid, all
$level = $_GET['level'] ?? '';

$sql = "
    SELECT c.*, cat.name as category_name, u.full_name as instructor_name,
           COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
    FROM courses c
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN reviews r ON c.id = r.course_id
    WHERE c.is_published = 1
";
$params = [];

if ($search) {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ? OR u.full_name LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term]);
}
if ($category) {
    $sql .= " AND cat.slug = ?";
    $params[] = $category;
}
if ($price === 'free') {
    $sql .= " AND c.price = 0";
} elseif ($price === 'paid') {
    $sql .= " AND c.price > 0";
}
if ($level) {
    $sql .= " AND c.level = ?";
    $params[] = $level;
}

$sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1 class="section-title">Browse Courses</h1>
            
            <form method="GET" class="search-filter-bar">
                <input type="text" name="search" id="course-search" class="search-input" 
                       placeholder="Search courses or instructors..." 
                       value="<?= htmlspecialchars($search) ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="price">
                    <option value="">All Prices</option>
                    <option value="free" <?= $price === 'free' ? 'selected' : '' ?>>Free</option>
                    <option value="paid" <?= $price === 'paid' ? 'selected' : '' ?>>Paid</option>
                </select>
                <select name="level">
                    <option value="">All Levels</option>
                    <option value="beginner" <?= $level === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= $level === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= $level === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>

            <?php if (empty($courses)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 3rem;">No courses found. Try adjusting your filters.</p>
            <?php else: ?>
            <div class="card-grid">
                <?php foreach ($courses as $course): ?>
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
                            <span>(<?= $course['review_count'] ?>)</span>
                            <?php endif; ?>
                        </div>
                        <p class="course-price"><?= $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free' ?></p>
                        <a href="<?= base_url('public/course-detail.php') ?>?id=<?= $course['id'] ?>" class="btn btn-primary btn-sm" style="margin-top:0.5rem">View Course</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
