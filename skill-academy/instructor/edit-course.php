<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('instructor');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . base_url('instructor/dashboard.php'));
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$id, $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('danger', 'Course not found.');
    header('Location: ' . base_url('instructor/dashboard.php'));
    exit;
}

$lessons = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order");
$lessons->execute([$id]);
$lessons = $lessons->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_course'])) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $syllabus = trim($_POST['syllabus'] ?? '');
        $youtubePlaylist = trim($_POST['youtube_playlist_url'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $level = $_POST['level'] ?? 'all';
        $duration = (int)($_POST['duration_hours'] ?? 0);
        $published = isset($_POST['is_published']) ? 1 : 0;
        $pdo->prepare("UPDATE courses SET title=?, category_id=?, description=?, syllabus=?, youtube_playlist_url=?, price=?, level=?, duration_hours=?, is_published=? WHERE id=?")
            ->execute([$title, $categoryId, $description, $syllabus, ($youtubePlaylist ?: null), $price, $level, $duration, $published, $id]);
        $message = 'Course updated.';
        $course = array_merge($course, compact('title', 'description', 'syllabus', 'youtube_playlist_url', 'category_id', 'price', 'level', 'duration_hours', 'is_published'));
        $course['category_id'] = $categoryId;
        $course['is_published'] = $published;
    } elseif (isset($_POST['add_lesson'])) {
        $lessonTitle = trim($_POST['lesson_title'] ?? '');
        $lessonContent = trim($_POST['lesson_content'] ?? '');
        $videoUrl = trim($_POST['video_url'] ?? '');
        $docUrl = trim($_POST['document_url'] ?? '');
        $duration = (int)($_POST['duration_minutes'] ?? 0);
        if ($lessonTitle) {
            $maxOrder = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM lessons WHERE course_id = ?");
            $maxOrder->execute([$id]);
            $sortOrder = $maxOrder->fetchColumn() + 1;
            $pdo->prepare("INSERT INTO lessons (course_id, title, content, video_url, document_url, sort_order, duration_minutes) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute([$id, $lessonTitle, $lessonContent, $videoUrl, $docUrl, $sortOrder, $duration]);
            $message = 'Lesson added.';
        }
        header('Location: ' . base_url('instructor/edit-course.php?id=' . $id));
        exit;
    } elseif (isset($_POST['delete_lesson'])) {
        $lid = (int)$_POST['delete_lesson'];
        $pdo->prepare("DELETE FROM lessons WHERE id = ? AND course_id = ?")->execute([$lid, $id]);
        $message = 'Lesson removed.';
        header('Location: ' . base_url('instructor/edit-course.php?id=' . $id));
        exit;
    }
}

$pageTitle = 'Edit Course';
require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container" style="max-width: 800px;">
            <h1>Edit Course: <?= htmlspecialchars($course['title']) ?></h1>
            <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

            <div class="dash-card" style="margin-bottom: 2rem;">
                <h3>Course Details</h3>
                <form method="POST">
                    <input type="hidden" name="update_course" value="1">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($course['title']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $course['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" required><?= htmlspecialchars($course['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Syllabus</label>
                        <textarea name="syllabus"><?= htmlspecialchars($course['syllabus'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>YouTube Playlist URL (optional)</label>
                        <input type="url" name="youtube_playlist_url" placeholder="https://www.youtube.com/playlist?list=..." value="<?= htmlspecialchars($course['youtube_playlist_url'] ?? '') ?>">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Price ($)</label>
                            <input type="number" name="price" step="0.01" min="0" value="<?= $course['price'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Duration (hrs)</label>
                            <input type="number" name="duration_hours" min="0" value="<?= $course['duration_hours'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Level</label>
                            <select name="level"><?php foreach (['all','beginner','intermediate','advanced'] as $l): ?><option value="<?= $l ?>" <?= $course['level'] === $l ? 'selected' : '' ?>><?= ucfirst($l) ?></option><?php endforeach; ?></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="is_published" <?= $course['is_published'] ? 'checked' : '' ?>> Published</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Course</button>
                </form>
            </div>

            <div class="dash-card">
                <h3>Lessons</h3>
                <form method="POST" style="margin-bottom: 2rem; padding: 1rem; background: var(--bg); border-radius: var(--radius-sm);">
                    <input type="hidden" name="add_lesson" value="1">
                    <div class="form-group">
                        <label>Lesson Title</label>
                        <input type="text" name="lesson_title" placeholder="e.g. Introduction to Variables" required>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="lesson_content" placeholder="Lesson notes or description"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Video URL</label>
                            <input type="url" name="video_url" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>Document URL</label>
                            <input type="url" name="document_url" placeholder="https://...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <input type="number" name="duration_minutes" min="0" value="0">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Lesson</button>
                </form>
                <ul style="list-style: none;">
                    <?php foreach ($lessons as $les): ?>
                    <li style="padding: 0.75rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
                        <span><?= htmlspecialchars($les['title']) ?></span>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_lesson" value="<?= $les['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Remove this lesson?')">Delete</button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (empty($lessons)): ?><p class="course-meta">No lessons yet. Add one above.</p><?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
