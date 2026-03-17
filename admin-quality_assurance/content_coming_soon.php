<?php
// admin-quality_assurance/content_coming_soon.php
// Content coming soon page for admin-qa section

// Get the page name from URL parameter or default
$page_name = $_GET['page'] ?? 'this page';
$section_name = 'Admin QA';

// Capitalize the first letter for better presentation
$page_name = ucfirst($page_name);
$section_name = ucfirst($section_name);
?>

<div class="content-wrapper">
    <div class="coming-soon-container" style="margin: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">
        <div class="icon construction-animation" style="font-size: 60px; margin-bottom: 20px; display: inline-block; animation: construction 2s ease-in-out infinite;">🚧</div>
        
        <h1 style="font-size: 2rem; font-weight: bold; color: #2d3748; margin-bottom: 15px; line-height: 1.2;">Content Coming Soon</h1>
        <div class="section-name" style="color: #4a5568; font-size: 1.5rem; font-weight: 600; margin-bottom: 10px;"><?php echo htmlspecialchars($section_name); ?></div>
        <div class="page-name" style="color: #718096; font-size: 1.2rem; margin-bottom: 20px;"><?php echo htmlspecialchars($page_name); ?></div>
        
        <p class="description" style="font-size: 1rem; color: #4a5568; line-height: 1.6; margin-bottom: 30px; max-width: 500px; margin-left: auto; margin-right: auto;">
            We're working hard to bring you amazing quality assurance and compliance monitoring features and functionality. 
            This section is currently under development and will be available soon.
        </p>
        
        <div class="features" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div class="feature" style="background: rgba(255, 255, 255, 0.8); padding: 15px; border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.1);">
                <span class="feature-icon" style="font-size: 20px; margin-bottom: 8px; display: block;">🔍</span>
                <div class="feature-title" style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Quality Monitoring</div>
                <div class="feature-desc" style="font-size: 0.8rem; color: #718096;">Monitor academic quality</div>
            </div>
            <div class="feature" style="background: rgba(255, 255, 255, 0.8); padding: 15px; border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.1);">
                <span class="feature-icon" style="font-size: 20px; margin-bottom: 8px; display: block;">📋</span>
                <div class="feature-title" style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Compliance Reports</div>
                <div class="feature-desc" style="font-size: 0.8rem; color: #718096;">Generate compliance reports</div>
            </div>
            <div class="feature" style="background: rgba(255, 255, 255, 0.8); padding: 15px; border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.1);">
                <span class="feature-icon" style="font-size: 20px; margin-bottom: 8px; display: block;">📊</span>
                <div class="feature-title" style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Analytics</div>
                <div class="feature-desc" style="font-size: 0.8rem; color: #718096;">Quality analytics</div>
            </div>
        </div>
        
        <div class="progress-bar" style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 15px;">
            <div class="progress-fill" style="height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); border-radius: 4px; animation: progress 2s ease-in-out infinite;"></div>
        </div>
        
        <div class="status" style="font-size: 0.9rem; color: #4a5568; font-weight: 500;">Development in Progress</div>
    </div>
</div>

<style>
@keyframes construction {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(5deg); }
    75% { transform: rotate(-5deg); }
}

@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}
</style> 