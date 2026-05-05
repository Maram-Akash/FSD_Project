<?php
/**
 * Application Configuration
 * For XAMPP: Place project in htdocs folder (e.g., htdocs/skill-academy)
 */
if (!defined('BASE_PATH')) {
    // Auto-detect BASE_PATH from DOCUMENT_ROOT, so links don't jump to XAMPP root.
    // Example: C:\xampp\htdocs\skill-academy => /skill-academy
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : null;
    $projectRoot = realpath(__DIR__ . '/..');
    $basePath = '';
    if ($docRoot && $projectRoot) {
        $docRootNorm = str_replace('\\', '/', rtrim($docRoot, '\\/'));
        $projRootNorm = str_replace('\\', '/', rtrim($projectRoot, '\\/'));
        if (stripos($projRootNorm, $docRootNorm) === 0) {
            $rel = substr($projRootNorm, strlen($docRootNorm));
            $rel = '/' . trim($rel, '/');
            $basePath = ($rel === '/') ? '' : $rel;
        }
    }
    define('BASE_PATH', $basePath);
}
define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_PATH);

function base_url($path = '') {
    return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
}

function youtube_playlist_embed_url($urlOrId) {
    $val = trim((string)$urlOrId);
    if ($val === '') return null;

    // If user pasted just the playlist ID (starts with PL/UU/OLAK/etc)
    if (!str_contains($val, 'http') && preg_match('/^[A-Za-z0-9_-]{10,}$/', $val)) {
        return 'https://www.youtube.com/embed/videoseries?list=' . rawurlencode($val);
    }

    $parts = parse_url($val);
    if (!$parts) return null;
    $query = $parts['query'] ?? '';
    parse_str($query, $qs);
    $list = $qs['list'] ?? null;
    if (!$list) return null;
    return 'https://www.youtube.com/embed/videoseries?list=' . rawurlencode($list);
}

function youtube_video_embed_url($urlOrId) {
    $val = trim((string)$urlOrId);
    if ($val === '') return null;

    // If user pasted just the video ID
    if (!str_contains($val, 'http') && preg_match('/^[A-Za-z0-9_-]{6,}$/', $val)) {
        return 'https://www.youtube.com/embed/' . rawurlencode($val);
    }

    $parts = parse_url($val);
    if (!$parts || empty($parts['host'])) return null;
    $host = strtolower($parts['host']);
    $path = $parts['path'] ?? '';
    $query = $parts['query'] ?? '';

    if ($host === 'youtu.be') {
        $id = trim($path, '/');
        return $id ? 'https://www.youtube.com/embed/' . rawurlencode($id) : null;
    }
    if (str_contains($host, 'youtube.com')) {
        if (str_starts_with($path, '/embed/')) return $val;
        parse_str($query, $qs);
        $id = $qs['v'] ?? null;
        return $id ? 'https://www.youtube.com/embed/' . rawurlencode($id) : null;
    }
    return null;
}

// Include database config
require_once __DIR__ . '/db.php';
?>
