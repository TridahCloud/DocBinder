// theme.js - Theme switching functionality

// Theme management
const ThemeManager = {
    init() {
        this.loadTheme();
        this.setupThemeToggle();
    },
    
    loadTheme() {
        // Check for saved theme in localStorage first, then cookie, then default to light
        const savedTheme = localStorage.getItem('theme') || 
                          (document.cookie.match(/theme=([^;]+)/) ? document.cookie.match(/theme=([^;]+)/)[1] : 'light');
        this.setTheme(savedTheme);
    },
    
    setTheme(theme) {
        document.body.className = theme;
        document.cookie = `theme=${theme}; path=/; max-age=31536000`; // 1 year
        localStorage.setItem('theme', theme);
        
        // Update theme icon
        const themeIcon = document.getElementById('theme-icon');
        if (themeIcon) {
            themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    },
    
    toggleTheme() {
        const currentTheme = document.body.className;
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    },
    
    setupThemeToggle() {
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }
};

// Global theme toggle function
function toggleTheme() {
    ThemeManager.toggleTheme();
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    ThemeManager.init();
});

// Listen for system theme changes
if (window.matchMedia) {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    mediaQuery.addEventListener('change', function(e) {
        // Only auto-switch if user hasn't manually set a preference
        if (!localStorage.getItem('theme')) {
            ThemeManager.setTheme(e.matches ? 'dark' : 'light');
        }
    });
}
