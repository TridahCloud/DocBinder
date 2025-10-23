<?php
// header.php - Common header for all pages
require_once 'config.php';

$current_user = get_current_user_data();
$is_logged_in = is_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="tridah icon.png">
</head>
<body class="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <img src="tridah icon.png" alt="Tridah Logo" class="nav-logo">
                <span class="nav-title"><?php echo APP_NAME; ?></span>
            </div>
            
            <div class="nav-menu">
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="create-binder.php" class="nav-link">
                        <i class="fas fa-plus"></i> New Binder
                    </a>
                    <div class="nav-dropdown">
                        <button class="nav-link dropdown-toggle">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($current_user['username']); ?>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> Profile
                            </a>
                            <hr class="dropdown-divider">
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link nav-link-primary">Sign Up</a>
                <?php endif; ?>
                
                <button class="theme-toggle">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
            </div>
            
            <button class="nav-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>
    
    <main class="main-content">
