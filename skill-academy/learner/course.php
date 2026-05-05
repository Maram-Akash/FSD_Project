<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('learner');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . base_url('learner/dashboard.php'));
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

// Check enrollment
$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user['id'], $id]);
if (!$stmt->fetch()) {
    setFlash('danger', 'You are not enrolled in this course.');
    header('Location: ' . base_url('learner/dashboard.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT c.*, u.full_name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course) {
    header('Location: ' . base_url('learner/dashboard.php'));
    exit;
}

$lessons = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order");
$lessons->execute([$id]);
$lessons = $lessons->fetchAll();

// Mark lessons as completed
foreach ($lessons as &$les) {
    $c = $pdo->prepare("SELECT 1 FROM lesson_completions WHERE user_id = ? AND lesson_id = ?");
    $c->execute([$user['id'], $les['id']]);
    $les['completed'] = (bool)$c->fetch();
}
unset($les);

$completedCount = count(array_filter($lessons, fn($l) => $l['completed']));
$progress = count($lessons) > 0 ? round($completedCount / count($lessons) * 100) : 0;

$activeLessonId = (int)($_GET['lesson'] ?? 0);
$activeLesson = null;
if ($activeLessonId) {
    foreach ($lessons as $l) {
        if ((int)$l['id'] === $activeLessonId) { $activeLesson = $l; break; }
    }
}
if (!$activeLesson && !empty($lessons)) {
    // Default to the next incomplete lesson, otherwise first lesson.
    $activeLesson = null;
    foreach ($lessons as $l) {
        if (!$l['completed']) { $activeLesson = $l; break; }
    }
    if (!$activeLesson) $activeLesson = $lessons[0];
}

// Handle mark complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_lesson'])) {
    $lessonId = (int)$_POST['complete_lesson'];
    $pdo->prepare("INSERT IGNORE INTO lesson_completions (user_id, lesson_id) VALUES (?, ?)")->execute([$user['id'], $lessonId]);
    // Update enrollment progress
    $total = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $total->execute([$id]);
    $total = (int)$total->fetchColumn();
    $done = $pdo->prepare("SELECT COUNT(*) FROM lesson_completions lc JOIN lessons l ON lc.lesson_id = l.id WHERE lc.user_id = ? AND l.course_id = ?");
    $done->execute([$user['id'], $id]);
    $done = (int)$done->fetchColumn();
    $pct = $total > 0 ? round($done / $total * 100) : 0;
    $pdo->prepare("UPDATE enrollments SET progress = ? WHERE user_id = ? AND course_id = ?")->execute([$pct, $user['id'], $id]);
    setFlash('success', 'Lesson marked as complete!');
    header('Location: ' . base_url('learner/course.php?id=' . $id));
    exit;
}

$pageTitle = $course['title'];
$currentPage = '';
require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1><?= htmlspecialchars($course['title']) ?></h1>
            <p class="course-meta">By <?= htmlspecialchars($course['instructor_name']) ?></p>
            <div class="progress-bar" style="margin: 1rem 0; max-width: 400px;">
                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
            </div>
            <p class="course-meta"><?= $progress ?>% complete (<?= $completedCount ?>/<?= count($lessons) ?> lessons)</p>

            <div style="margin-top: 1.25rem; display:flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="<?= base_url('learner/quiz.php?id=' . $id) ?>" class="btn btn-primary btn-sm">Take Course Test</a>
                <a href="<?= base_url('public/course-detail.php?id=' . $id) ?>" class="btn btn-outline btn-sm">Course Details</a>
            </div>

            <?php if ($activeLesson): ?>
                <?php $embed = $activeLesson['video_url'] ? youtube_video_embed_url($activeLesson['video_url']) : null; ?>
                <?php $plistEmbed = !empty($course['youtube_playlist_url']) ? youtube_playlist_embed_url($course['youtube_playlist_url']) : null; ?>
                <div class="dash-card" style="margin-top: 2rem;">
                    <h3>Now Playing</h3>
                    <p class="course-meta" style="margin-top: -0.25rem;"><?= htmlspecialchars($activeLesson['title']) ?></p>
                    <?php if ($embed): ?>
                        <div style="position: relative; padding-top: 56.25%; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border); background: #000;">
                            <iframe
                                src="<?= htmlspecialchars($embed) ?>"
                                title="Lesson video"
                                style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    <?php elseif ($activeLesson['video_url']): ?>
                        <p class="course-meta">Video link:</p>
                        <p><a href="<?= htmlspecialchars($activeLesson['video_url']) ?>" target="_blank" rel="noreferrer"><?= htmlspecialchars($activeLesson['video_url']) ?></a></p>
                    <?php elseif ($plistEmbed): ?>
                        <p class="course-meta">This lesson uses the course playlist.</p>
                        <div style="position: relative; padding-top: 56.25%; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border); background: #000;">
                            <iframe
                                src="<?= htmlspecialchars($plistEmbed) ?>"
                                title="Course playlist"
                                style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    <?php else: ?>
                        <p class="course-meta">No video for this lesson yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($course['youtube_playlist_url'])): ?>
                <?php $plist = youtube_playlist_embed_url($course['youtube_playlist_url']); ?>
                <?php if ($plist): ?>
                    <div class="dash-card" style="margin-top: 2rem;">
                        <h3>Course Playlist</h3>
                        <p class="course-meta" style="margin-top:-0.25rem;">All course videos in one playlist.</p>
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

            <div class="dash-card" style="margin-top: 2rem;">
                <h3>Lessons</h3>
                <?php foreach ($lessons as $i => $lesson): ?>
                <div style="padding: 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <strong><?= $i + 1 ?>. <?= htmlspecialchars($lesson['title']) ?></strong>
                        <?php if ($lesson['completed']): ?><span class="stars">✓ Complete</span><?php endif; ?>
                    </div>
                    <div>
                        <?php if ($lesson['video_url']): ?>
                            <?php $lessonEmbed = youtube_video_embed_url($lesson['video_url']); ?>
                            <?php if ($lessonEmbed): ?>
                                <a href="<?= base_url('learner/course.php?id=' . $id . '&lesson=' . (int)$lesson['id']) ?>" class="btn btn-outline btn-sm">Watch Video</a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($lesson['video_url']) ?>" target="_blank" rel="noreferrer" class="btn btn-outline btn-sm">Watch Video</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($lesson['document_url']): ?>
                        <a href="<?= htmlspecialchars($lesson['document_url']) ?>" target="_blank" class="btn btn-outline btn-sm">View Document</a>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="complete_lesson" value="<?= $lesson['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm" <?= $lesson['completed'] ? 'disabled' : '' ?>><?= $lesson['completed'] ? '✓ Done' : 'Mark Complete' ?></button>
                        </form>
                    </div>
                </div>
                <?php if ($lesson['content']): ?>
                <div style="padding: 0 1rem 1rem; color: var(--text-muted); font-size: 0.9375rem;"><?= nl2br(htmlspecialchars($lesson['content'])) ?></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($progress === 100 && count($lessons) > 0): ?>
            <div class="alert alert-success" style="margin-top: 2rem;">Congratulations! You've completed this course. <a href="<?= base_url('learner/dashboard.php') ?>">Back to Dashboard</a></div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
