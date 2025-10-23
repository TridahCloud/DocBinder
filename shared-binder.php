<?php
// shared-binder.php - Email-based binder sharing viewer
require_once 'config.php';

$page_title = "Shared Binder";
include 'header.php';

$email = $_GET['email'] ?? '';
$binder_id = (int)($_GET['binder'] ?? 0);
$document_id = (int)($_GET['doc'] ?? 0);

if (!$email || !$binder_id) {
    echo '<div class="container text-center" style="padding: 4rem 0;">
            <h1>Invalid Share Link</h1>
            <p>This sharing link is invalid or incomplete.</p>
            <a href="index.php" class="btn btn-primary">Go Home</a>
          </div>';
    include 'footer.php';
    exit;
}

try {
    // Verify email-based access
    $stmt = $pdo->prepare("
        SELECT b.*, sb.shared_with_email, sb.expires_at, u.username as shared_by_username
        FROM binders b 
        JOIN shared_binders sb ON b.id = sb.binder_id 
        LEFT JOIN users u ON sb.shared_by_user_id = u.id
        WHERE b.id = ? AND sb.shared_with_email = ? AND sb.is_active = 1
        AND (sb.expires_at IS NULL OR sb.expires_at > NOW())
    ");
    $stmt->execute([$binder_id, $email]);
    $binder = $stmt->fetch();
    
    if (!$binder) {
        echo '<div class="container text-center" style="padding: 4rem 0;">
                <h1>Access Denied</h1>
                <p>You don\'t have access to this binder or the link has expired.</p>
                <a href="index.php" class="btn btn-primary">Go Home</a>
              </div>';
        include 'footer.php';
        exit;
    }
    
    // Get documents
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE binder_id = ? ORDER BY sort_order, created_at");
    $stmt->execute([$binder['id']]);
    $documents = $stmt->fetchAll();
    
    // Get current document
    $current_document = null;
    if ($document_id) {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND binder_id = ?");
        $stmt->execute([$document_id, $binder['id']]);
        $current_document = $stmt->fetch();
    }
    
    // If no specific document requested, get first one
    if (!$current_document && !empty($documents)) {
        $current_document = $documents[0];
    }
    
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}
?>

<div class="binder-viewer">
    <!-- Sidebar -->
    <div class="binder-sidebar">
        <div class="binder-sidebar-header">
            <div class="binder-sidebar-title"><?php echo htmlspecialchars($binder['title']); ?></div>
            <div class="binder-sidebar-description"><?php echo htmlspecialchars($binder['description'] ?: 'No description'); ?></div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                <i class="fas fa-share-alt"></i> Shared with <?php echo htmlspecialchars($email); ?>
                <?php if ($binder['shared_by_username']): ?>
                    <br><i class="fas fa-user"></i> By <?php echo htmlspecialchars($binder['shared_by_username']); ?>
                <?php endif; ?>
                <?php if ($binder['expires_at']): ?>
                    <br><i class="fas fa-clock"></i> Expires <?php echo date('M j, Y', strtotime($binder['expires_at'])); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <ul class="document-list">
            <?php foreach ($documents as $doc): ?>
                <li class="document-item <?php echo ($current_document && $doc['id'] == $current_document['id']) ? 'active' : ''; ?>"
                    onclick="window.location.href='shared-binder.php?email=<?php echo urlencode($email); ?>&binder=<?php echo $binder['id']; ?>&doc=<?php echo $doc['id']; ?>'">
                    <div class="document-item-title"><?php echo htmlspecialchars($doc['title']); ?></div>
                    <div class="document-item-meta">
                        <?php echo ucfirst($doc['file_type']); ?>
                        <?php if ($doc['file_type'] === 'text'): ?>
                            - Text Document
                        <?php elseif ($doc['file_type'] === 'pdf'): ?>
                            - PDF File
                        <?php else: ?>
                            - Image File
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
            
            <?php if (empty($documents)): ?>
                <li style="padding: 1rem; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-file-plus" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                    No documents available
                </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="binder-content">
        <div class="binder-toolbar">
            <div class="binder-nav">
                <?php if ($current_document): ?>
                    <?php
                    $current_index = array_search($current_document, $documents);
                    $prev_doc = ($current_index > 0) ? $documents[$current_index - 1] : null;
                    $next_doc = ($current_index < count($documents) - 1) ? $documents[$current_index + 1] : null;
                    ?>
                    <button class="binder-nav-btn" 
                            onclick="window.location.href='shared-binder.php?email=<?php echo urlencode($email); ?>&binder=<?php echo $binder['id']; ?>&doc=<?php echo $prev_doc['id']; ?>'"
                            <?php echo !$prev_doc ? 'disabled' : ''; ?>>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button class="binder-nav-btn" 
                            onclick="window.location.href='shared-binder.php?email=<?php echo urlencode($email); ?>&binder=<?php echo $binder['id']; ?>&doc=<?php echo $next_doc['id']; ?>'"
                            <?php echo !$next_doc ? 'disabled' : ''; ?>>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-outline btn-sm" onclick="copyToClipboard(window.location.href)">
                    <i class="fas fa-link"></i> Copy Link
                </button>
                <?php if (!is_logged_in()): ?>
                    <a href="register.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="binder-viewport">
            <?php if ($current_document): ?>
                <div class="document-content">
                    <div class="document-header">
                        <h1 class="document-title"><?php echo htmlspecialchars($current_document['title']); ?></h1>
                        <?php if ($current_document['intro_text']): ?>
                            <div class="document-intro"><?php echo nl2br(htmlspecialchars($current_document['intro_text'])); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="document-body">
                        <?php if ($current_document['file_type'] === 'pdf' && $current_document['file_path']): ?>
                            <iframe src="<?php echo htmlspecialchars($current_document['file_path']); ?>" 
                                    class="pdf-viewer" 
                                    type="application/pdf">
                                <p>Your browser doesn't support PDF viewing. 
                                   <a href="<?php echo htmlspecialchars($current_document['file_path']); ?>" target="_blank">Download the PDF</a>
                                </p>
                            </iframe>
                        <?php elseif ($current_document['file_type'] === 'image' && $current_document['file_path']): ?>
                            <img src="<?php echo htmlspecialchars($current_document['file_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($current_document['title']); ?>" 
                                 class="image-viewer">
                        <?php elseif ($current_document['file_type'] === 'text'): ?>
                            <div class="rich-text-content">
                                <?php echo $current_document['text_content']; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                <i class="fas fa-file" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p>No content available for this document.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($current_document['outro_text']): ?>
                        <div class="document-outro">
                            <?php echo nl2br(htmlspecialchars($current_document['outro_text'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem; color: var(--text-muted);">
                    <i class="fas fa-file-plus" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                    <h2>No documents in this binder</h2>
                    <p>This binder doesn't contain any documents yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
