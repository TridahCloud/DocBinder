<?php
// api/reorder-documents.php - Reorder documents in binder
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
$document_orders = $input['document_orders'] ?? [];

if (!$binder_id || empty($document_orders)) {
    echo json_encode(['success' => false, 'error' => 'Binder ID and document orders are required']);
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
    
    // Update document orders
    $stmt = $pdo->prepare("UPDATE documents SET sort_order = ? WHERE id = ? AND binder_id = ?");
    
    foreach ($document_orders as $order) {
        $document_id = (int)$order['id'];
        $sort_order = (int)$order['order'];
        
        $stmt->execute([$sort_order, $document_id, $binder_id]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
