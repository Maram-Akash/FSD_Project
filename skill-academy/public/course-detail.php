<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . base_url('public/catalog.php'));
    exit;
}

$pageTitle = 'Course Detail';
$currentPage = 'catalog';
$user = getCurrentUser();

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT c.*, cat.name as category_name, u.full_name as instructor_name, u.bio as instructor_bio,
           COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
    FROM courses c
    JOIN categories cat ON c.category_id = cat.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN reviews r ON c.id = r.course_id
    WHERE c.id = ? AND c.is_published = 1
    GROUP BY c.id
");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: ' . base_url('public/catalog.php'));
    exit;
}

// Lessons
$lessons = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order");
$lessons->execute([$id]);
$lessons = $lessons->fetchAll();

// Reviews
$reviews = $pdo->prepare("
    SELECT r.*, u.full_name FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.course_id = ? ORDER BY r.created_at DESC LIMIT 10
");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$enrolled = false;
$inWishlist = false;
if ($user) {
    $e = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
    $e->execute([$user['id'], $id]);
    $enrolled = (bool)$e->fetch();
    $w = $pdo->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND course_id = ?");
    $w->execute([$user['id'], $id]);
    $inWishlist = (bool)$w->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <div class="course-detail-header">
                <div>
                    <span class="course-meta"><?= htmlspecialchars($course['category_name']) ?> • <?= htmlspecialchars($course['level']) ?></span>
                    <h1 style="margin: 0.5rem 0 1rem; font-size: 2rem;"><?= htmlspecialchars($course['title']) ?></h1>
                    <p class="course-meta">By <?= htmlspecialchars($course['instructor_name']) ?>
                        <?php if ($course['review_count'] > 0): ?>
                        • <span class="stars">★ <?= number_format($course['avg_rating'], 1) ?></span> (<?= $course['review_count'] ?> reviews)
                        <?php endif; ?>
                    </p>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                    
                    <?php if ($course['syllabus']): ?>
                    <h3 style="margin-bottom: 0.5rem;">Syllabus</h3>
                    <p style="color: var(--text-muted); white-space: pre-wrap;"><?= htmlspecialchars($course['syllabus']) ?></p>
                    <?php endif; ?>

                    <?php if ($user): ?>
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <?php if ($enrolled): ?>
                        <a href="<?= base_url('learner/course.php') ?>?id=<?= $id ?>" class="btn btn-primary">Go to Course</a>
                        <a href="<?= base_url('learner/quiz.php') ?>?id=<?= $id ?>" class="btn btn-outline">Take Course Test</a>
                        <?php else: ?>
                        <?php if ((float)$course['price'] > 0): ?>
                            <a href="<?= base_url('learner/checkout.php') ?>?course_id=<?= $id ?>" class="btn btn-primary">Enroll Now</a>
                        <?php else: ?>
                            <a href="<?= base_url('learner/enroll.php') ?>?course_id=<?= $id ?>" class="btn btn-primary">Enroll Now</a>
                        <?php endif; ?>
                        <form method="POST" action="<?= base_url('api/wishlist-toggle.php') ?>" style="display:inline;">
                            <input type="hidden" name="course_id" value="<?= $id ?>">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars((defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : '') . '/public/course-detail.php?id=' . $id) ?>">
                            <button type="submit" class="btn btn-outline"><?= $inWishlist ? '★ Saved (Remove)' : '☆ Save for Later' ?></button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <a href="<?= base_url('auth/login.php') ?>?redirect=<?= urlencode((defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : '') . '/public/course-detail.php?id=' . $id) ?>" class="btn btn-primary" style="margin-top: 1rem;">Login to Enroll</a>
                    <?php endif; ?>
                </div>
                <aside class="course-sidebar">
                    <div class="course-card-image" style="height: 180px; border-radius: var(--radius-sm);">
                        <?= $course['thumbnail_url'] ? '<img src="' . htmlspecialchars($course['thumbnail_url']) . '" alt="" style="width:100%;height:100%;object-fit:cover">' : '📖' ?>
                    </div>
                    <p class="course-price" style="font-size: 1.5rem; margin: 1rem 0;"><?= $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free' ?></p>
                    <ul class="course-meta" style="list-style: none; padding: 0;">
                        <li>Duration: <?= $course['duration_hours'] ?> hrs</li>
                        <li>Lessons: <?= count($lessons) ?></li>
                        <li>Level: <?= htmlspecialchars($course['level']) ?></li>
                    </ul>
                    <?php if (!empty($course['youtube_playlist_url'])): ?>
                        <?php $plist = youtube_playlist_embed_url($course['youtube_playlist_url']); ?>
                        <?php if ($plist): ?>
                            <div style="margin-top: 1rem;">
                                <p class="course-meta" style="margin-bottom: 0.5rem;"><strong>Course Playlist</strong></p>
                                <div style="position: relative; padding-top: 56.25%; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border); background: #000;">
                                    <iframe
                                        src="<?= htmlspecialchars($plist) ?>"
                                        title="Course playlist"
                                        style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </aside>
            </div>

            <?php if (!empty($lessons)): ?>
            <div class="dash-card" style="margin-top: 2rem;">
                <h3>Course Content</h3>
                <ul style="list-style: none;">
                    <?php foreach ($lessons as $i => $lesson): ?>
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                        <?= $i + 1 ?>. <?= htmlspecialchars($lesson['title']) ?>
                        <?php if ($lesson['duration_minutes']): ?><span class="course-meta">(<?= $lesson['duration_minutes'] ?> min)</span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="dash-card" style="margin-top: 2rem;">
                <h3>Reviews</h3>
                <?php if ($user && $enrolled): ?>
                <form id="review-form" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
                    <input type="hidden" name="course_id" value="<?= $id ?>">
                    <div class="form-group">
                        <label>Your Rating</label>
                        <select name="rating" required>
                            <option value="">Select...</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Your Review (optional)</label>
                        <textarea name="review_text" placeholder="Share your experience..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
                <?php endif; ?>
                <?php foreach ($reviews as $rev): ?>
                <div style="padding: 1rem 0; border-bottom: 1px solid var(--border);">
                    <span class="stars"><?= str_repeat('★', (int)$rev['rating']) ?></span>
                    <strong><?= htmlspecialchars($rev['full_name']) ?></strong>
                    <?php if ($rev['review_text']): ?><p style="margin: 0.5rem 0;"><?= htmlspecialchars($rev['review_text']) ?></p><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($reviews) && (!$user || !$enrolled)): ?><p class="course-meta">No reviews yet.</p><?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('review-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    try {
        const res = await fetch('<?= base_url("api/review.php") ?>', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) { alert('Review submitted!'); location.reload(); }
        else alert(data.error || 'Error');
    } catch (err) { alert('Error submitting review'); }
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
