<?php
/**
 * Authentication Helper Functions
 */
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, email, full_name, role, avatar_url FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireLogin() {
    if (!isLoggedIn()) {
        $redirect = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header('Location: ' . base_url('auth/login.php') . '?redirect=' . urlencode($redirect));
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    $user = getCurrentUser();
    if (!in_array($user['role'], (array)$roles)) {
        header('Location: ' . base_url('index.php') . '?error=unauthorized');
        exit;
    }
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
