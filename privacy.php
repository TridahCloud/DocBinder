<?php
$page_title = "Privacy Policy";
include 'header.php';
?>

<div class="container" style="max-width: 800px; margin: 2rem auto;">
    <div class="card">
        <div class="card-header">
            <h1>Privacy Policy</h1>
            <p style="color: var(--text-muted); margin: 0;">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>
        
        <div class="card-body">
            <h2>1. Information We Collect</h2>
            <p>DocBinder collects minimal information necessary to provide our service:</p>
            <ul>
                <li><strong>Account Information:</strong> Username, email address, and password (hashed)</li>
                <li><strong>Content:</strong> Documents, binders, and text content you create</li>
                <li><strong>Usage Data:</strong> Basic analytics to improve our service</li>
                <li><strong>Technical Data:</strong> IP address, browser type, and device information</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>We use your information solely to:</p>
            <ul>
                <li>Provide and maintain the DocBinder service</li>
                <li>Authenticate your account and ensure security</li>
                <li>Enable document sharing functionality</li>
                <li>Improve our service through anonymous usage analytics</li>
                <li>Communicate with you about your account or service updates</li>
            </ul>

            <h2>3. Information Sharing</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share information only in these limited circumstances:</p>
            <ul>
                <li><strong>With Your Consent:</strong> When you explicitly share documents with others</li>
                <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                <li><strong>Service Providers:</strong> With trusted third parties who help us operate our service (under strict confidentiality agreements)</li>
            </ul>

            <h2>4. Data Security</h2>
            <p>We implement appropriate security measures to protect your information:</p>
            <ul>
                <li>Passwords are hashed using industry-standard encryption</li>
                <li>All data transmission is encrypted using HTTPS</li>
                <li>Regular security audits and updates</li>
                <li>Access controls and authentication systems</li>
            </ul>

            <h2>5. Data Retention</h2>
            <p>We retain your information for as long as your account is active or as needed to provide our service. You can delete your account and all associated data at any time.</p>

            <h2>6. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Correct inaccurate information</li>
                <li>Delete your account and data</li>
                <li>Export your documents</li>
                <li>Opt out of non-essential communications</li>
            </ul>

            <h2>7. Cookies and Tracking</h2>
            <p>We use minimal cookies for:</p>
            <ul>
                <li>Session management and authentication</li>
                <li>Theme preferences (light/dark mode)</li>
                <li>Basic analytics to improve our service</li>
            </ul>
            <p>You can disable cookies in your browser, though this may affect some functionality.</p>

            <h2>8. Third-Party Services</h2>
            <p>DocBinder may integrate with third-party services for:</p>
            <ul>
                <li>Font loading (Google Fonts)</li>
                <li>Icon libraries (Font Awesome)</li>
                <li>Analytics (if enabled)</li>
            </ul>
            <p>These services have their own privacy policies.</p>

            <h2>9. International Transfers</h2>
            <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for such transfers.</p>

            <h2>10. Children's Privacy</h2>
            <p>DocBinder is not intended for children under 13. We do not knowingly collect personal information from children under 13.</p>

            <h2>11. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last updated" date.</p>

            <h2>12. Contact Us</h2>
            <p>If you have any questions about this Privacy Policy, please contact us:</p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:team@tridah.cloud">team@tridah.cloud</a></li>
                <li><strong>Website:</strong> <a href="https://tridah.cloud" target="_blank">tridah.cloud</a></li>
                <li><strong>GitHub:</strong> <a href="https://github.com/TridahCloud" target="_blank">github.com/TridahCloud</a></li>
            </ul>

            <div style="margin-top: 2rem; padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius-md); border-left: 4px solid var(--primary-color);">
                <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">
                    <strong>Note:</strong> This privacy policy applies to DocBinder, a free and open-source application developed by Tridah, a non-profit organization committed to user privacy and data protection.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
