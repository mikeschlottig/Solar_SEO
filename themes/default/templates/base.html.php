<?php
/**
 * Base template file.
 *
 * Available variables:
 * $page: Array containing page data (title, slug, frontmatter, etc.)
 * $content_markdown: The raw Markdown content of the page.
 * $image_html: Pre-rendered HTML for the page image, if any.
 * $site_name: The name of the site.
 * $tpl: The Template class instance, for using methods like $tpl->escape(), $tpl->section(), etc.
 */
?>
<!DOCTYPE html>
<html lang="<?php echo $tpl->escape($page['frontmatter']['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tpl->escape($page['title'] ?? $site_name); ?> - <?php echo $tpl->escape($site_name); ?></title>
    <meta name="description" content="<?php echo $tpl->escape($page['frontmatter']['description'] ?? ''); ?>">

    <!-- Default CSS (can be conditional or managed by an asset pipeline) -->
    <link rel="stylesheet" href="/themes/default/assets/css/style.css">

    <?php echo $tpl->section('head_extra'); // For additional head content from child templates ?>
</head>
<body>
    <header id="site-header">
        <div class="container">
            <h1><a href="/"><?php echo $tpl->escape($site_name); ?></a></h1>
            <nav id="main-nav">
                <ul>
                    <li><a href="/index.php?page=home">Home</a></li>
                    <!-- Add more static navigation items or generate dynamically -->
                    <?php if(isset($page['frontmatter']['show_about_link']) && $page['frontmatter']['show_about_link']): ?>
                        <li><a href="/index.php?page=about">About</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main id="site-content">
        <div class="container">
            <?php
                // This will render the main content block defined by child templates (like page.html.php)
                // or the implicit content if the child doesn't use blocks.
                // The index.php passes $pageData and $image_html to the child (e.g. page.html.php),
                // which then sets up its blocks. The base template then renders these blocks.
                echo $tpl->section('content_main', '<!-- Default content if no block is provided -->');
            ?>
        </div>
    </main>

    <footer id="site-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $tpl->escape($site_name); ?>. All rights reserved.</p>
            <?php echo $tpl->section('footer_extra'); // For additional footer content ?>
        </div>
    </footer>

    <?php echo $tpl->section('scripts_extra'); // For additional scripts at the end of the body ?>
</body>
</html>
