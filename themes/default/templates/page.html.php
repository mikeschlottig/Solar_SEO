<?php
/**
 * Default page template.
 * Extends base.html.php
 *
 * Available variables:
 * $page: Array containing page data (title, slug, frontmatter, etc.)
 * $content_markdown: The raw Markdown content of the page.
 * $image_html: Pre-rendered HTML for the page image, if any.
 * $site_name: The name of the site.
 * $tpl: The Template class instance, for using methods like $tpl->escape(), $tpl->startBlock(), etc.
 */

$tpl->extends('base.html.php'); // Tell the template engine this extends base.html.php

// --- Define additional content for the <head> ---
$tpl->startBlock('head_extra');
?>
    <link rel="stylesheet" href="/themes/default/assets/css/page.css">
    <?php if (isset($page['frontmatter']['custom_css'])): ?>
        <link rel="stylesheet" href="<?php echo $tpl->escape($page['frontmatter']['custom_css']); ?>">
    <?php endif; ?>
<?php
$tpl->endBlock();


// --- Define the main content for the page ---
$tpl->startBlock('content_main');
?>
    <article class="page-<?php echo $tpl->escape($page['slug'] ?? 'default'); ?>">
        <header class="page-header">
            <h1><?php echo $tpl->escape($page['title'] ?? 'Untitled Page'); ?></h1>
            <?php if (isset($page['frontmatter']['date'])): ?>
                <p class="page-meta">Published on: <time datetime="<?php echo $tpl->escape(date('Y-m-d', strtotime($page['frontmatter']['date']))); ?>"><?php echo $tpl->escape(date('F j, Y', strtotime($page['frontmatter']['date']))); ?></time></p>
            <?php endif; ?>
        </header>

        <?php
        // Render the pre-processed image HTML if it exists
        if (!empty($image_html)) {
            echo $image_html; // $image_html is already processed and contains <img> tag
        }
        ?>

        <div class="page-content-body">
            <?php
            // $content_markdown contains the raw Markdown.
            // For actual display, this should be parsed to HTML.
            // Using a library like Parsedown is recommended.
            // Example:
            // if (class_exists('Parsedown')) {
            //    $parsedown = new Parsedown();
            //    echo $parsedown->text($content_markdown);
            // } else {
            //    echo nl2br($tpl->escape($content_markdown)); // Basic fallback: escape and show with line breaks
            // }
            // For now, just escaping and using nl2br as a placeholder for Markdown parsing.
            echo nl2br($tpl->escape($content_markdown ?? 'No content available.'));
            ?>
        </div>
    </article>
<?php
$tpl->endBlock();


// --- Define additional content for the footer (optional) ---
$tpl->startBlock('footer_extra');
?>
    <p>This page was last updated: <?php echo $tpl->escape($page['frontmatter']['last_updated'] ?? 'N/A'); ?></p>
<?php
$tpl->endBlock();

// --- Define additional scripts (optional) ---
$tpl->startBlock('scripts_extra');
?>
    <?php if (isset($page['frontmatter']['custom_js'])): ?>
        <script src="<?php echo $tpl->escape($page['frontmatter']['custom_js']); ?>"></script>
    <?php endif; ?>
<?php
$tpl->endBlock();

// Note: The actual rendering of this file and its extension of base.html.php
// is handled by the Template::render() method called from index.php.
// index.php calls $templateManager->render('page.html.php', $templateVars);
// The Template class sees $tpl->extends() and then re-renders base.html.php,
// making the blocks defined here available to the base template.
?>
