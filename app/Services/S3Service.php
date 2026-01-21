<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Aws;

/**
 * S3 Service for file storage operations
 * 
 * Usage:
 *   $s3 = service('s3');
 *   $url = $s3->upload($file, 'documents/' . $userId);
 *   $presignedUrl = $s3->getPresignedUrl($key);
 */
class S3Service
{
    protected S3Client $client;
    protected Aws $config;

    public function __construct()
    {
        $this->config = config('Aws');

        // Validate required config
        if (empty($this->config->accessKey) || empty($this->config->secretKey) || empty($this->config->bucketName)) {
            throw new \RuntimeException('AWS configuration is incomplete. Please set aws.accessKey, aws.secretKey, and aws.bucketName in your .env file.');
        }

        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $this->config->region,
            'credentials' => [
                'key' => $this->config->accessKey,
                'secret' => $this->config->secretKey,
            ],
        ]);
    }

    /**
     * Upload a file to S3
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $path Folder path in S3 (e.g., 'documents/123')
     * @return array ['success' => bool, 'key' => string, 'url' => string, 'error' => string]
     */
    public function upload(UploadedFile $file, string $path): array
    {
        try {
            // Generate unique filename: timestamp_uniqid.extension
            $extension = $file->getClientExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $key = trim($path, '/') . '/' . $filename;

            // Upload to S3
            $result = $this->client->putObject([
                'Bucket' => $this->config->bucketName,
                'Key' => $key,
                'Body' => fopen($file->getTempName(), 'rb'),
                'ContentType' => $file->getMimeType(),
            ]);

            return [
                'success' => true,
                'key' => $key,
                'url' => $result['ObjectURL'] ?? '',
                'error' => null,
            ];
        } catch (S3Exception $e) {
            log_message('error', 'S3 Upload Error: ' . $e->getMessage());
            return [
                'success' => false,
                'key' => null,
                'url' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload raw content to S3 (for videos, etc.)
     * 
     * @param string $content File content
     * @param string $key Full S3 key (path + filename)
     * @param string $contentType MIME type
     * @return array
     */
    public function uploadContent(string $content, string $key, string $contentType): array
    {
        try {
            $result = $this->client->putObject([
                'Bucket' => $this->config->bucketName,
                'Key' => $key,
                'Body' => $content,
                'ContentType' => $contentType,
            ]);

            return [
                'success' => true,
                'key' => $key,
                'url' => $result['ObjectURL'] ?? '',
                'error' => null,
            ];
        } catch (S3Exception $e) {
            log_message('error', 'S3 Upload Error: ' . $e->getMessage());
            return [
                'success' => false,
                'key' => null,
                'url' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get a presigned URL for private file access (GET)
     * 
     * @param string $key S3 object key
     * @param int|null $expiryMinutes URL expiry in minutes (default from config)
     * @return string|null Presigned URL or null on error
     */
    public function getPresignedUrl(string $key, ?int $expiryMinutes = null): ?string
    {
        try {
            $expiry = $expiryMinutes ?? $this->config->presignedUrlExpiry;

            $command = $this->client->getCommand('GetObject', [
                'Bucket' => $this->config->bucketName,
                'Key' => $key,
            ]);

            $presignedRequest = $this->client->createPresignedRequest(
                $command,
                "+{$expiry} minutes"
            );

            return (string) $presignedRequest->getUri();
        } catch (S3Exception $e) {
            log_message('error', 'S3 Presigned URL Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a presigned URL for direct file upload (PUT)
     * 
     * Frontend uploads directly to S3 using this URL
     * 
     * @param string $path Folder path (e.g., 'documents/123')
     * @param string $filename Desired filename
     * @param string $contentType MIME type of file
     * @param int|null $expiryMinutes URL expiry in minutes
     * @param int|null $maxSizeBytes Maximum file size in bytes (enforced by AWS)
     * @return array ['success' => bool, 'uploadUrl' => string, 'key' => string]
     */
    public function getPresignedUploadUrl(string $path, string $filename, string $contentType, ?int $expiryMinutes = null, ?int $maxSizeBytes = null): array
    {
        try {
            $expiry = $expiryMinutes ?? 5; // 5 minutes for upload
            $key = trim($path, '/') . '/' . $filename;

            $commandParams = [
                'Bucket' => $this->config->bucketName,
                'Key' => $key,
                'ContentType' => $contentType,
            ];

            // Add content-length constraint if max size is specified
            if ($maxSizeBytes !== null) {
                $commandParams['@http'] = [
                    'headers' => [
                        'x-amz-content-length-range' => "0,{$maxSizeBytes}",
                    ],
                ];
            }

            $command = $this->client->getCommand('PutObject', $commandParams);

            $presignedRequest = $this->client->createPresignedRequest(
                $command,
                "+{$expiry} minutes"
            );

            return [
                'success' => true,
                'uploadUrl' => (string) $presignedRequest->getUri(),
                'key' => $key,
                'error' => null,
            ];
        } catch (S3Exception $e) {
            log_message('error', 'S3 Presigned Upload URL Error: ' . $e->getMessage());
            return [
                'success' => false,
                'uploadUrl' => null,
                'key' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a unique filename
     * 
     * @param string $extension File extension
     * @return string
     */
    public function generateFilename(string $extension): string
    {
        return time() . '_' . uniqid() . '.' . strtolower($extension);
    }

    /**
     * Delete a file from S3
     * 
     * @param string $key S3 object key
     * @return bool Success status
     */
    public function delete(string $key): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->config->bucketName,
                'Key' => $key,
            ]);
            return true;
        } catch (S3Exception $e) {
            log_message('error', 'S3 Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a file exists in S3
     * 
     * @param string $key S3 object key
     * @return bool
     */
    public function exists(string $key): bool
    {
        try {
            return $this->client->doesObjectExist($this->config->bucketName, $key);
        } catch (S3Exception $e) {
            return false;
        }
    }

    /**
     * Get file metadata
     * 
     * @param string $key S3 object key
     * @return array|null
     */
    public function getMetadata(string $key): ?array
    {
        try {
            $result = $this->client->headObject([
                'Bucket' => $this->config->bucketName,
                'Key' => $key,
            ]);

            return [
                'size' => $result['ContentLength'] ?? 0,
                'contentType' => $result['ContentType'] ?? '',
                'lastModified' => $result['LastModified'] ?? null,
            ];
        } catch (S3Exception $e) {
            return null;
        }
    }
}
