<?php
/**
 * One-time setup: Create database, import schema, create admin user
 * Run: http://localhost/skill-academy/setup.php
 * Delete this file after setup for security.
 */
// Load DB config only (avoid loading full config which may expect DB to exist)
require_once __DIR__ . '/config/db.php';
if (!defined('BASE_PATH')) define('BASE_PATH', '/skill-academy');

// Step 1: Connect to MySQL (without database) and create it
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $pdo->exec("USE `" . DB_NAME . "`");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "<br><br>Check config/db.php (host, user, password).");
}

// Step 2: Run schema - create tables
$schemaFile = __DIR__ . '/database/schema.sql';
if (!file_exists($schemaFile)) {
    die("Schema file not found: database/schema.sql");
}

$sql = file_get_contents($schemaFile);
$sql = preg_replace('/CREATE DATABASE IF NOT EXISTS[^;]+;/i', '', $sql);
$sql = preg_replace('/USE\s+\w+\s*;/i', '', $sql);
$sql = preg_replace('/--[^\n]*\n/', "\n", $sql); // Remove comment lines
$sql = str_replace("\r\n", "\n", $sql);

// Split by semicolon followed by newline
$statements = preg_split('/;\s*\n/', $sql);
foreach ($statements as $stmt) {
    $stmt = trim($stmt, " \t\n\r;");
    if (empty($stmt)) continue;
    try {
        $pdo->exec($stmt . ';');
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Duplicate') !== false || strpos($msg, 'already exists') !== false || strpos($msg, '1091') !== false) {
            // Ignore
        } else {
            die("Schema error: " . htmlspecialchars($msg) . "<br><br>Statement: " . htmlspecialchars(substr($stmt, 0, 200)) . "...");
        }
    }
}

// Ensure schema upgrades for existing databases
try {
    $col = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'youtube_playlist_url' LIMIT 1");
    $col->execute([DB_NAME]);
    if (!$col->fetch()) {
        $pdo->exec("ALTER TABLE courses ADD COLUMN youtube_playlist_url VARCHAR(500) DEFAULT NULL AFTER thumbnail_url");
        $msg = ($msg ?? '') . ' Courses table upgraded (YouTube playlist).';
    }
} catch (PDOException $e) {
    // Ignore upgrade failures (older MySQL permissions/config)
}

// Step 3: Create or update admin user
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@skillacademy.com'");
$stmt->execute();
if (!$stmt->fetch()) {
    $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES ('admin@skillacademy.com', ?, 'Platform Admin', 'admin')")->execute([$hash]);
    $msg = 'Database created and admin user added.';
} else {
    $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@skillacademy.com'")->execute([$hash]);
    $msg = 'Admin password reset to admin123.';
}

