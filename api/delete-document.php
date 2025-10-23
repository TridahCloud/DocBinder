<?php
// api/delete-document.php - Delete document
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

$input = json_decode(file_get_contents('php://input'), true);
$document_id = (int)($input['document_id'] ?? 0);

if (!$document_id) {
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

try {
    // Verify document ownership through binder
    $stmt = $pdo->prepare("
        SELECT d.id, d.file_path 
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
    
    // Delete file if it exists
    if ($document['file_path']) {
        // Convert web path back to file system path
        $actual_file_path = __DIR__ . '/../' . $document['file_path'];
        if (file_exists($actual_file_path)) {
            unlink($actual_file_path);
        }
    }
    
    // Delete document from database
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmt->execute([$document_id]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
