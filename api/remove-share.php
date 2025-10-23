<?php
// api/remove-share.php - Remove email-based sharing
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
$share_id = (int)($input['share_id'] ?? 0);

if (!$share_id) {
    echo json_encode(['success' => false, 'error' => 'Share ID is required']);
    exit;
}

try {
    // Verify share ownership through binder
    $stmt = $pdo->prepare("
        SELECT sb.*, b.title as binder_title 
        FROM shared_binders sb 
        JOIN binders b ON sb.binder_id = b.id 
        WHERE sb.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$share_id, $current_user['id']]);
    $share = $stmt->fetch();
    
    if (!$share) {
        echo json_encode(['success' => false, 'error' => 'Share not found']);
        exit;
    }
    
    // Deactivate the share (soft delete)
    $stmt = $pdo->prepare("UPDATE shared_binders SET is_active = 0 WHERE id = ?");
    $stmt->execute([$share_id]);
    
    // Log the removal
    error_log("Share {$share_id} removed for binder {$share['binder_id']} by user {$current_user['id']}");
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
