<?php
// share-binder.php - Manage binder sharing
require_once 'config.php';

$page_title = "Share Binder";

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

include 'header.php';

$current_user = get_current_user_data();
$binder_id = (int)($_GET['id'] ?? 0);

if (!$binder_id) {
    header('Location: dashboard.php');
    exit;
}

try {
    // Verify binder ownership
    $stmt = $pdo->prepare("SELECT * FROM binders WHERE id = ? AND user_id = ?");
    $stmt->execute([$binder_id, $current_user['id']]);
    $binder = $stmt->fetch();
    
    if (!$binder) {
        header('Location: dashboard.php');
        exit;
    }
    
    // Get current shares
    $stmt = $pdo->prepare("
        SELECT sb.*, u.username as shared_by_username 
        FROM shared_binders sb 
        LEFT JOIN users u ON sb.shared_with_email = u.email
        WHERE sb.binder_id = ? AND sb.is_active = 1
        ORDER BY sb.created_at DESC
    ");
    $stmt->execute([$binder_id]);
    $shares = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="container" style="max-width: 800px; margin: 2rem auto;">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Share "<?php echo htmlspecialchars($binder['title']); ?>"</h2>
                <a href="view-binder.php?id=<?php echo $binder_id; ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Binder
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Add new share -->
            <div class="form-group">
                <label for="share_email" class="form-label">Share with Email Address</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="email" id="share_email" class="form-input" placeholder="Enter email address" style="flex: 1;">
                    <button onclick="addShare()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Access
                    </button>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.5rem;">
                    The person will receive an email with a link to view this binder.
                </div>
            </div>
            
            <!-- Current shares -->
            <div class="form-group">
                <h3>Current Shares</h3>
                <?php if (empty($shares)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        <i class="fas fa-share-alt" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <p>No one has access to this binder yet.</p>
                    </div>
                <?php else: ?>
                    <div class="shares-list">
                        <?php foreach ($shares as $share): ?>
                            <div class="share-item" data-share-id="<?php echo $share['id']; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); margin-bottom: 0.5rem;">
                                    <div>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($share['shared_with_email']); ?></div>
                                        <div style="font-size: 0.875rem; color: var(--text-muted);">
                                            <?php if ($share['shared_by_username']): ?>
                                                User account
                                            <?php else: ?>
                                                Guest access
                                            <?php endif; ?>
                                            • Shared <?php echo date('M j, Y', strtotime($share['created_at'])); ?>
                                            <?php if ($share['expires_at']): ?>
                                                • Expires <?php echo date('M j, Y', strtotime($share['expires_at'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="copyShareLink(<?php echo $share['id']; ?>)" class="btn btn-outline btn-sm" title="Copy Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button onclick="removeShare(<?php echo $share['id']; ?>)" class="btn btn-outline btn-sm" title="Remove Access" style="color: var(--error-color, #dc2626);">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function addShare() {
    const email = document.getElementById('share_email').value.trim();
    
    if (!email) {
        showNotification('Please enter an email address', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    btn.disabled = true;
    
    fetch('api/share-binder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            binder_id: <?php echo $binder_id; ?>,
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Access granted successfully', 'success');
            document.getElementById('share_email').value = '';
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.error || 'Failed to add access', 'error');
        }
    })
    .catch(error => {
        showNotification('An error occurred', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function removeShare(shareId) {
    if (confirm('Are you sure you want to remove access for this person?')) {
        fetch('api/remove-share.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                share_id: shareId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Access removed successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.error || 'Failed to remove access', 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred', 'error');
        });
    }
}

function copyShareLink(shareId) {
    const shareItem = document.querySelector(`[data-share-id="${shareId}"]`);
    const email = shareItem.querySelector('div > div').textContent.trim();
    
    // Generate the share link
    const shareUrl = `${window.location.origin}/shared-binder.php?email=${encodeURIComponent(email)}&binder=<?php echo $binder_id; ?>`;
    
    copyToClipboard(shareUrl);
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Allow Enter key to add share
document.getElementById('share_email').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        addShare();
    }
});
</script>

<?php include 'footer.php'; ?>
