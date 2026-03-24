<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('instructor');

$pageTitle = 'Add Course';
$currentPage = '';
$user = getCurrentUser();

$pdo = getDBConnection();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $syllabus = trim($_POST['syllabus'] ?? '');
    $youtubePlaylist = trim($_POST['youtube_playlist_url'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $level = $_POST['level'] ?? 'all';
    $duration = (int)($_POST['duration_hours'] ?? 0);

    if (empty($title) || empty($description) || !$categoryId) {
        $error = 'Title, description, and category are required.';
    } else {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title)) . '-' . substr(uniqid(), -5);
        $stmt = $pdo->prepare("INSERT INTO courses (instructor_id, category_id, title, slug, description, syllabus, youtube_playlist_url, price, level, duration_hours, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$user['id'], $categoryId, $title, $slug, $description, $syllabus, ($youtubePlaylist ?: null), $price, $level, $duration]);
        $courseId = $pdo->lastInsertId();
        setFlash('success', 'Course created! Add lessons in the edit page.');
        header('Location: ' . base_url('instructor/edit-course.php?id=' . $courseId));
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container" style="max-width: 700px;">
            <h1>Add New Course</h1>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST" class="dash-card">
                <div class="form-group">
                    <label>Course Title</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select...</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Syllabus (optional)</label>
                    <textarea name="syllabus"><?= htmlspecialchars($_POST['syllabus'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>YouTube Playlist URL (optional)</label>
                    <input type="url" name="youtube_playlist_url" placeholder="https://www.youtube.com/playlist?list=..." value="<?= htmlspecialchars($_POST['youtube_playlist_url'] ?? '') ?>">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '0') ?>">
                    </div>
                    <div class="form-group">
                        <label>Duration (hours)</label>
                        <input type="number" name="duration_hours" min="0" value="<?= htmlspecialchars($_POST['duration_hours'] ?? '0') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Level</label>
                    <select name="level">
                        <option value="all">All Levels</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create Course</button>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
