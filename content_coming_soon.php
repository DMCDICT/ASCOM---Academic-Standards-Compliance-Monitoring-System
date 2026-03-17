<?php
// content_coming_soon.php
// A reusable "Content coming soon..." page for sections that are under development

// Get the section name from URL parameter or default
$section_name = $_GET['section'] ?? 'this section';
$page_name = $_GET['page'] ?? 'this page';

// If accessed directly (not via include), show full page
if (!defined('INCLUDED_FROM_CONTENT')) {
    // Capitalize the first letter for better presentation
    $section_name = ucfirst($section_name);
    $page_name = ucfirst($page_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $section_name; ?> - Content Coming Soon</title>
    <style>
        @font-face {
            font-family: 'TT Interphases';
            src: url('../src/assets/fonts/tt-interphases/TT Interphases Pro Trial Regular.ttf') format('truetype');
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'TT Interphases', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .coming-soon-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .section-name {
            color: #4a5568;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .page-name {
            color: #718096;
            font-size: 1.4rem;
            margin-bottom: 30px;
        }
        
        .description {
            font-size: 1.1rem;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .feature {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }
        
        .feature-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .feature-desc {
            font-size: 0.9rem;
            color: #718096;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            animation: progress 2s ease-in-out infinite;
        }
        
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        
        .status {
            font-size: 1rem;
            color: #4a5568;
            font-weight: 500;
        }
        
        .back-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .construction-animation {
            display: inline-block;
            animation: construction 2s ease-in-out infinite;
        }
        
        @keyframes construction {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(5deg); }
            75% { transform: rotate(-5deg); }
        }
        
        @media (max-width: 768px) {
            .coming-soon-container {
                padding: 40px 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .section-name {
                font-size: 1.5rem;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="coming-soon-container">
        <div class="icon construction-animation">🚧</div>
        
        <h1>Content Coming Soon</h1>
        <div class="section-name"><?php echo htmlspecialchars($section_name); ?></div>
        <div class="page-name"><?php echo htmlspecialchars($page_name); ?></div>
        
        <p class="description">
            We're working hard to bring you amazing features and functionality. 
            This section is currently under development and will be available soon.
        </p>
        
        <div class="features">
            <div class="feature">
                <span class="feature-icon">⚡</span>
                <div class="feature-title">Fast & Efficient</div>
                <div class="feature-desc">Optimized for performance</div>
            </div>
            <div class="feature">
                <span class="feature-icon">🎨</span>
                <div class="feature-title">Modern Design</div>
                <div class="feature-desc">Beautiful user interface</div>
            </div>
            <div class="feature">
                <span class="feature-icon">🔒</span>
                <div class="feature-title">Secure</div>
                <div class="feature-desc">Protected and safe</div>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <div class="status">Development in Progress</div>
        
        <a href="javascript:history.back()" class="back-button">
            ← Go Back
        </a>
    </div>
</body>
</html>
<?php
} else {
    // If included from content.php, show just the content wrapper
    $section_name = ucfirst($section_name);
    $page_name = ucfirst($page_name);
?>
<div class="content-wrapper">
    <div class="coming-soon-container" style="margin: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">
        <div class="icon construction-animation" style="font-size: 60px; margin-bottom: 20px; display: inline-block; animation: construction 2s ease-in-out infinite;">🚧</div>
        
        <h1 style="font-size: 2rem; font-weight: bold; color: #2d3748; margin-bottom: 15px; line-height: 1.2;">Content Coming Soon</h1>
        <div class="section-name" style="color: #4a5568; font-size: 1.5rem; font-weight: 600; margin-bottom: 10px;"><?php echo htmlspecialchars($section_name); ?></div>
        <div class="page-name" style="color: #718096; font-size: 1.2rem; margin-bottom: 20px;"><?php echo htmlspecialchars($page_name); ?></div>
        
        <p class="description" style="font-size: 1rem; color: #4a5568; line-height: 1.6; margin-bottom: 30px; max-width: 500px; margin-left: auto; margin-right: auto;">
            We're working hard to bring you amazing features and functionality. 
            This section is currently under development and will be available soon.
        </p>
        
        <div class="features" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div class="feature" style="background: rgba(255, 255, 255, 0.8); padding: 15px; border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.1);">
                <span class="feature-icon" style="font-size: 20px; margin-bottom: 8px; display: block;">⚡</span>
                <div class="feature-title" style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Fast & Efficient</div>
                <div class="feature-desc" style="font-size: 0.8rem; color: #718096;">Optimized for performance</div>
            </div>
            <div class="feature" style="background: rgba(255, 255, 255, 0.8); padding: 15px; border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.1);">
                <span class="feature-icon" style="font-size: 20px; margin-bottom: 8px; display: block;">🎨</span>
                <div class="feature-title" style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Modern Design</div>
                <div class="feature-desc" style="font-size: 0.8rem; color: #718096;">Beautiful user interface</div>
            </div>
            <div class="feature" style="background: rgba(255, 255, 255, 0.8); padding: 15px; border-radius: 12px; border: 1px solid rgba(0, 0, 0, 0.1);">
                <span class="feature-icon" style="font-size: 20px; margin-bottom: 8px; display: block;">🔒</span>
                <div class="feature-title" style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Secure</div>
                <div class="feature-desc" style="font-size: 0.8rem; color: #718096;">Protected and safe</div>
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
<?php
}
?> 