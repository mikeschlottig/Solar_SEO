---
title: Welcome to Our CMS!
description: This is the homepage of our new flat-file CMS.
date: 2023-10-26
image: /uploads/sample-image.jpg # Example image path, ensure this exists or change it
image_width: 800 # Optional: specify width for Image class
image_height: 400 # Optional: specify height for Image class
custom_css: /themes/default/assets/css/home-custom.css # Example of page-specific CSS
show_about_link: true
lang: en-US
---

# Hello World!

This is the **homepage** of your new flat-file Content Management System.

## Features

*   **Easy to Use**: Write content in Markdown.
*   **Lightweight**: No database required!
*   **Customizable**: Themes and templates.

## Getting Started

1.  Explore the `content/` directory to see how pages are structured.
2.  Modify this page (`content/Home/default.md`) to change the homepage.
3.  Create new pages by adding new folders and `default.md` files. For example, create `content/About/default.md` for an "About Us" page.
    The URL will then be `/index.php?page=about`.

### Example List

-   Item one
-   Item two
    -   Sub-item A
    -   Sub-item B

### Example Code Block

```php
<?php
// This is a PHP code block
echo "Hello from PHP!";
?>
```

Feel free to edit this content and explore the capabilities of the CMS.
You can add more Markdown content here. For example, an image from your uploads (if it exists):

If you added an image to `uploads/another-sample.png`:
<!-- ![Sample Image Alt Text](/uploads/another-sample.png) -->
Remember to adjust image paths in the frontmatter or content as needed. The `image` frontmatter variable is currently processed by `index.php` to display an image at the top. Markdown images like the one above would be rendered if your Markdown parser supports them and paths are correct.
