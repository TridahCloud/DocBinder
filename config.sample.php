<?php
// config.sample.php - Sample configuration file
// Copy this file to config.php and update with your actual database credentials

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'docbinder');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Application settings
define('APP_NAME', 'DocBinder');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost'); // Update with your domain

// File upload settings
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Security settings
define('SESSION_LIFETIME', 24 * 60 * 60); // 24 hours
define('PASSWORD_MIN_LENGTH', 6);

// Email settings (for notifications)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@example.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@example.com');
define('SMTP_FROM_NAME', 'DocBinder');

// Development settings
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session management
session_start();

// Helper functions
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_current_user_data() {
    global $pdo;
    
    if (!is_logged_in()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function sanitize_rich_text($data) {
    // Allow safe HTML tags for rich text content
    $allowed_tags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><div><span>';
    $cleaned = strip_tags(trim($data), $allowed_tags);
    
    // Remove color-related attributes and styles
    $cleaned = preg_replace('/\s*color\s*=\s*["\'][^"\']*["\']/', '', $cleaned);
    $cleaned = preg_replace('/\s*bgcolor\s*=\s*["\'][^"\']*["\']/', '', $cleaned);
    $cleaned = preg_replace('/\s*face\s*=\s*["\'][^"\']*["\']/', '', $cleaned);
    $cleaned = preg_replace('/\s*style\s*=\s*["\'][^"\']*color[^"\']*["\']/', '', $cleaned);
    $cleaned = preg_replace('/\s*style\s*=\s*["\'][^"\']*background[^"\']*["\']/', '', $cleaned);
    $cleaned = preg_replace('/\s*style\s*=\s*["\'][^"\']*font-family[^"\']*["\']/', '', $cleaned);
    $cleaned = preg_replace('/\s*style\s*=\s*["\'][^"\']*font-size[^"\']*["\']/', '', $cleaned);
    
    return $cleaned;
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
?>
