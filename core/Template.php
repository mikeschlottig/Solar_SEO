<?php
// core/Template.php
class Template {
    private $blocks = [];
    private $templatePath;
    private $activeBlockName = null; // To capture block content with ob_start/end

    public function __construct($templatePath) {
        // Ensure templatePath ends with a slash
        $this->templatePath = rtrim($templatePath, '/') . '/';
    }

    // Render a template
    public function render($template, $vars = []) {
        // Ensure template name has .php extension if not provided
        if (substr($template, -4) !== '.php') {
            $template .= '.php';
        }

        $templateFile = $this->templatePath . $template;

        if (!file_exists($templateFile)) {
            // A more robust system might throw an exception or log an error
            // For now, returning an error message or empty string.
            // Consider a fallback to a default theme or a specific error template.
            error_log("Template file not found: " . $templateFile);
            return "Error: Template '$template' not found.";
        }

        extract($vars);

        ob_start();
        include $templateFile;
        $content = ob_get_clean();

        // If this template extends another, render the base template
        if (isset($this->blocks['extends'])) {
            $baseTemplateName = $this->blocks['extends'];
            unset($this->blocks['extends']); // Avoid infinite loops

            // The content of the current template becomes the 'content' block for the base template,
            // unless specific blocks were defined that override parts of this $content.
            // This is a common pattern: child defines blocks, base yields them.
            // The `$content` here is the fully rendered child.
            // We need to pass the *child's blocks* to the parent.
            // So, the child template should have defined blocks using startBlock/endBlock.
            // The $content variable itself is implicitly the main content area if not using blocks.

            // A common way is to make $content available as a default {{ content }} or $content variable in base.
            // Or, the child explicitly defines a block like {% block content %} child stuff {% endblock %}
            // and the base does {% yield content %}.
            // The current block() method stores content directly.
            // Let's assume `render` is called on the "outermost" child template first.
            // This child's `extends()` call will trigger rendering of the base.
            // The base template will then call `block()` or `section()` to get content from the child.

            // Store the rendered content of the child template to be accessible by the parent,
            // typically via a default block name like 'content' if no other blocks are defined.
            if (!isset($this->blocks['content'])) {
                 $this->blocks['content_implicit'] = $content; // Make child's full render available
            }
            return $this->render($baseTemplateName, $vars); // Pass original vars, base accesses child blocks
        }

        return $content;
    }

    // Set the template to extend
    public function extends($template) {
        $this->blocks['extends'] = $template;
    }

    // Start defining a block's content
    public function startBlock($name) {
        $this->activeBlockName = $name;
        ob_start();
    }

    // End defining a block's content
    public function endBlock() {
        if ($this->activeBlockName === null) {
            // Trigger error or handle gracefully if endBlock is called without startBlock
            error_log("endBlock() called without startBlock()");
            if (ob_get_level() > 0) ob_end_flush(); // Try to clean up
            return;
        }
        $this->blocks[$this->activeBlockName] = ob_get_clean();
        $this->activeBlockName = null;
    }

    // Get a block's content (used in the template being extended)
    public function getBlock($name, $defaultContent = '') {
        if (isset($this->blocks[$name])) {
            return $this->blocks[$name];
        }
        // Specific handling for the implicit full content of a child template
        if ($name === 'content' && isset($this->blocks['content_implicit'])) {
            return $this->blocks['content_implicit'];
        }
        return $defaultContent;
    }

    // Alias for getBlock, as used in one of the example templates
    public function section($name, $defaultContent = '') {
        return $this->getBlock($name, $defaultContent);
    }

    // Include a partial template
    public function include($template, $vars = []) {
        // Create a new Template instance for the partial to keep block contexts separate
        // Or, ensure that partials don't use extends/block in a way that conflicts.
        // For simplicity, let's assume partials are self-contained or use the same $this context carefully.
        // A safer way is $partialTemplate = new self($this->templatePath); echo $partialTemplate->render($template, $vars);

        // Ensure template name has .php extension if not provided
        if (substr($template, -4) !== '.php') {
            $template .= '.php';
        }
        $partialFile = $this->templatePath . $template;

        if (!file_exists($partialFile)) {
            error_log("Partial template file not found: " . $partialFile);
            return "Error: Partial '$template' not found.";
        }

        extract($vars); // Extract variables for the partial
        include $partialFile; // Include directly in the current output buffer context
    }

    // Output a variable with HTML escaping
    public function escape($var, $double_encode = true) {
        if (is_array($var) || is_object($var)) {
            // Decide how to handle arrays/objects, e.g., json_encode or recursive escape
            // For now, just return a string indicating it's a complex type.
            return '[complex type]';
        }
        return htmlspecialchars((string)$var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $double_encode);
    }

    // Kept the old block method for compatibility with one of the examples,
    // but startBlock/endBlock/getBlock is preferred for Twig-like inheritance.
    // This method is problematic for inheritance as it directly sets or gets.
    public function block($name, $content = null) {
        if ($content === null) {
            // Get block content
            return $this->getBlock($name);
        } else {
            // Set block content (directly)
            $this->blocks[$name] = $content;
        }
    }
}
?>
