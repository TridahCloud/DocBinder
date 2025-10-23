<?php
// api/share-binder.php - Add email-based sharing
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
$email = trim($input['email'] ?? '');

if (!$binder_id || !$email) {
    echo json_encode(['success' => false, 'error' => 'Binder ID and email are required']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

try {
    // Verify binder ownership
    $stmt = $pdo->prepare("SELECT * FROM binders WHERE id = ? AND user_id = ?");
    $stmt->execute([$binder_id, $current_user['id']]);
    $binder = $stmt->fetch();
    
    if (!$binder) {
        echo json_encode(['success' => false, 'error' => 'Binder not found']);
        exit;
    }
    
    // Check if already shared with this email
    $stmt = $pdo->prepare("SELECT id FROM shared_binders WHERE binder_id = ? AND shared_with_email = ? AND is_active = 1");
    $stmt->execute([$binder_id, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'This binder is already shared with this email']);
        exit;
    }
    
    // Generate unique access token
    $access_token = generate_token(32);
    
    // Set expiration date (30 days from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Insert share record
    $stmt = $pdo->prepare("
        INSERT INTO shared_binders (binder_id, shared_by_user_id, shared_with_email, access_token, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$binder_id, $current_user['id'], $email, $access_token, $expires_at]);
    
    // TODO: Send email notification here
    // For now, we'll just log it
    error_log("Binder {$binder_id} shared with {$email} by user {$current_user['id']}");
    
    echo json_encode(['success' => true, 'share_id' => $pdo->lastInsertId()]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
