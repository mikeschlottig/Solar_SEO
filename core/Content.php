<?php
// core/Content.php
class Content {
    private $contentDir;

    public function __construct($contentDir = 'content/') {
        // Ensure contentDir path is relative to the project root if needed,
        // or ensure it's an absolute path. Assuming it's relative from where index.php is.
        // For consistency with ROOT_PATH in previous examples, one might define ROOT_PATH
        // in a config or index.php and pass it here, e.g., ROOT_PATH . '/' . $contentDir
        $this->contentDir = rtrim($contentDir, '/') . '/';

        // Create content directory if it doesn't exist
        if (!file_exists($this->contentDir)) {
            mkdir($this->contentDir, 0755, true);
        }
    }

    // Get page by slug
    public function getPage($slug) {
        $filePath = $this->contentDir . $this->slugToPath($slug) . '/default.md';

        if (!file_exists($filePath)) {
            return null;
        }

        // Read file contents
        $raw_content = file_get_contents($filePath);

        // Split frontmatter and content
        // Regex adjusted for robustness: handles optional BOM, CRNL/NL, and ensures frontmatter is at the start.
        if (preg_match('/^(?:\xEF\xBB\xBF)?---\r?\n(.*?)\r?\n---\r?\n(.*)/s', $raw_content, $matches)) {
            $frontmatter_yaml = $matches[1];
            $pageContent = $matches[2];
        } else {
            // No frontmatter found or incorrect format
            $frontmatter_yaml = '';
            $pageContent = $raw_content; // Treat all as content
        }

        $frontmatter = $this->parseYaml($frontmatter_yaml);

        return [
            'slug' => $slug,
            'title' => $frontmatter['title'] ?? 'Untitled Page', // More descriptive default
            'content' => $pageContent,
            'frontmatter' => $frontmatter
        ];
    }

    // Create new page
    public function createPage($slug, $title, $content, $frontmatter = []) {
        $path = $this->contentDir . $this->slugToPath($slug);

        // Create directory if it doesn't exist
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $filePath = $path . '/default.md';

        // Add/update title in frontmatter
        $frontmatter['title'] = $title;
        $yaml = $this->generateYaml($frontmatter);

        // Write to file
        $fileContent = "---\n" . rtrim($yaml) . "\n---\n" . ltrim($content);
        $result = file_put_contents($filePath, $fileContent);

        return $result !== false;
    }

    // Convert slug to filesystem path (e.g., 'about/team' -> 'About/Team')
    private function slugToPath($slug) {
        // Sanitize slug: remove leading/trailing slashes, remove relative path components
        $slug = trim($slug, '/');
        $slug = str_replace(['../', './'], '', $slug); // Basic security for path
        if (empty($slug)) return ''; // Avoid creating just the contentDir

        $parts = explode('/', $slug);
        // Capitalize each part of the path for directory names as in Grav
        // However, Grav typically uses lowercase for slugs and paths.
        // The provided example `ucfirst` each part. Sticking to that.
        return implode('/', array_map('ucfirst', $parts));
    }

    // Parse YAML frontmatter
    private function parseYaml($yaml_string) {
        $result = [];
        if (empty(trim($yaml_string))) {
            return $result;
        }
        $lines = preg_split('/\r\n|\r|\n/', $yaml_string); // Handle different line endings

        foreach ($lines as $line) {
            // Skip empty lines or comments (if any standard is assumed)
            if (empty(trim($line)) || (isset($line[0]) && $line[0] === '#')) {
                continue;
            }

            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Basic type casting for common YAML types (optional, but can be useful)
                if (strtolower($value) === 'true') {
                    $result[$key] = true;
                } elseif (strtolower($value) === 'false') {
                    $result[$key] = false;
                } elseif (strtolower($value) === 'null') {
                    $result[$key] = null;
                } elseif (is_numeric($value)) {
                    $result[$key] = ctype_digit($value) ? (int)$value : (float)$value;
                } else {
                    // Remove quotes if value is quoted
                    if ((isset($value[0]) && $value[0] === '"' && isset($value[strlen($value)-1]) && $value[strlen($value)-1] === '"') ||
                        (isset($value[0]) && $value[0] === "'" && isset($value[strlen($value)-1]) && $value[strlen($value)-1] === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    // Generate YAML from array
    private function generateYaml($data) {
        $yaml = '';
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $yaml_value = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $yaml_value = 'null';
            } elseif (is_string($value) && (strpos($value, "\n") !== false || strpos($value, ': ') !== false || preg_match('/^[#\[\]\{\}\-\*\&]/', $value))) {
                // For multi-line strings or strings with special chars, quote them or use block scalar (not implemented here for simplicity)
                $yaml_value = '"' . str_replace('"', '\"', $value) . '"';
            } else {
                $yaml_value = $value;
            }
            $yaml .= "$key: $yaml_value\n";
        }
        return $yaml;
    }
}
?>
