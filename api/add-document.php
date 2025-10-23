<?php
// api/add-document.php - Add document to binder
header('Content-Type: application/json');
require_once '../config.php';

// Debug logging
error_log("Add document request received");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$current_user = get_current_user_data();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$binder_id = (int)($_POST['binder_id'] ?? 0);
$title = sanitize_input($_POST['title'] ?? '');
$intro_text = sanitize_input($_POST['intro_text'] ?? '');
$outro_text = sanitize_input($_POST['outro_text'] ?? '');
$file_type = sanitize_input($_POST['file_type'] ?? '');

if (!$binder_id || !$title || !$file_type) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Verify binder ownership
    $stmt = $pdo->prepare("SELECT id FROM binders WHERE id = ? AND user_id = ?");
    $stmt->execute([$binder_id, $current_user['id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Binder not found']);
        exit;
    }
    
    $file_path = null;
    $text_content = null;
    $final_file_type = '';
    
    if ($file_type === 'file') {
        // Handle file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded']);
            exit;
        }
        
        $file = $_FILES['file'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
            exit;
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            echo json_encode(['success' => false, 'error' => 'File too large']);
            exit;
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = __DIR__ . '/../' . UPLOAD_PATH . $current_user['id'] . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            exit;
        }
        
        // Store the web-accessible path
        $web_path = UPLOAD_PATH . $current_user['id'] . '/' . $filename;
        
        $final_file_type = ($file_extension === 'pdf') ? 'pdf' : 'image';
        
    } elseif ($file_type === 'text') {
        // Handle text content - use rich text sanitization to preserve formatting
        $text_content = sanitize_rich_text($_POST['text_content'] ?? '');
        if (empty($text_content)) {
            echo json_encode(['success' => false, 'error' => 'Text content is required']);
            exit;
        }
        $final_file_type = 'text';
        
        // Debug: Log the content being saved
        error_log("Rich text content being saved: " . substr($text_content, 0, 200) . "...");
    }
    
    // Get next sort order
    $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM documents WHERE binder_id = ?");
    $stmt->execute([$binder_id]);
    $max_order = $stmt->fetch()['max_order'] ?? 0;
    $sort_order = $max_order + 1;
    
    // Insert document
    $stmt = $pdo->prepare("
        INSERT INTO documents (binder_id, title, intro_text, outro_text, file_path, file_type, text_content, sort_order) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $binder_id, 
        $title, 
        $intro_text, 
        $outro_text, 
        $web_path, 
        $final_file_type, 
        $text_content, 
        $sort_order
    ]);
    
    echo json_encode(['success' => true, 'document_id' => $pdo->lastInsertId()]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
