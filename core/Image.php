<?php
// core/Image.php
class Image {
    private $image;
    private $originalFile;
    private $width;
    private $height;
    private $type; // e.g., IMAGETYPE_JPEG

    public function __construct($file) {
        if (!extension_loaded('gd')) {
            throw new Exception('GD library is not installed or enabled.');
        }
        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception("Image file not found or not readable: $file");
        }

        $this->originalFile = $file;
        $imageInfo = getimagesize($file);

        if ($imageInfo === false) {
            throw new Exception("Invalid image file or unsupported image type: $file");
        }

        list($this->width, $this->height, $this->type) = $imageInfo;

        switch ($this->type) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($file);
                // Preserve transparency for PNG
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($file);
                // For GIFs, transparency is handled differently, often by palette.
                // imagecolortransparent might be needed if dealing with indexed color transparency.
                break;
            default:
                throw new Exception('Unsupported image type: ' . image_type_to_mime_type($this->type));
        }

        if (!$this->image) {
            throw new Exception('Failed to create image resource from file: ' . $file);
        }
    }

    // Resize image
    public function resize($newWidth, $newHeight, $maintainAspectRatio = true) {
        if ($newWidth <= 0 || $newHeight <= 0) {
            throw new InvalidArgumentException("New width and height must be positive integers.");
        }

        $originalAspectRatio = $this->width / $this->height;

        if ($maintainAspectRatio) {
            $targetAspectRatio = $newWidth / $newHeight;
            if ($originalAspectRatio > $targetAspectRatio) { // Original is wider
                $newHeight = (int)($newWidth / $originalAspectRatio);
            } else { // Original is taller or same aspect ratio
                $newWidth = (int)($newHeight * $originalAspectRatio);
            }
            // Ensure dimensions are at least 1px
            $newWidth = max(1, $newWidth);
            $newHeight = max(1, $newHeight);
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->type === IMAGETYPE_PNG || $this->type === IMAGETYPE_GIF) {
            $this->preserveTransparency($resizedImage);
        }

        imagecopyresampled($resizedImage, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $this->width, $this->height);

        imagedestroy($this->image);
        $this->image = $resizedImage;
        $this->width = $newWidth;
        $this->height = $newHeight;

        return $this;
    }

    // Crop image
    public function crop($cropWidth, $cropHeight, $x = 0, $y = 0) {
        if ($cropWidth <= 0 || $cropHeight <= 0) {
            throw new InvalidArgumentException("Crop width and height must be positive integers.");
        }
        if ($x < 0 || $y < 0 || ($x + $cropWidth) > $this->width || ($y + $cropHeight) > $this->height) {
            // Adjust crop if it goes out of bounds, or throw error.
            // For simplicity, let's ensure it doesn't go out of bounds by clipping.
            // A more robust solution would be to decide on a strategy (error, scale crop box, etc.)
            $x = max(0, $x);
            $y = max(0, $y);
            $cropWidth = min($cropWidth, $this->width - $x);
            $cropHeight = min($cropHeight, $this->height - $y);
            if ($cropWidth <= 0 || $cropHeight <= 0) throw new InvalidArgumentException("Crop dimensions result in zero or negative size after boundary adjustment.");
        }

        $croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);

        if ($this->type === IMAGETYPE_PNG || $this->type === IMAGETYPE_GIF) {
            $this->preserveTransparency($croppedImage);
        }

        imagecopy($croppedImage, $this->image, 0, 0, $x, $y, $cropWidth, $cropHeight);

        imagedestroy($this->image);
        $this->image = $croppedImage;
        $this->width = $cropWidth;
        $this->height = $cropHeight;

        return $this;
    }

    private function preserveTransparency($newImageResource) {
        if ($this->type === IMAGETYPE_PNG) {
            imagealphablending($newImageResource, false);
            imagesavealpha($newImageResource, true);
        } elseif ($this->type === IMAGETYPE_GIF) {
            // Attempt to preserve GIF transparency
            $transparentIndex = imagecolortransparent($this->image);
            if ($transparentIndex >= 0) {
                $transparentColor = imagecolorsforindex($this->image, $transparentIndex);
                $newTransparentIndex = imagecolorallocatealpha($newImageResource, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue'], $transparentColor['alpha']);
                imagefill($newImageResource, 0, 0, $newTransparentIndex);
                imagecolortransparent($newImageResource, $newTransparentIndex);
            } else { // If no transparent color, ensure PNG-like alpha blending for truecolor GIF target
                 imagealphablending($newImageResource, false);
                 imagesavealpha($newImageResource, true);
            }
        }
    }

    // Save image
    public function save($file, $quality = 85) {
        $outputType = $this->type; // Default to original type
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Optionally change type based on output extension
        if ($extension === 'jpg' || $extension === 'jpeg') $outputType = IMAGETYPE_JPEG;
        if ($extension === 'png') $outputType = IMAGETYPE_PNG;
        if ($extension === 'gif') $outputType = IMAGETYPE_GIF;

        $result = false;
        switch ($outputType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($this->image, $file, $quality);
                break;
            case IMAGETYPE_PNG:
                // PNG quality is compression level (0-9), not percentage
                $pngQuality = round(($quality / 100) * 9);
                $result = imagepng($this->image, $file, $pngQuality);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($this->image, $file);
                break;
            default:
                throw new Exception('Unsupported output image type or failed to determine from extension.');
        }
        // Don't destroy image here if further operations are needed,
        // but the example implies save is a final step for this instance.
        // imagedestroy($this->image); $this->image = null;
        return $result;
    }

    // Get image as base64 data URL
    public function dataUrl($quality = 85) {
        ob_start();
        $mime = '';
        switch ($this->type) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image, null, $quality);
                $mime = 'image/jpeg';
                break;
            case IMAGETYPE_PNG:
                $pngQuality = round(($quality / 100) * 9);
                imagepng($this->image, null, $pngQuality);
                $mime = 'image/png';
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image, null);
                $mime = 'image/gif';
                break;
            default:
                imagedestroy($this->image); // Clean up if error
                throw new Exception('Cannot create data URL for unsupported image type.');
        }
        $imageData = ob_get_clean();
        // Consider not destroying image here if the object is to be reused.
        // imagedestroy($this->image); $this->image = null;
        return 'data:' . $mime . ';base64,' . base64_encode($imageData);
    }

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    // Call this when the Image object is no longer needed to free up memory
    public function __destruct() {
        if ($this->image) {
            imagedestroy($this->image);
        }
    }
}
?>
