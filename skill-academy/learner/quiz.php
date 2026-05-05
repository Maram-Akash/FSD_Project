<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('learner');

$courseId = (int)($_GET['id'] ?? $_GET['course_id'] ?? 0);
if (!$courseId) {
    header('Location: ' . base_url('learner/dashboard.php'));
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

// Must be enrolled
$en = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
$en->execute([$user['id'], $courseId]);
if (!$en->fetch()) {
    setFlash('danger', 'You are not enrolled in this course.');
    header('Location: ' . base_url('learner/dashboard.php'));
    exit;
}

$courseStmt = $pdo->prepare("SELECT c.*, u.full_name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.id = ?");
$courseStmt->execute([$courseId]);
$course = $courseStmt->fetch();
if (!$course) {
    header('Location: ' . base_url('learner/dashboard.php'));
    exit;
}

$quizStmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? LIMIT 1");
$quizStmt->execute([$courseId]);
$quiz = $quizStmt->fetch();

$questions = [];
if ($quiz) {
    $qStmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
    $qStmt->execute([$quiz['id']]);
    $questions = $qStmt->fetchAll();
}

$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $quiz && !empty($questions)) {
    $answers = $_POST['answer'] ?? [];
    $correct = 0;
    $total = count($questions);
    foreach ($questions as $qq) {
        $given = strtoupper(trim($answers[$qq['id']] ?? ''));
        if ($given && $given === $qq['correct_option']) $correct++;
    }
    $score = $total > 0 ? (int)round(($correct / $total) * 100) : 0;
    $passScore = (int)($quiz['pass_score_percent'] ?? 70);
    $passed = $score >= $passScore ? 1 : 0;

    $pdo->prepare("INSERT INTO quiz_attempts (user_id, quiz_id, score_percent, correct_count, total_questions, passed) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$user['id'], $quiz['id'], $score, $correct, $total, $passed]);

    $result = [
        'score' => $score,
        'correct' => $correct,
        'total' => $total,
        'passed' => (bool)$passed,
        'passScore' => $passScore,
        'answers' => array_map(fn($v) => strtoupper(trim((string)$v)), $answers),
    ];
}

$attempts = [];
if ($quiz) {
    $aStmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY attempted_at DESC LIMIT 5");
    $aStmt->execute([$user['id'], $quiz['id']]);
    $attempts = $aStmt->fetchAll();
}

$pageTitle = 'Course Test';
$currentPage = '';
require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container" style="max-width: 900px;">
            <p class="course-meta"><a href="<?= base_url('learner/course.php?id=' . $courseId) ?>">← Back to Course</a></p>
            <h1 style="margin-top: 0.75rem;">Course Test</h1>
            <p class="course-meta"><?= htmlspecialchars($course['title']) ?> • By <?= htmlspecialchars($course['instructor_name']) ?></p>

            <?php if (!$quiz || empty($questions)): ?>
                <div class="dash-card" style="margin-top: 1.5rem;">
                    <h3>No test available yet</h3>
                    <p class="course-meta">This course doesn’t have a quiz right now.</p>
                </div>
            <?php else: ?>
                <?php if ($result): ?>
                    <div class="alert alert-<?= $result['passed'] ? 'success' : 'danger' ?>" style="margin-top: 1.5rem;">
                        Your score: <strong><?= $result['score'] ?>%</strong> (<?= $result['correct'] ?>/<?= $result['total'] ?>)
                        — Pass mark: <?= $result['passScore'] ?>%
                        <?php if ($result['passed']): ?> — You passed!<?php else: ?> — Try again.<?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?><div class="alert alert-danger" style="margin-top: 1.5rem;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

                <div class="dash-card" style="margin-top: 1.5rem;">
                    <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                    <form method="POST">
                        <?php foreach ($questions as $idx => $qq): ?>
                            <div style="padding: 1rem 0; border-bottom: 1px solid var(--border);">
                                <p style="margin: 0 0 0.75rem; font-weight: 600;"><?= ($idx + 1) ?>. <?= htmlspecialchars($qq['question_text']) ?></p>

                                <?php foreach (['A' => $qq['option_a'], 'B' => $qq['option_b'], 'C' => $qq['option_c'], 'D' => $qq['option_d']] as $opt => $label): ?>
                                    <?php
                                        $checked = $result ? (($result['answers'][$qq['id']] ?? '') === $opt) : ((($_POST['answer'][$qq['id']] ?? '') === $opt));
                                        $showCorrect = $result !== null;
                                        $isCorrect = $opt === $qq['correct_option'];
                                        $style = '';
                                        if ($showCorrect && $isCorrect) $style = 'border:1px solid rgba(34,197,94,0.5); background: rgba(34,197,94,0.06);';
                                    ?>
                                    <label style="display:block; padding: 0.65rem 0.75rem; border-radius: 10px; border: 1px solid var(--border); margin-bottom: 0.5rem; cursor:pointer; <?= $style ?>">
                                        <input type="radio" name="answer[<?= (int)$qq['id'] ?>]" value="<?= $opt ?>" <?= $checked ? 'checked' : '' ?> <?= $result ? 'disabled' : '' ?>>
                                        <strong style="margin-right: 0.5rem;"><?= $opt ?>.</strong> <?= htmlspecialchars($label) ?>
                                    </label>
                                <?php endforeach; ?>

                                <?php if ($result && $qq['explanation']): ?>
                                    <p class="course-meta" style="margin-top: 0.5rem;">Explanation: <?= htmlspecialchars($qq['explanation']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div style="display:flex; gap: 0.75rem; margin-top: 1rem; flex-wrap: wrap;">
                            <?php if (!$result): ?>
                                <button type="submit" class="btn btn-primary">Submit Test</button>
                            <?php else: ?>
                                <a class="btn btn-primary" href="<?= base_url('learner/quiz.php?id=' . $courseId) ?>">Retake Test</a>
                            <?php endif; ?>
                            <a class="btn btn-outline" href="<?= base_url('learner/course.php?id=' . $courseId) ?>">Back to Lessons</a>
                        </div>
                    </form>
                </div>

                <?php if (!empty($attempts)): ?>
                    <div class="dash-card" style="margin-top: 1.5rem;">
                        <h3>Recent Attempts</h3>
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach ($attempts as $a): ?>
                                <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--border); display:flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                                    <span class="course-meta"><?= date('M j, Y g:i A', strtotime($a['attempted_at'])) ?></span>
                                    <span><strong><?= (int)$a['score_percent'] ?>%</strong> (<?= (int)$a['correct_count'] ?>/<?= (int)$a['total_questions'] ?>) <?= $a['passed'] ? '— Passed' : '— Not passed' ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

