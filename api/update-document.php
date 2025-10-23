<?php
// api/update-document.php - Update document
header('Content-Type: application/json');
require_once '../config.php';

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

$document_id = (int)($_POST['document_id'] ?? 0);
$title = sanitize_input($_POST['title'] ?? '');
$intro_text = sanitize_input($_POST['intro_text'] ?? '');
$outro_text = sanitize_input($_POST['outro_text'] ?? '');
$file_type = sanitize_input($_POST['file_type'] ?? '');

if (!$document_id || !$title) {
    echo json_encode(['success' => false, 'error' => 'Document ID and title are required']);
    exit;
}

try {
    // Verify document ownership through binder
    $stmt = $pdo->prepare("
        SELECT d.*, b.user_id 
        FROM documents d 
        JOIN binders b ON d.binder_id = b.id 
        WHERE d.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$document_id, $current_user['id']]);
    $document = $stmt->fetch();
    
    if (!$document) {
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }
    
    $file_path = $document['file_path'];
    $text_content = $document['text_content'];
    $final_file_type = $document['file_type'];
    
    // Handle file upload if new file provided
    if ($file_type === 'file' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Delete old file if it exists
        if ($document['file_path']) {
            $old_file_path = __DIR__ . '/../' . $document['file_path'];
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
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
        $file_path = UPLOAD_PATH . $current_user['id'] . '/' . $filename;
        $final_file_type = ($file_extension === 'pdf') ? 'pdf' : 'image';
        
    } elseif ($file_type === 'text') {
        // Handle text content - use rich text sanitization to preserve formatting
        $text_content = sanitize_rich_text($_POST['text_content'] ?? '');
        if (empty($text_content)) {
            echo json_encode(['success' => false, 'error' => 'Text content is required']);
            exit;
        }
        $file_path = null;
        $final_file_type = 'text';
    }
    
    // Update document
    $stmt = $pdo->prepare("
        UPDATE documents 
        SET title = ?, intro_text = ?, outro_text = ?, file_path = ?, file_type = ?, text_content = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $title, 
        $intro_text, 
        $outro_text, 
        $file_path, 
        $final_file_type, 
        $text_content, 
        $document_id
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
