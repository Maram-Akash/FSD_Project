<?php
/**
 * Seed 10 sample courses (5 free, 5 paid)
 * Run once: http://localhost/skill-academy/database/seed-courses.php
 */
require_once __DIR__ . '/../config/db.php';
if (!defined('BASE_PATH')) define('BASE_PATH', '/skill-academy');

$pdo = getDBConnection();

// Ensure playlist column exists (safe no-op if already added)
try {
    $col = $pdo->query("SHOW COLUMNS FROM courses LIKE 'youtube_playlist_url'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE courses ADD COLUMN youtube_playlist_url VARCHAR(500) DEFAULT NULL AFTER thumbnail_url");
    }
} catch (PDOException $e) {
    // ignore
}

// Course-specific YouTube playlists
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

// Ensure instructor exists
$instructorHash = password_hash('instructor123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'instructor@skillacademy.com'");
$stmt->execute();
if (!$stmt->fetch()) {
    $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES ('instructor@skillacademy.com', ?, 'Sarah Johnson', 'instructor')")->execute([$instructorHash]);
}
$instructorId = $pdo->query("SELECT id FROM users WHERE email = 'instructor@skillacademy.com'")->fetchColumn();

$courses = [
    // 5 FREE
    ['Introduction to Python Programming', 'python-basics', 1, 'Learn Python from scratch. Variables, loops, functions, and object-oriented programming.', 'Beginner to Python. Variables and data types. Control flow. Functions. OOP basics.', 0, 'beginner', 8],
    ['Web Design with HTML & CSS', 'web-design-html-css', 2, 'Build beautiful, responsive websites using HTML5 and CSS3.', 'HTML structure. CSS styling. Flexbox and Grid. Responsive design.', 0, 'beginner', 6],
    ['Digital Marketing Fundamentals', 'digital-marketing-fundamentals', 3, 'Master SEO, social media, and content marketing.', 'SEO basics. Social media strategy. Email marketing. Analytics.', 0, 'all', 5],
    ['Data Analysis with Excel', 'data-analysis-excel', 5, 'Analyze data using Excel: pivot tables, charts, and formulas.', 'Formulas. Pivot tables. Charts. Data visualization.', 0, 'beginner', 4],
    ['Business Communication Skills', 'business-communication', 4, 'Improve your professional communication and presentation skills.', 'Writing emails. Presentations. Meetings. Negotiation.', 0, 'all', 3],
    // 5 PAID
    ['Full-Stack JavaScript Development', 'fullstack-javascript', 1, 'Build complete web apps with Node.js, React, and MongoDB.', 'Node.js backend. React frontend. MongoDB. REST APIs. Deployment.', 49.99, 'intermediate', 40],
    ['UI/UX Design Masterclass', 'uiux-design-masterclass', 2, 'Design stunning user interfaces and create seamless user experiences.', 'User research. Wireframing. Prototyping. Figma. Design systems.', 59.99, 'intermediate', 25],
    ['Advanced SEO & Content Strategy', 'advanced-seo-content', 3, 'Rank #1 on Google with advanced SEO and content marketing.', 'Technical SEO. Content strategy. Link building. Analytics.', 39.99, 'advanced', 12],
    ['Machine Learning with Python', 'machine-learning-python', 5, 'Hands-on machine learning: algorithms, models, and real projects.', 'Scikit-learn. TensorFlow. Neural networks. Projects.', 79.99, 'advanced', 50],
    ['Startup & Entrepreneurship', 'startup-entrepreneurship', 4, 'Launch your own business: idea validation to scaling.', 'Idea validation. Business model. Funding. Growth strategies.', 44.99, 'intermediate', 15],
];

$stmt = $pdo->prepare("INSERT INTO courses (instructor_id, category_id, title, slug, description, syllabus, price, level, duration_hours, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
$added = 0;
foreach ($courses as $c) {
    $check = $pdo->prepare("SELECT 1 FROM courses WHERE slug = ?");
    $check->execute([$c[1]]);
    if (!$check->fetch()) {
        $stmt->execute([$instructorId, $c[2], $c[0], $c[1], $c[3], $c[4], $c[5], $c[6], $c[7]]);
        $added++;
    }
}

// Update playlists for seeded courses
try {
    $upd = $pdo->prepare("UPDATE courses SET youtube_playlist_url = ? WHERE slug = ? AND (youtube_playlist_url IS NULL OR youtube_playlist_url = '')");
    foreach ($playlistMap as $slug => $url) {
        $upd->execute([$url, $slug]);
    }
} catch (PDOException $e) {
    // ignore
}
?>
<!DOCTYPE html>
<html><head><title>Courses Seeded</title></head>
<body style="font-family:sans-serif;padding:2rem">
<h1>Done!</h1>
<p><?= $added ?> course(s) added (5 free, 5 paid total available).</p>
<p>Instructor: instructor@skillacademy.com / instructor123</p>
<p><a href="<?= (defined('BASE_PATH') ? rtrim(BASE_PATH,'/') : '/skill-academy') ?>/index.php">Go to Home</a></p>
</body></html>
