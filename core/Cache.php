<?php
// core/Cache.php
class Cache {
    private $cacheDir;
    private $enabled;
    private $defaultLifetime; // seconds

    public function __construct($cacheDir = 'cache/', $enabled = true, $defaultLifetime = 86400) { // Default 24 hours
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        $this->enabled = $enabled;
        $this->defaultLifetime = $defaultLifetime;

        if ($this->enabled && !file_exists($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true)) {
                // Failed to create cache directory, disable caching
                error_log("Failed to create cache directory: {$this->cacheDir}. Caching disabled.");
                $this->enabled = false;
            }
        }
    }

    // Get cached content
    public function get($key) {
        if (!$this->enabled) return null;

        $filePath = $this->getCacheFilePath($key);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }

        // Check if cache is expired
        if ($this->defaultLifetime > 0 && (time() - filemtime($filePath) > $this->defaultLifetime)) {
            unlink($filePath); // Delete expired cache file
            return null;
        }

        $content = file_get_contents($filePath);
        // Basic check for tampering or corruption if a checksum was stored (more advanced)
        // For now, assume content is valid if file exists and is not expired.
        // Unserialize if data was stored serialized (e.g. for arrays/objects)
        // The example stores raw output, so no unserialization needed here.
        return $content;
    }

    // Set cache content
    public function set($key, $content, $lifetime = null) {
        if (!$this->enabled) return false;

        $filePath = $this->getCacheFilePath($key);

        // Use specific lifetime if provided, else default
        $currentLifetime = $lifetime !== null ? $lifetime : $this->defaultLifetime;

        // Writing to a temporary file first and then renaming can make writes more atomic
        $tempFilePath = $this->cacheDir . 'temp_' . uniqid(md5($key), true);

        if (file_put_contents($tempFilePath, $content) === false) {
            error_log("Failed to write to temporary cache file: {$tempFilePath}");
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            return false;
        }

        if (!rename($tempFilePath, $filePath)) {
            error_log("Failed to rename temporary cache file to: {$filePath}");
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            if(file_exists($filePath)) { // if rename failed but original exists, try to delete original
                 unlink($filePath);
            }
            // Fallback: try direct write if rename failed (less atomic)
            if (file_put_contents($filePath, $content) === false) {
                 error_log("Failed to write to cache file (fallback): {$filePath}");
                 return false;
            }
        }

        // Set file modification time if specific lifetime is 0 (cache forever until cleared)
        // or to control expiry more explicitly if needed, though filemtime is usually sufficient.
        return true;
    }

    // Clear cache for a specific key or all cache
    public function clear($key = null) {
        if (!$this->enabled) return false;

        if ($key) {
            $filePath = $this->getCacheFilePath($key);
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
            return false; // Key not found or already cleared
        } else {
            // Clear all cache
            $success = true;
            $files = glob($this->cacheDir . '*.cache');
            if ($files === false) { // Error in glob
                error_log("Failed to glob cache directory: {$this->cacheDir}");
                return false;
            }
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (!unlink($file)) {
                        $success = false;
                        error_log("Failed to delete cache file: {$file}");
                    }
                }
            }
            return $success;
        }
    }

    // Generate cache file path
    private function getCacheFilePath($key) {
        // Sanitize key to prevent directory traversal or invalid characters
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->cacheDir . $safeKey . '_' . md5($key) . '.cache'; // md5 to ensure uniqueness and fixed length
    }

    public function isEnabled() {
        return $this->enabled;
    }
}
?>
