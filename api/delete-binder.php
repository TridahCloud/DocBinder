<?php
// api/delete-binder.php - Delete binder
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
$binder_id = (int)($input['binder_id'] ?? 0);

if (!$binder_id) {
    echo json_encode(['success' => false, 'error' => 'Binder ID required']);
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
    
    // Get all documents with file paths to delete files
    $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE binder_id = ? AND file_path IS NOT NULL");
    $stmt->execute([$binder_id]);
    $documents = $stmt->fetchAll();
    
    // Delete files
    foreach ($documents as $doc) {
        if ($doc['file_path']) {
            // Convert web path back to file system path
            $actual_file_path = __DIR__ . '/../' . $doc['file_path'];
            if (file_exists($actual_file_path)) {
                unlink($actual_file_path);
            }
        }
    }
    
    // Delete binder (documents will be deleted by CASCADE)
    $stmt = $pdo->prepare("DELETE FROM binders WHERE id = ?");
    $stmt->execute([$binder_id]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
