<?php
// profile.php - User profile management
require_once 'config.php';

$page_title = "Profile";

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

include 'header.php';

$current_user = get_current_user_data();
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }
    
    // Validate names
    if (!empty($first_name) && strlen($first_name) > 50) {
        $errors[] = 'First name must be less than 50 characters';
    }
    if (!empty($last_name) && strlen($last_name) > 50) {
        $errors[] = 'Last name must be less than 50 characters';
    }
    
    // Check if username or email already exists (excluding current user)
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $current_user['id']]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already exists';
        }
    } catch (PDOException $e) {
        $errors[] = 'Database error occurred';
    }
    
    // Handle password change
    $password_changed = false;
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to change password';
        } elseif (!password_verify($current_password, $current_user['password_hash'])) {
            $errors[] = 'Current password is incorrect';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters long';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New password and confirmation do not match';
        } else {
            $password_changed = true;
        }
    }
    
    // Update user if no errors
    if (empty($errors)) {
        try {
            if ($password_changed) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, first_name = ?, last_name = ?, password_hash = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $username, 
                    $email, 
                    $first_name ?: null, 
                    $last_name ?: null, 
                    password_hash($new_password, PASSWORD_DEFAULT),
                    $current_user['id']
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, first_name = ?, last_name = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $username, 
                    $email, 
                    $first_name ?: null, 
                    $last_name ?: null,
                    $current_user['id']
                ]);
            }
            
            $success_message = 'Profile updated successfully!';
            
            // Refresh user data
            $current_user = get_current_user_data();
            
        } catch (PDOException $e) {
            $error_message = 'Failed to update profile. Please try again.';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>

<div class="container" style="max-width: 600px; margin: 2rem auto;">
    <div class="card">
        <div class="card-header">
            <h2>Profile Settings</h2>
            <p style="color: var(--text-muted); margin: 0;">Manage your account information</p>
        </div>
        
        <div class="card-body">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" id="username" name="username" class="form-input" 
                           value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" 
                           value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" 
                           value="<?php echo htmlspecialchars($current_user['last_name'] ?? ''); ?>">
                </div>
                
                <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border-color);">
                
                <h3 style="margin-bottom: 1rem;">Change Password</h3>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Leave password fields blank to keep your current password.
                </p>
                
                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Account Information -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>Account Information</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <strong>Member Since:</strong><br>
                    <span style="color: var(--text-muted);">
                        <?php echo date('F j, Y', strtotime($current_user['created_at'])); ?>
                    </span>
                </div>
                <div>
                    <strong>Last Updated:</strong><br>
                    <span style="color: var(--text-muted);">
                        <?php echo date('F j, Y', strtotime($current_user['updated_at'])); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const currentPassword = document.getElementById('current_password').value;
    
    // If trying to change password, current password is required
    if (newPassword && !currentPassword) {
        e.preventDefault();
        alert('Current password is required to change password');
        document.getElementById('current_password').focus();
        return;
    }
    
    // Check password match
    if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirmation do not match');
        document.getElementById('confirm_password').focus();
        return;
    }
});
</script>

<?php include 'footer.php'; ?>
