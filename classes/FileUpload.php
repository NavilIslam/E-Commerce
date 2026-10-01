<?php
/**
 * Secure File Upload Handler
 */

class FileUpload {
    private array $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    private int $maxSize = 5 * 1024 * 1024; // 5MB default
    private string $uploadDir;
    private array $errors = [];

    public function __construct(string $subDirectory = 'products', int $maxSizeBytes = 5242880) {
        $this->uploadDir = UPLOAD_PATH . DIRECTORY_SEPARATOR . $subDirectory;
        $this->maxSize = $maxSizeBytes;

        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $fileArray): ?string {
        $this->errors = [];

        if (!isset($fileArray['error']) || is_array($fileArray['error'])) {
            $this->errors[] = 'Invalid file upload parameters.';
            return null;
        }

        switch ($fileArray['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file was submitted.';
                return null;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'File exceeds maximum allowed upload size.';
                return null;
            default:
                $this->errors[] = 'An unknown error occurred during upload.';
                return null;
        }

        if ($fileArray['size'] > $this->maxSize) {
            $this->errors[] = 'File exceeds maximum allowed size of ' . round($this->maxSize / 1048576, 1) . 'MB.';
            return null;
        }

        // Verify MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fileArray['tmp_name']);

        if (!array_key_exists($mime, $this->allowedMimes)) {
            $this->errors[] = 'Invalid image format. Allowed formats: JPG, PNG, WEBP, GIF.';
            return null;
        }

        $extension = $this->allowedMimes[$mime];
        // Generate cryptographically random filename
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($fileArray['tmp_name'], $destination)) {
            $this->errors[] = 'Failed to save uploaded file to destination.';
            return null;
        }

        return $fileName;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): ?string {
        return reset($this->errors) ?: null;
    }
}
