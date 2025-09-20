<?php
// Database configuration for StarWash Laundry
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'starwash_laundry');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isSeller() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seller';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function redirectTo($location) {
    header("Location: $location");
    exit();
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirectTo('../../pages/login.php');
    }
}
?>