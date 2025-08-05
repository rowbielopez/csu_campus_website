<?php
/**
 * Media Management Core Functions
 * Handles file uploads, media library operations, and security
 */

class MediaManager {
    private $db;
    private $upload_path;
    private $allowed_types;
    private $max_file_size;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->upload_path = $_SERVER['DOCUMENT_ROOT'] . '/campus_website2/uploads/';
        $this->max_file_size = 10 * 1024 * 1024; // 10MB
        
        // Allowed file types with MIME validation
        $this->allowed_types = [
            'image' => [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            ],
            'document' => [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ],
            'video' => [
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'mov' => 'video/quicktime'
            ],
            'audio' => [
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg'
            ]
        ];
    }
    
    /**
     * Upload a file to campus-specific directory
     */
    public function uploadFile($file, $campus_id, $user_id, $options = []) {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }
            
            // Get campus info
            $campus = $this->getCampusById($campus_id);
            if (!$campus) {
                return ['success' => false, 'error' => 'Invalid campus ID'];
            }
            
            // Generate unique filename
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $file_type = $this->getFileType($file_extension);
            $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
            $safe_name = $this->sanitizeFilename($original_name);
            $timestamp = date('Ymd_His');
            $random = substr(md5(uniqid()), 0, 6);
            $new_filename = $safe_name . '_' . $timestamp . '_' . $random . '.' . $file_extension;
            
            // Create campus directory if not exists
            $campus_dir = $this->upload_path . $campus['code'] . '/';
            if (!is_dir($campus_dir)) {
                mkdir($campus_dir, 0755, true);
            }
            
            // Move uploaded file
            $file_path = $campus_dir . $new_filename;
            $file_url = '/campus_website2/uploads/' . $campus['code'] . '/' . $new_filename;
            
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                return ['success' => false, 'error' => 'Failed to move uploaded file'];
            }
            
            // Get file metadata
            $metadata = $this->getFileMetadata($file_path, $file_extension);
            
            // Save to database
            $media_id = $this->saveToDatabase([
                'campus_id' => $campus_id,
                'uploader_id' => $user_id,
                'filename' => $new_filename,
                'original_filename' => $file['name'],
                'file_path' => $file_path,
                'file_url' => $file_url,
                'file_type' => $file_type,
                'mime_type' => $file['type'],
                'file_size' => $file['size'],
                'file_extension' => $file_extension,
                'alt_text' => $options['alt_text'] ?? '',
                'caption' => $options['caption'] ?? '',
                'description' => $options['description'] ?? '',
                'is_public' => $options['is_public'] ?? 1,
                'metadata' => json_encode($metadata)
            ]);
            
            // Generate thumbnail for images (if GD extension is available)
            if ($file_type === 'image' && extension_loaded('gd')) {
                $this->generateThumbnail($file_path, $new_filename);
            }
            
            return [
                'success' => true,
                'media_id' => $media_id,
                'filename' => $new_filename,
                'file_url' => $file_url,
                'file_type' => $file_type
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > $this->max_file_size) {
            $max_mb = $this->max_file_size / (1024 * 1024);
            return ['valid' => false, 'error' => "File too large. Maximum size: {$max_mb}MB"];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = [];
        foreach ($this->allowed_types as $type => $exts) {
            $allowed_extensions = array_merge($allowed_extensions, array_keys($exts));
        }
        
        if (!in_array($extension, $allowed_extensions)) {
            return ['valid' => false, 'error' => 'File type not allowed: ' . $extension];
        }
        
        // Validate MIME type
        $mime_type = $file['type'];
        $expected_mime = null;
        foreach ($this->allowed_types as $type => $exts) {
            if (isset($exts[$extension])) {
                $expected_mime = $exts[$extension];
                break;
            }
        }
        
        if ($mime_type !== $expected_mime) {
            // Additional check using finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($detected_mime !== $expected_mime) {
                return ['valid' => false, 'error' => 'Invalid file type detected'];
            }
        }
        
        return ['valid' => true];
    }
    
    /**
     * Get file type based on extension
     */
    private function getFileType($extension) {
        foreach ($this->allowed_types as $type => $extensions) {
            if (array_key_exists($extension, $extensions)) {
                return $type;
            }
        }
        return 'unknown';
    }
    
    /**
     * Sanitize filename to prevent security issues
     */
    private function sanitizeFilename($filename) {
        // Remove path traversal attempts
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        // Limit length
        $filename = substr($filename, 0, 50);
        
        return $filename;
    }
    
    /**
     * Get file metadata (dimensions for images, etc.)
     */
    private function getFileMetadata($file_path, $extension) {
        $metadata = [];
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $image_info = getimagesize($file_path);
            if ($image_info) {
                $metadata['width'] = $image_info[0];
                $metadata['height'] = $image_info[1];
                $metadata['aspect_ratio'] = round($image_info[0] / $image_info[1], 2);
            }
        }
        
        return $metadata;
    }
    
    /**
     * Generate thumbnail for images
     */
    private function generateThumbnail($source_path, $filename) {
        // Check if GD extension is loaded
        if (!extension_loaded('gd')) {
            error_log('GD extension not available for thumbnail generation');
            return false;
        }
        
        $thumb_dir = $this->upload_path . 'thumbs/';
        if (!is_dir($thumb_dir)) {
            mkdir($thumb_dir, 0755, true);
        }
        
        $thumb_path = $thumb_dir . 'thumb_' . $filename;
        $thumb_width = 300;
        $thumb_height = 300;
        
        // Get image info
        $image_info = getimagesize($source_path);
        if (!$image_info) return false;
        
        list($orig_width, $orig_height, $image_type) = $image_info;
        
        // Calculate thumbnail dimensions (maintain aspect ratio)
        $ratio = min($thumb_width / $orig_width, $thumb_height / $orig_height);
        $new_width = round($orig_width * $ratio);
        $new_height = round($orig_height * $ratio);
        
        // Create image resources
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($source_path);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }
        
        if (!$source) return false;
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG and GIF
        if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Resize image
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
        
        // Save thumbnail
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $thumb_path, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $thumb_path);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $thumb_path);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return true;
    }
    
    /**
     * Save media record to database
     */
    private function saveToDatabase($data) {
        $sql = "INSERT INTO media (
            campus_id, uploader_id, filename, original_filename, file_path, file_url,
            file_type, mime_type, file_size, file_extension, alt_text, caption,
            description, is_public, metadata
        ) VALUES (
            :campus_id, :uploader_id, :filename, :original_filename, :file_path, :file_url,
            :file_type, :mime_type, :file_size, :file_extension, :alt_text, :caption,
            :description, :is_public, :metadata
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get media files for a campus with pagination and filtering
     */
    public function getMediaFiles($campus_id, $options = []) {
        $page = $options['page'] ?? 1;
        $per_page = $options['per_page'] ?? 20;
        $file_type = $options['file_type'] ?? null;
        $search = $options['search'] ?? null;
        $user_id = $options['user_id'] ?? null;
        
        $offset = ($page - 1) * $per_page;
        
        // Build WHERE clause
        $where_conditions = ['m.campus_id = :campus_id'];
        $params = ['campus_id' => $campus_id];
        
        if ($file_type) {
            $where_conditions[] = 'm.file_type = :file_type';
            $params['file_type'] = $file_type;
        }
        
        if ($search) {
            $where_conditions[] = '(m.original_filename LIKE :search OR m.alt_text LIKE :search OR m.caption LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        
        if ($user_id) {
            $where_conditions[] = 'm.uploader_id = :user_id';
            $params['user_id'] = $user_id;
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM media m {$where_clause}";
        $count_stmt = $this->db->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetchColumn();
        
        // Get media files
        $sql = "SELECT m.*, u.username, u.first_name, u.last_name
                FROM media m
                LEFT JOIN users u ON m.uploader_id = u.id
                {$where_clause}
                ORDER BY m.created_at DESC
                LIMIT {$per_page} OFFSET {$offset}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'files' => $files,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ];
    }
    
    /**
     * Get single media file by ID
     */
    public function getMediaById($id, $campus_id = null) {
        $sql = "SELECT m.*, u.username, u.first_name, u.last_name
                FROM media m
                LEFT JOIN users u ON m.uploader_id = u.id
                WHERE m.id = :id";
        
        $params = ['id' => $id];
        
        if ($campus_id) {
            $sql .= " AND m.campus_id = :campus_id";
            $params['campus_id'] = $campus_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete media file
     */
    public function deleteMedia($id, $campus_id = null) {
        // Get media info first
        $media = $this->getMediaById($id, $campus_id);
        if (!$media) {
            return ['success' => false, 'error' => 'Media not found'];
        }
        
        try {
            // Delete physical files
            if (file_exists($media['file_path'])) {
                unlink($media['file_path']);
            }
            
            // Delete thumbnail if exists
            $thumb_path = $this->upload_path . 'thumbs/thumb_' . $media['filename'];
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
            }
            
            // Delete from database
            $sql = "DELETE FROM media WHERE id = :id";
            if ($campus_id) {
                $sql .= " AND campus_id = :campus_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $params = ['id' => $id];
            if ($campus_id) {
                $params['campus_id'] = $campus_id;
            }
            
            $stmt->execute($params);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update media metadata
     */
    public function updateMedia($id, $data, $campus_id = null) {
        $allowed_fields = ['alt_text', 'caption', 'description', 'is_public', 'is_featured'];
        $update_fields = [];
        $params = ['id' => $id];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($update_fields)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }
        
        $sql = "UPDATE media SET " . implode(', ', $update_fields) . " WHERE id = :id";
        if ($campus_id) {
            $sql .= " AND campus_id = :campus_id";
            $params['campus_id'] = $campus_id;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get campus by ID
     */
    private function getCampusById($campus_id) {
        $sql = "SELECT * FROM campuses WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $campus_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get file type options
     */
    public function getFileTypes() {
        return array_keys($this->allowed_types);
    }
    
    /**
     * Get allowed extensions for a file type
     */
    public function getAllowedExtensions($file_type = null) {
        if ($file_type && isset($this->allowed_types[$file_type])) {
            return array_keys($this->allowed_types[$file_type]);
        }
        
        $all_extensions = [];
        foreach ($this->allowed_types as $type => $extensions) {
            $all_extensions = array_merge($all_extensions, array_keys($extensions));
        }
        
        return $all_extensions;
    }
}
?>
