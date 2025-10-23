<?php
// view-binder.php - View binder with documents
require_once 'config.php';

$page_title = "View Binder";

$binder_id = (int)($_GET['id'] ?? 0);
$document_id = (int)($_GET['doc'] ?? 0);

if (!$binder_id) {
    if (is_logged_in()) {
        header('Location: dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$is_logged_in = is_logged_in();
$current_user = $is_logged_in ? get_current_user_data() : null;

try {
    // Get binder info - check if it's public or owned by current user
    if ($is_logged_in) {
        // Logged in users can see their own binders or public binders
        $stmt = $pdo->prepare("SELECT * FROM binders WHERE id = ? AND (user_id = ? OR is_public = 1)");
        $stmt->execute([$binder_id, $current_user['id']]);
    } else {
        // Non-logged in users can only see public binders
        $stmt = $pdo->prepare("SELECT * FROM binders WHERE id = ? AND is_public = 1");
        $stmt->execute([$binder_id]);
    }
    
    $binder = $stmt->fetch();
    
    if (!$binder) {
        // Binder doesn't exist or user doesn't have access
        include 'header.php';
        echo '<div class="container text-center" style="padding: 4rem 0;">
                <h1>Access Denied</h1>
                <p>You don\'t have access to this binder or it doesn\'t exist.</p>';
        if (!$is_logged_in) {
            echo '<p>If this is your binder, please <a href="login.php">log in</a> to view it.</p>';
        }
        echo '<a href="' . ($is_logged_in ? 'dashboard.php' : 'index.php') . '" class="btn btn-primary">Go ' . ($is_logged_in ? 'to Dashboard' : 'Home') . '</a>
              </div>';
        include 'footer.php';
        exit;
    }
    
    // Include header after access check
    include 'header.php';
    
    // Get documents
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE binder_id = ? ORDER BY sort_order, created_at");
    $stmt->execute([$binder_id]);
    $documents = $stmt->fetchAll();
    
    // Get current document
    $current_document = null;
    if ($document_id) {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND binder_id = ?");
        $stmt->execute([$document_id, $binder_id]);
        $current_document = $stmt->fetch();
    }
    
    // If no specific document requested, get first one
    if (!$current_document && !empty($documents)) {
        $current_document = $documents[0];
    }
    
} catch (PDOException $e) {
    header('Location: dashboard.php');
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
                <?php if ($is_logged_in && $binder['user_id'] == $current_user['id']): ?>
                    <i class="fas fa-user"></i> Your Binder
                <?php elseif ($binder['is_public']): ?>
                    <i class="fas fa-globe"></i> Public Binder
                <?php endif; ?>
            </div>
        </div>
        
        <ul class="document-list">
            <?php foreach ($documents as $doc): ?>
                <li class="document-item <?php echo ($current_document && $doc['id'] == $current_document['id']) ? 'active' : ''; ?>"
                    onclick="window.location.href='view-binder.php?id=<?php echo $binder_id; ?>&doc=<?php echo $doc['id']; ?>'">
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
                    No documents yet
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
                            onclick="window.location.href='view-binder.php?id=<?php echo $binder_id; ?>&doc=<?php echo $prev_doc['id']; ?>'"
                            <?php echo !$prev_doc ? 'disabled' : ''; ?>>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button class="binder-nav-btn" 
                            onclick="window.location.href='view-binder.php?id=<?php echo $binder_id; ?>&doc=<?php echo $next_doc['id']; ?>'"
                            <?php echo !$next_doc ? 'disabled' : ''; ?>>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <?php if ($is_logged_in && $binder['user_id'] == $current_user['id']): ?>
                    <!-- Owner actions -->
                    <a href="edit-binder.php?id=<?php echo $binder_id; ?>" class="btn btn-outline btn-sm">
                        <i class="fas fa-edit"></i> Edit Binder
                    </a>
                    <button class="btn btn-outline btn-sm" onclick="shareBinder(<?php echo $binder_id; ?>)">
                        <i class="fas fa-share"></i> Share
                    </button>
                <?php elseif ($is_logged_in): ?>
                    <!-- Logged in user viewing public binder -->
                    <button class="btn btn-outline btn-sm" onclick="copyToClipboard(window.location.href)">
                        <i class="fas fa-link"></i> Copy Link
                    </button>
                <?php else: ?>
                    <!-- Non-logged in user viewing public binder -->
                    <button class="btn btn-outline btn-sm" onclick="copyToClipboard(window.location.href)">
                        <i class="fas fa-link"></i> Copy Link
                    </button>
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
                    <p>Add some documents to get started</p>
                    <a href="edit-binder.php?id=<?php echo $binder_id; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Documents
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function shareBinder(binderId) {
    window.location.href = 'share-binder.php?id=' + binderId;
}
</script>

<?php include 'footer.php'; ?>
