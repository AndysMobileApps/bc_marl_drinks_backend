<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Services;

use Psr\Http\Message\UploadedFileInterface;

class ImageService
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ICON_SIZE = 64; // 64x64 pixels for product icons
    
    private string $uploadPath;
    
    public function __construct()
    {
        $this->uploadPath = __DIR__ . '/../../public/images/uploads/';
        $this->ensureUploadDirectoryExists();
    }
    
    /**
     * Process and upload a product icon
     */
    public function uploadProductIcon(UploadedFileInterface $uploadedFile): string
    {
        $this->validateUploadedFile($uploadedFile);
        
        // Generate unique filename
        $extension = $this->getFileExtension($uploadedFile);
        $filename = $this->generateFilename($extension);
        $filePath = $this->uploadPath . $filename;
        
        // Move uploaded file to temporary location
        $uploadedFile->moveTo($filePath);
        
        // Process and resize image
        $this->processImage($filePath, self::ICON_SIZE, self::ICON_SIZE);
        
        // Return web-accessible path
        return '/images/uploads/' . $filename;
    }
    
    /**
     * Validate uploaded file
     */
    private function validateUploadedFile(UploadedFileInterface $file): void
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('File upload error: ' . $this->getUploadErrorMessage($file->getError()));
        }
        
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException('File too large. Maximum size is ' . (self::MAX_FILE_SIZE / 1024 / 1024) . 'MB');
        }
        
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException('Invalid file type. Allowed types: ' . implode(', ', self::ALLOWED_TYPES));
        }
    }
    
    /**
     * Get file extension from uploaded file
     */
    private function getFileExtension(UploadedFileInterface $file): string
    {
        $filename = $file->getClientFilename();
        if (!$filename) {
            return 'jpg'; // Default extension
        }
        
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Map common extensions
        return match($extension) {
            'jpeg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            default => 'jpg'
        };
    }
    
    /**
     * Generate unique filename
     */
    private function generateFilename(string $extension): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return "icon_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Process and resize image
     */
    private function processImage(string $filePath, int $width, int $height): void
    {
        // Get image info
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            throw new \InvalidArgumentException('Invalid image file');
        }
        
        [$originalWidth, $originalHeight, $imageType] = $imageInfo;
        
        // Create image resource from uploaded file
        $sourceImage = $this->createImageResource($filePath, $imageType);
        if (!$sourceImage) {
            throw new \RuntimeException('Failed to create image resource');
        }
        
        // Create new image with target dimensions
        $targetImage = imagecreatetruecolor($width, $height);
        if (!$targetImage) {
            imagedestroy($sourceImage);
            throw new \RuntimeException('Failed to create target image');
        }
        
        // Preserve transparency for PNG and GIF
        $this->preserveTransparency($targetImage, $imageType);
        
        // Resize image
        imagecopyresampled(
            $targetImage, $sourceImage,
            0, 0, 0, 0,
            $width, $height,
            $originalWidth, $originalHeight
        );
        
        // Save processed image
        $this->saveProcessedImage($targetImage, $filePath, $imageType);
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
    }
    
    /**
     * Create image resource from file
     */
    private function createImageResource(string $filePath, int $imageType)
    {
        return match($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => imagecreatefrompng($filePath),
            IMAGETYPE_GIF => imagecreatefromgif($filePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($filePath),
            default => false
        };
    }
    
    /**
     * Preserve transparency for PNG and GIF images
     */
    private function preserveTransparency($targetImage, int $imageType): void
    {
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefill($targetImage, 0, 0, $transparent);
        }
    }
    
    /**
     * Save processed image to file
     */
    private function saveProcessedImage($targetImage, string $filePath, int $imageType): void
    {
        $success = match($imageType) {
            IMAGETYPE_JPEG => imagejpeg($targetImage, $filePath, 90),
            IMAGETYPE_PNG => imagepng($targetImage, $filePath, 6),
            IMAGETYPE_GIF => imagegif($targetImage, $filePath),
            IMAGETYPE_WEBP => imagewebp($targetImage, $filePath, 90),
            default => false
        };
        
        if (!$success) {
            throw new \RuntimeException('Failed to save processed image');
        }
    }
    
    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectoryExists(): void
    {
        if (!is_dir($this->uploadPath)) {
            if (!mkdir($this->uploadPath, 0755, true)) {
                throw new \RuntimeException('Failed to create upload directory: ' . $this->uploadPath);
            }
        }
        
        if (!is_writable($this->uploadPath)) {
            throw new \RuntimeException('Upload directory is not writable: ' . $this->uploadPath);
        }
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            default => 'Unknown upload error'
        };
    }
    
    /**
     * Delete uploaded file
     */
    public function deleteFile(string $webPath): bool
    {
        // Convert web path to file system path
        $filePath = $this->uploadPath . basename($webPath);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
}
