<?php
$page_title = "Home";
include 'header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Organize Your Documents Digitally</h1>
        <p>DocBinder by Tridah - Create, manage, and share digital binders with ease. Transform your paper documents into organized, searchable digital collections.</p>
        <div class="hero-actions">
            <?php if ($is_logged_in): ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-th-large"></i> Go to Dashboard
                </a>
                <a href="create-binder.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-plus"></i> Create New Binder
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Get Started Free
                </a>
                <a href="login.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" style="padding: 4rem 0; background: var(--bg-secondary);">
    <div class="container">
        <div class="text-center mb-8">
            <h2>Why Choose DocBinder?</h2>
            <p>Built for modern document management with privacy and simplicity in mind</p>
        </div>
        
        <div class="grid grid-cols-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>Digital Binders</h3>
                    <p>Create organized digital binders for your documents. Upload PDFs, images, or write text directly in our lightweight editor.</p>
                </div>
            </div>
            
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3>Easy Sharing</h3>
                    <p>Share your binders with colleagues, friends, or make them public. Control access with secure sharing links.</p>
                </div>
            </div>
            
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Responsive Design</h3>
                    <p>Access your documents anywhere with our responsive design. Works perfectly on desktop, tablet, and mobile devices.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works" style="padding: 4rem 0;">
    <div class="container">
        <div class="text-center mb-8">
            <h2>How It Works</h2>
            <p>Get started in minutes with our simple three-step process</p>
        </div>
        
        <div class="grid grid-cols-3">
            <div class="text-center">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem; font-weight: bold;">1</div>
                <h3>Sign Up</h3>
                <p>Create your free account in seconds. No credit card required.</p>
            </div>
            
            <div class="text-center">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem; font-weight: bold;">2</div>
                <h3>Create Binders</h3>
                <p>Upload documents or write content directly. Organize everything in digital binders.</p>
            </div>
            
            <div class="text-center">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem; font-weight: bold;">3</div>
                <h3>Share & Collaborate</h3>
                <p>Share your binders with others or keep them private. View and edit from anywhere.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Tridah Section -->
<section class="about-tridah" style="padding: 4rem 0; background: var(--bg-secondary);">
    <div class="container">
        <div class="grid grid-cols-2" style="align-items: center;">
            <div>
                <h2>Built by Tridah</h2>
                <p>DocBinder is developed by Tridah, a non-profit organization committed to creating free, open-source software that empowers individuals and organizations.</p>
                <p>Our mission is to provide high-quality digital tools without the barriers of proprietary software. DocBinder is released under the MIT license, ensuring it remains free and open for everyone.</p>
                <div style="margin-top: 2rem;">
                    <a href="https://tridah.cloud" target="_blank" class="btn btn-outline">
                        <i class="fas fa-globe"></i> Visit Tridah
                    </a>
                    <a href="https://github.com/TridahCloud" target="_blank" class="btn btn-outline">
                        <i class="fab fa-github"></i> View on GitHub
                    </a>
                </div>
            </div>
            <div class="text-center">
                <img src="tridah icon.png" alt="Tridah Logo" style="max-width: 200px; border-radius: var(--radius-lg);">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta" style="padding: 4rem 0;">
    <div class="container text-center">
        <h2>Ready to Get Started?</h2>
        <p>Join thousands of users who have already organized their documents with DocBinder</p>
        <?php if (!$is_logged_in): ?>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-rocket"></i> Start Your Free Account
            </a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary btn-lg">
                <i class="fas fa-th-large"></i> Go to Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
