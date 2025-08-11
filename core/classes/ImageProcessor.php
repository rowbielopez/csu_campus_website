<?php
/**
 * Image Processing Helper
 * Handles image resizing, cropping, and optimization for carousel uploads
 */

class ImageProcessor {
    
    /**
     * Resize and optimize image to carousel dimensions
     */
    public static function processCarouselImage($sourcePath, $targetPath, $maxWidth = 1904, $maxHeight = 534) {
        try {
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                throw new Exception('Invalid image file');
            }
            
            list($originalWidth, $originalHeight, $imageType) = $imageInfo;
            
            // Create image resource based on type
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    throw new Exception('Unsupported image type');
            }
            
            if (!$sourceImage) {
                throw new Exception('Failed to create image resource');
            }
            
            // Calculate new dimensions maintaining aspect ratio
            $aspectRatio = $originalWidth / $originalHeight;
            $targetAspectRatio = $maxWidth / $maxHeight;
            
            if ($aspectRatio > $targetAspectRatio) {
                // Image is wider - fit to width
                $newWidth = $maxWidth;
                $newHeight = intval($maxWidth / $aspectRatio);
            } else {
                // Image is taller - fit to height
                $newHeight = $maxHeight;
                $newWidth = intval($maxHeight * $aspectRatio);
            }
            
            // Create new image
            $targetImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG/GIF
            if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                imagefill($targetImage, 0, 0, $transparent);
            }
            
            // Resize image
            imagecopyresampled(
                $targetImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );
            
            // Save optimized image
            $saved = false;
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $saved = imagejpeg($targetImage, $targetPath, 90);
                    break;
                case IMAGETYPE_PNG:
                    $saved = imagepng($targetImage, $targetPath, 6);
                    break;
                case IMAGETYPE_GIF:
                    $saved = imagegif($targetImage, $targetPath);
                    break;
                case IMAGETYPE_WEBP:
                    $saved = imagewebp($targetImage, $targetPath, 90);
                    break;
            }
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($targetImage);
            
            if (!$saved) {
                throw new Exception('Failed to save processed image');
            }
            
            return [
                'success' => true,
                'dimensions' => ['width' => $newWidth, 'height' => $newHeight],
                'original_dimensions' => ['width' => $originalWidth, 'height' => $originalHeight]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create thumbnail for admin preview
     */
    public static function createThumbnail($sourcePath, $targetPath, $maxWidth = 300, $maxHeight = 200) {
        try {
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                throw new Exception('Invalid image file');
            }
            
            list($originalWidth, $originalHeight, $imageType) = $imageInfo;
            
            // Calculate thumbnail dimensions
            $aspectRatio = $originalWidth / $originalHeight;
            
            if ($originalWidth > $originalHeight) {
                $newWidth = min($maxWidth, $originalWidth);
                $newHeight = intval($newWidth / $aspectRatio);
            } else {
                $newHeight = min($maxHeight, $originalHeight);
                $newWidth = intval($newHeight * $aspectRatio);
            }
            
            // Create images
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    throw new Exception('Unsupported image type');
            }
            
            $targetImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency
            if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                imagefill($targetImage, 0, 0, $transparent);
            }
            
            // Resize
            imagecopyresampled(
                $targetImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );
            
            // Save thumbnail as JPEG for consistency
            $saved = imagejpeg($targetImage, $targetPath, 85);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($targetImage);
            
            return $saved;
            
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
