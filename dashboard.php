<?php
// dashboard.php - User dashboard
require_once 'config.php';

$page_title = "Dashboard";

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

include 'header.php';

$current_user = get_current_user_data();

// Get user's binders
try {
    $stmt = $pdo->prepare("
        SELECT b.*, 
               COUNT(d.id) as document_count,
               MAX(d.updated_at) as last_updated
        FROM binders b 
        LEFT JOIN documents d ON b.id = d.binder_id 
        WHERE b.user_id = ? 
        GROUP BY b.id 
        ORDER BY b.updated_at DESC
    ");
    $stmt->execute([$current_user['id']]);
    $binders = $stmt->fetchAll();
    
    // Get stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_binders FROM binders WHERE user_id = ?");
    $stmt->execute([$current_user['id']]);
    $total_binders = $stmt->fetch()['total_binders'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_documents 
        FROM documents d 
        JOIN binders b ON d.binder_id = b.id 
        WHERE b.user_id = ?
    ");
    $stmt->execute([$current_user['id']]);
    $total_documents = $stmt->fetch()['total_documents'];
    
} catch (PDOException $e) {
    $binders = [];
    $total_binders = 0;
    $total_documents = 0;
}
?>

<div class="container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="dashboard-title">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($current_user['first_name'] ?: $current_user['username']); ?>!</h1>
                <p>Manage your digital binders and documents</p>
            </div>
            <a href="create-binder.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Binder
            </a>
        </div>
        
        <!-- Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_binders; ?></div>
                <div class="stat-label">Total Binders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_documents; ?></div>
                <div class="stat-label">Total Documents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($binders, function($b) { return $b['is_public']; })); ?></div>
                <div class="stat-label">Public Binders</div>
            </div>
        </div>
    </div>
    
    <!-- Binders Grid -->
    <?php if (empty($binders)): ?>
        <div class="text-center" style="padding: 4rem 0;">
            <div style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;">
                <i class="fas fa-folder-open"></i>
            </div>
            <h2>No binders yet</h2>
            <p>Create your first digital binder to get started organizing your documents</p>
            <a href="create-binder.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Create Your First Binder
            </a>
        </div>
    <?php else: ?>
        <div class="binders-grid">
            <?php foreach ($binders as $binder): ?>
                <div class="binder-card" onclick="window.location.href='view-binder.php?id=<?php echo $binder['id']; ?>'">
                    <div class="binder-header">
                        <div class="binder-title"><?php echo htmlspecialchars($binder['title']); ?></div>
                        <div class="binder-description"><?php echo htmlspecialchars($binder['description'] ?: 'No description'); ?></div>
                        <div class="binder-meta">
                            <span>
                                <i class="fas fa-file"></i> <?php echo $binder['document_count']; ?> documents
                            </span>
                            <span>
                                <i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($binder['last_updated'] ?: $binder['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="binder-actions">
                        <button class="binder-action" onclick="event.stopPropagation(); window.location.href='view-binder.php?id=<?php echo $binder['id']; ?>'">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="binder-action" onclick="event.stopPropagation(); window.location.href='edit-binder.php?id=<?php echo $binder['id']; ?>'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="binder-action" onclick="event.stopPropagation(); shareBinder(<?php echo $binder['id']; ?>)">
                            <i class="fas fa-share"></i> Share
                        </button>
                        <button class="binder-action" onclick="event.stopPropagation(); deleteBinder(<?php echo $binder['id']; ?>)" style="color: var(--error-color, #dc2626);">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function shareBinder(binderId) {
    window.location.href = 'share-binder.php?id=' + binderId;
}

function deleteBinder(binderId) {
    if (confirm('Are you sure you want to delete this binder? This action cannot be undone.')) {
        fetch('api/delete-binder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ binder_id: binderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Binder deleted successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('Failed to delete binder', 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred', 'error');
        });
    }
}

function generateShareToken(binderId) {
    // This would normally be generated server-side
    return 'temp_token_' + binderId;
}
</script>

<?php include 'footer.php'; ?>
