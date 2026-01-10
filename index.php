<?php
// index.php

// Attempt to load configuration
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    // Define essential constants if config.php is missing or paths need adjustment
    if (!defined('ROOT_PATH')) define('ROOT_PATH', __DIR__);
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false); // Default to production if no config
    // Setup basic error reporting if not set by config
    if (DEBUG_MODE) {
        ini_set('display_errors', '1'); error_reporting(E_ALL);
    } else {
        ini_set('display_errors', '0'); error_reporting(0);
    }
}

// Autoload core classes (simple autoloader for this example)
// A more robust solution would use Composer's autoloader.
spl_autoload_register(function ($class_name) {
    $file = ROOT_PATH . '/core/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize components
// Use default paths from config or pass specific paths
$content_dir = defined('CONTENT_DIR_DEFAULT') ? CONTENT_DIR_DEFAULT : ROOT_PATH . '/content/';
$themes_dir = defined('THEMES_DIR_DEFAULT') ? THEMES_DIR_DEFAULT : ROOT_PATH . '/themes/';
$cache_dir = defined('CACHE_DIR_DEFAULT') ? CACHE_DIR_DEFAULT : ROOT_PATH . '/cache/';
// Default theme name - could also come from a settings file or DB in a more complex CMS
$default_theme_name = defined('DEFAULT_THEME') ? DEFAULT_THEME : 'default';


try {
    $contentManager = new Content($content_dir);
    // Template path should point to the active theme's template directory
    // For now, hardcoding 'default' theme. A theme system would make this dynamic.
    $templateManager = new Template($themes_dir . $default_theme_name . '/templates/');
    $cacheManager = new Cache($cache_dir, !DEBUG_MODE); // Enable cache when not in debug mode

    // Get current page slug from URL (e.g., /index.php?page=about/contact)
    // A more robust router would handle clean URLs (e.g. /about/contact) via .htaccess and parse REQUEST_URI
    $slug = isset($_GET['page']) ? trim($_GET['page'], '/') : 'home';
    if (empty($slug)) $slug = 'home';


    // Try to get cached page
    $cacheKey = "page:" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $slug);
    $output = $cacheManager->get($cacheKey);

    if ($output === null || DEBUG_MODE) { // Re-render if cache miss or in debug mode
        $pageData = $contentManager->getPage($slug);

        if (!$pageData) {
            // Page not found - show 404
            http_response_code(404); // Set HTTP status code
            // Prepare data for a 404 page. The template system should handle this.
            // You might have a specific 404.md or use a default 404 template.
            $pageData = [
                'title' => '404 - Page Not Found',
                'content' => "# 404 - Page Not Found\n\nThe page you requested could not be found.",
                'frontmatter' => ['title' => '404 - Page Not Found', 'description' => 'Page not found'],
                'slug' => '404' // Special slug for 404
            ];
            // Attempt to render a specific 404 template if available
            $template_to_render = '404.html.php';
            // Check if 404.html.php exists, otherwise fallback to page.html.php
            // This logic should ideally be in the template manager or a router.
            $theme_template_path = $themes_dir . $default_theme_name . '/templates/';
            if (!file_exists($theme_template_path . $template_to_render)) {
                $template_to_render = 'page.html.php'; // Fallback to default page template
            }

        } else {
            $template_to_render = 'page.html.php'; // Default template for pages
            // Potentially override template based on frontmatter: $pageData['frontmatter']['template'] ?? 'page.html.php';
        }

        // Process image if specified in frontmatter
        $imageHtml = '';
        if (isset($pageData['frontmatter']['image'])) {
            $imagePath = ROOT_PATH . '/' . ltrim($pageData['frontmatter']['image'], '/'); // Assuming image path is relative to root
            if (file_exists($imagePath)) {
                try {
                    $image = new Image($imagePath);
                    // Example: Resize image if dimensions are specified in frontmatter
                    if (isset($pageData['frontmatter']['image_width']) && isset($pageData['frontmatter']['image_height'])) {
                        $img_w = (int)$pageData['frontmatter']['image_width'];
                        $img_h = (int)$pageData['frontmatter']['image_height'];
                        if ($img_w > 0 && $img_h > 0) {
                           $image->resize($img_w, $img_h);
                        }
                    } elseif (isset($pageData['frontmatter']['image_resize'])) { // e.g. image_resize: "800x600"
                        list($w, $h) = explode('x', $pageData['frontmatter']['image_resize']);
                        if ((int)$w > 0 && (int)$h > 0) $image->resize((int)$w, (int)$h);
                    }
                    $imageHtml = '<div class="page-image">' .
                                 '<img src="' . $image->dataUrl() . '" alt="' .
                                 htmlspecialchars($pageData['title'] ?? '') . '"></div>';
                } catch (Exception $e) {
                    if (DEBUG_MODE) {
                        $imageHtml = "<p style='color:red;'>Image processing failed: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                    error_log("Image processing error for {$imagePath}: " . $e->getMessage());
                }
            } else {
                 if (DEBUG_MODE) {
                    $imageHtml = "<p style='color:orange;'>Image not found: " . htmlspecialchars($imagePath) . "</p>";
                }
            }
        }

        // Render template with page data
        // The 'content' key for $pageData is the markdown body. Template needs to parse it if desired (e.g. with Parsedown)
        // For now, the template example uses $template->escape($content), which will output raw markdown.
        // A real CMS would convert markdown to HTML here or in the template.
        // Let's assume $pageData['content'] should be HTML for the template.
        // We'll need a Markdown parser. For simplicity, I'll just pass it as is.
        // If Parsedown was available:
        // $parsedown = new Parsedown();
        // $htmlContent = $parsedown->text($pageData['content']);

        $templateVars = [
            'page' => $pageData, // Contains title, slug, frontmatter, raw_markdown_content
            'content_markdown' => $pageData['content'], // Explicitly pass markdown
            'image_html' => $imageHtml, // Pass pre-rendered image HTML
            'site_name' => defined('SITE_NAME') ? SITE_NAME : 'My CMS',
            // Make Template object available to templates if they need to call $template->escape(), etc.
            // This is often done by passing $templateManager itself or by making methods static/global.
            // For simplicity, the template files in the example instantiate their own Template object, which is not ideal.
            // A better way: pass $templateManager to be used by the templates.
            'tpl' => $templateManager // So templates can do $tpl->escape(...)
        ];

        $output = $templateManager->render($template_to_render, $templateVars);

        // Cache the output if not in debug mode
        if (!$cacheManager->isEnabled() || !DEBUG_MODE) { // Corrected logic: cache if enabled AND not in debug
             $cacheManager->set($cacheKey, $output);
        }
    }

    // Output the page
    echo $output;

} catch (Exception $e) {
    // Global error handler
    if (DEBUG_MODE) {
        echo "<h1>An Error Occurred</h1>";
        echo "<pre>";
        echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n";
        echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")\n\n";
        echo "<strong>Stack Trace:</strong>\n" . htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Internal Server Error</h1><p>We are sorry, but something went wrong. Please try again later.</p>";
    }
    error_log("CMS Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}

?>
