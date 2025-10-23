<?php
// create-binder.php - Create new binder
require_once 'config.php';

$page_title = "Create New Binder";

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

include 'header.php';

$current_user = get_current_user_data();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    if (empty($title)) {
        $error = 'Please enter a title for your binder.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO binders (user_id, title, description, is_public) VALUES (?, ?, ?, ?)");
            $stmt->execute([$current_user['id'], $title, $description, $is_public]);
            
            $binder_id = $pdo->lastInsertId();
            $success = 'Binder created successfully!';
            
            // Redirect to edit page to add documents
            header('Location: edit-binder.php?id=' . $binder_id);
            exit;
            
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>

<div class="container" style="max-width: 600px; margin: 2rem auto;">
    <div class="card">
        <div class="card-header">
            <h1>Create New Binder</h1>
            <p>Start organizing your documents in a digital binder</p>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" id="createBinderForm">
                <div class="form-group">
                    <label for="title" class="form-label">Binder Title *</label>
                    <input type="text" id="title" name="title" class="form-input" 
                           value="<?php echo htmlspecialchars($title ?? ''); ?>" 
                           placeholder="e.g., Project Documentation, Meeting Notes, etc." required>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-input form-textarea" 
                              placeholder="Optional description of what this binder contains..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_public" value="1" 
                               <?php echo (isset($_POST['is_public']) || isset($is_public)) ? 'checked' : ''; ?>>
                        Make this binder public
                    </label>
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        Public binders can be viewed by anyone with the link
                    </small>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Binder
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('createBinderForm').addEventListener('submit', function(e) {
    if (!validateForm('createBinderForm')) {
        e.preventDefault();
    }
});
</script>

<?php include 'footer.php'; ?>