// Step 4: Create instructor and seed 10 courses (5 free, 5 paid)
$instructorHash = password_hash('instructor123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'instructor@skillacademy.com'");
$stmt->execute();
if (!$stmt->fetch()) {
    $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES ('instructor@skillacademy.com', ?, 'Sarah Johnson', 'instructor')")->execute([$instructorHash]);
}
$instructorId = $pdo->query("SELECT id FROM users WHERE email = 'instructor@skillacademy.com'")->fetchColumn();

// Step 4b: Create a demo learner account
$learnerHash = password_hash('learner123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'learner@skillacademy.com'");
$stmt->execute();
if (!$stmt->fetch()) {
    $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES ('learner@skillacademy.com', ?, 'Demo Learner', 'learner')")->execute([$learnerHash]);
} else {
    $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'learner@skillacademy.com'")->execute([$learnerHash]);
}

$courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
if ($courseCount == 0) {
    $courses = [
        // 5 FREE courses
        ['Introduction to Python Programming', 'python-basics', 1, 'Learn Python from scratch. Variables, loops, functions, and object-oriented programming.', 'Beginner to Python. Variables and data types. Control flow. Functions. OOP basics.', 0, 'beginner', 8],
        ['Web Design with HTML & CSS', 'web-design-html-css', 2, 'Build beautiful, responsive websites using HTML5 and CSS3.', 'HTML structure. CSS styling. Flexbox and Grid. Responsive design.', 0, 'beginner', 6],
        ['Digital Marketing Fundamentals', 'digital-marketing-fundamentals', 3, 'Master SEO, social media, and content marketing.', 'SEO basics. Social media strategy. Email marketing. Analytics.', 0, 'all', 5],
        ['Data Analysis with Excel', 'data-analysis-excel', 5, 'Analyze data using Excel: pivot tables, charts, and formulas.', 'Formulas. Pivot tables. Charts. Data visualization.', 0, 'beginner', 4],
        ['Business Communication Skills', 'business-communication', 4, 'Improve your professional communication and presentation skills.', 'Writing emails. Presentations. Meetings. Negotiation.', 0, 'all', 3],
        // 5 PAID courses
        ['Full-Stack JavaScript Development', 'fullstack-javascript', 1, 'Build complete web apps with Node.js, React, and MongoDB.', 'Node.js backend. React frontend. MongoDB. REST APIs. Deployment.', 49.99, 'intermediate', 40],
        ['UI/UX Design Masterclass', 'uiux-design-masterclass', 2, 'Design stunning user interfaces and create seamless user experiences.', 'User research. Wireframing. Prototyping. Figma. Design systems.', 59.99, 'intermediate', 25],
        ['Advanced SEO & Content Strategy', 'advanced-seo-content', 3, 'Rank #1 on Google with advanced SEO and content marketing.', 'Technical SEO. Content strategy. Link building. Analytics.', 39.99, 'advanced', 12],
        ['Machine Learning with Python', 'machine-learning-python', 5, 'Hands-on machine learning: algorithms, models, and real projects.', 'Scikit-learn. TensorFlow. Neural networks. Projects.', 79.99, 'advanced', 50],
        ['Startup & Entrepreneurship', 'startup-entrepreneurship', 4, 'Launch your own business: idea validation to scaling.', 'Idea validation. Business model. Funding. Growth strategies.', 44.99, 'intermediate', 15],
    ];
    $stmt = $pdo->prepare("INSERT INTO courses (instructor_id, category_id, title, slug, description, syllabus, price, level, duration_hours, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    foreach ($courses as $c) {
        $stmt->execute([$instructorId, $c[2], $c[0], $c[1], $c[3], $c[4], $c[5], $c[6], $c[7]]);
    }
    $msg .= ' 10 sample courses added (5 free, 5 paid).';
}

// Add course-specific YouTube playlists (so every course has related videos)
$playlistMap = [
    'python-basics' => 'https://www.youtube.com/playlist?list=PL-osiE80TeTt2d9bfVyTiXJA-UTHn6WwU',
    'web-design-html-css' => 'https://www.youtube.com/playlist?list=PLillGF-RfqbYeckUaD1z6nviTp31GLTH8',
    'digital-marketing-fundamentals' => 'https://www.youtube.com/playlist?list=PLbCp0wyLCjUwhuXhlHyZPccRPfw0-KYO8',
    'data-analysis-excel' => 'https://www.youtube.com/playlist?list=PLp6cX9mN-SNrvxR8PAeCH1x1cHGLTleVW',
    'business-communication' => 'https://www.youtube.com/playlist?list=PL2fCZiDqOYYUuVESWBPZj8_PCqrJSgm-3',
    'fullstack-javascript' => 'https://www.youtube.com/playlist?list=PLSDeUiTMfxW4zCLgOQgz4PWSN0QRmUUFR',
    'uiux-design-masterclass' => 'https://www.youtube.com/playlist?list=PL8bmndJfIX_OWoQemrwZJqlgkiKi5XU8q',
    'advanced-seo-content' => 'https://www.youtube.com/playlist?list=PLEiEAq2VkUUJpY2dHm-X7VB1acIH8d0Ze',
    'machine-learning-python' => 'https://www.youtube.com/playlist?list=PLQVvvaa0QuDfKTOs3Keq_kaG2P55YRn5v',
    'startup-entrepreneurship' => 'https://www.youtube.com/playlist?list=PLQ-uHSnFig5M9fW16o2l35jrfdsxGknNB',
];

try {
    $rows = $pdo->query("SELECT id, slug, youtube_playlist_url FROM courses")->fetchAll();
    $upd = $pdo->prepare("UPDATE courses SET youtube_playlist_url = ? WHERE id = ?");
    $addedPlaylists = 0;
    foreach ($rows as $r) {
        $slug = $r['slug'];
        if (empty($r['youtube_playlist_url']) && isset($playlistMap[$slug])) {
            $upd->execute([$playlistMap[$slug], (int)$r['id']]);
            $addedPlaylists++;
        }
    }
    if ($addedPlaylists > 0) $msg .= " $addedPlaylists course playlist(s) added.";
} catch (PDOException $e) {
    // ignore
}

// Step 5: Seed demo lessons + course tests (videos + quizzes)
$lessonCount = (int)$pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
$quizCount = (int)$pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();

if ($lessonCount === 0 || $quizCount === 0) {
    $courses = $pdo->query("SELECT id, slug, title FROM courses ORDER BY id ASC")->fetchAll();

    if ($lessonCount === 0) {
        $lessonInsert = $pdo->prepare("INSERT INTO lessons (course_id, title, content, video_url, document_url, sort_order, duration_minutes) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($courses as $c) {
            $slug = $c['slug'];
            $templates = [];

            if ($slug === 'python-basics') {
                $templates = [
                    ['Welcome & Setup', 'Install Python, choose an editor, and run your first script.', 'https://www.youtube.com/watch?v=kqtD5dpn9C8', '', 12],
                    ['Variables & Data Types', 'Learn strings, numbers, booleans, and basic operations.', 'https://www.youtube.com/watch?v=rfscVS0vtbw', '', 18],
                    ['Loops & Functions', 'Write reusable code and iterate with for/while loops.', 'https://www.youtube.com/watch?v=9Os0o3wzS_I', '', 22],
                ];
            } elseif ($slug === 'web-design-html-css') {
                $templates = [
                    ['HTML Structure', 'Learn the basic structure of an HTML page.', 'https://www.youtube.com/watch?v=UB1O30fR-EE', '', 14],
                    ['CSS Basics', 'Selectors, colors, spacing, and typography.', 'https://www.youtube.com/watch?v=yfoY53QXEnI', '', 20],
                    ['Responsive Layout', 'Flexbox/Grid fundamentals for responsive design.', 'https://www.youtube.com/watch?v=JJSoEo8JSnc', '', 24],
                ];
            } elseif ($slug === 'digital-marketing-fundamentals') {
                $templates = [
                    ['Marketing Overview', 'What is digital marketing and how channels work together?', null, '', 16],
                    ['SEO & Content Basics', 'Keywords, on-page SEO, and content strategy fundamentals.', null, '', 19],
                    ['Analytics Basics', 'Measure performance using simple metrics and goals.', null, '', 15],
                ];
            } elseif ($slug === 'data-analysis-excel') {
                $templates = [
                    ['Excel Foundations', 'Learn the Excel interface, data entry, and core shortcuts.', null, '', 14],
                    ['Formulas & Functions', 'Use SUM/IF/LOOKUP-style formulas to analyze data.', null, '', 18],
                    ['Pivot Tables & Charts', 'Summarize and visualize datasets quickly.', null, '', 20],
                ];
            } elseif ($slug === 'business-communication') {
                $templates = [
                    ['Professional Communication', 'Clear emails, structured messages, and tone.', null, '', 12],
                    ['Meetings & Collaboration', 'Run effective meetings and handle difficult conversations.', null, '', 15],
                    ['Presentations', 'Tell a story, design slides, and deliver confidently.', null, '', 18],
                ];
            } elseif ($slug === 'fullstack-javascript') {
                $templates = [
                    ['JavaScript Essentials', 'Modern JavaScript fundamentals and tooling.', null, '', 20],
                    ['Backend with Node.js', 'Build APIs with Node/Express and connect a database.', null, '', 24],
                    ['Frontend with React', 'Build UI components and connect to APIs.', null, '', 22],
                ];
            } elseif ($slug === 'uiux-design-masterclass') {
                $templates = [
                    ['UX Foundations', 'User research, personas, and problem definition.', null, '', 18],
                    ['Wireframes to UI', 'Turn ideas into wireframes and polished UI screens.', null, '', 22],
                    ['Figma Prototyping', 'Create clickable prototypes and handoff.', null, '', 20],
                ];
            } elseif ($slug === 'advanced-seo-content') {
                $templates = [
                    ['Technical SEO', 'Crawling, indexing, site structure, and performance.', null, '', 18],
                    ['Content Strategy', 'Topic research, clustering, and on-page optimization.', null, '', 20],
                    ['Link Building & Analytics', 'Authority building and measuring results.', null, '', 18],
                ];
            } elseif ($slug === 'machine-learning-python') {
                $templates = [
                    ['ML Overview', 'What machine learning is and how it’s used.', null, '', 16],
                    ['Models & Evaluation', 'Train models and measure performance properly.', null, '', 22],
                    ['Hands-on with Python', 'Use common libraries and build a small project.', null, '', 24],
                ];
            } elseif ($slug === 'startup-entrepreneurship') {
                $templates = [
                    ['Idea & Validation', 'Find a problem and validate with users.', null, '', 16],
                    ['MVP & Launch', 'Build the smallest solution and launch quickly.', null, '', 20],
                    ['Growth & Funding', 'Acquire customers and understand fundraising basics.', null, '', 18],
                ];
            } else {
                $templates = [
                    ['Course Welcome', 'Start here to understand what you will learn in this course.', 'https://www.youtube.com/watch?v=ysz5S6PUM-U', '', 10],
                    ['Core Concepts', 'We cover the main ideas you need to master.', 'https://www.youtube.com/watch?v=ysz5S6PUM-U', '', 15],
                    ['Practical Exercise', 'Apply what you learned with a small exercise.', 'https://www.youtube.com/watch?v=ysz5S6PUM-U', '', 12],
                ];
            }

            $order = 1;
            foreach ($templates as $t) {
                $lessonInsert->execute([(int)$c['id'], $t[0], $t[1], $t[2], $t[3], $order, (int)$t[4]]);
                $order++;
            }
        }

        $msg .= ' Demo lessons added.';
    }

    if ($quizCount === 0) {
        $quizInsert = $pdo->prepare("INSERT INTO quizzes (course_id, title, pass_score_percent) VALUES (?, ?, ?)");
        $questionInsert = $pdo->prepare("INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($courses as $c) {
            $courseId = (int)$c['id'];
            $slug = $c['slug'];

            $quizInsert->execute([$courseId, 'Course Test: ' . $c['title'], 70]);
            $quizId = (int)$pdo->lastInsertId();

            $questions = [];
            if ($slug === 'python-basics') {
                $questions = [
                    ['Which keyword defines a function in Python?', 'func', 'define', 'def', 'fn', 'C', 'Python uses `def` to define a function.'],
                    ['What is the output of `print(2 + 3 * 2)`?', '10', '12', '8', '7', 'D', 'Multiplication happens before addition: 3*2=6, 2+6=8.'],
                    ['Which type is `True`?', 'string', 'boolean', 'integer', 'list', 'B', '`True` is a boolean value.'],
                    ['Which loop is used to iterate over a sequence?', 'for', 'repeat', 'loop', 'each', 'A', '`for` loops iterate over sequences.'],
                    ['Which of these is a list literal?', '{1,2,3}', '(1,2,3)', '[1,2,3]', '<1,2,3>', 'C', 'Square brackets create a list.'],
                ];
            } elseif ($slug === 'web-design-html-css') {
                $questions = [
                    ['Which tag is used for the largest heading?', '<h6>', '<p>', '<h1>', '<head>', 'C', '`<h1>` is the largest heading.'],
                    ['Which CSS property changes text color?', 'font-style', 'color', 'background', 'text-align', 'B', 'Use `color` to set text color.'],
                    ['Which layout system is best for 1D alignment?', 'Flexbox', 'Canvas', 'SVG', 'Tables', 'A', 'Flexbox is great for one-dimensional layouts.'],
                    ['Which unit is relative to the root font size?', 'px', 'em', 'rem', '%', 'C', '`rem` is relative to the root font-size.'],
                    ['Which selector targets a class?', '#header', '.header', 'header>', 'header[]', 'B', 'Classes are selected with a dot `.className`.'],
                ];
            } else {
                $questions = [
                    ['What is the best first step when starting a new course?', 'Skip the intro', 'Read the syllabus', 'Only watch videos', 'Ignore practice', 'B', 'The syllabus helps you understand the structure and goals.'],
                    ['Which option helps you learn faster?', 'No practice', 'Only theory', 'Practice + review', 'Never take notes', 'C', 'Practice and review improve retention.'],
                    ['How should you handle mistakes during learning?', 'Give up', 'Avoid feedback', 'Use them to improve', 'Hide them', 'C', 'Mistakes are part of the learning process.'],
                    ['What is a good study habit?', 'Cram once', 'Consistent schedule', 'Never revise', 'Multitask always', 'B', 'Consistency beats cramming for long-term learning.'],
                    ['What should you do after finishing lessons?', 'Stop immediately', 'Take the course test', 'Delete notes', 'Skip feedback', 'B', 'A test helps confirm what you learned.'],
                ];
            }

            foreach ($questions as $q) {
                $questionInsert->execute([$quizId, $q[0], $q[1], $q[2], $q[3], $q[4], $q[5], $q[6]]);
            }
        }

        $msg .= ' Course tests (quizzes) added.';
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Setup Complete</title></head>
<body style="font-family: sans-serif; padding: 2rem;">
<h1>Setup Complete!</h1>
<p><?= $msg ?></p>
<p><strong>Admin login:</strong> admin@skillacademy.com / admin123</p>
<p><strong>Learner login:</strong> learner@skillacademy.com / learner123</p>
<p><strong>Instructor login:</strong> instructor@skillacademy.com / instructor123</p>
<p><strong>Delete this file (setup.php) for security.</strong></p>
<p><a href="<?= rtrim(BASE_PATH, '/') ?>/index.php">Go to Home</a></p>
</body>
</html>
