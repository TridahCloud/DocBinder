<?php
// api/get-document.php - Get document data for editing
header('Content-Type: application/json');
require_once '../config.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$current_user = get_current_user_data();
$document_id = (int)($_GET['id'] ?? 0);

if (!$document_id) {
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

try {
    // Verify document ownership through binder
    $stmt = $pdo->prepare("
        SELECT d.* 
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
    
    echo json_encode(['success' => true, 'document' => $document]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
