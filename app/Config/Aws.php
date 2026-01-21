<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * AWS Configuration
 */
class Aws extends BaseConfig
{
    public string $accessKey = '';
    public string $secretKey = '';
    public string $region = 'ap-south-1';
    public string $bucketName = '';

    // Presigned URL expiry in minutes
    public int $presignedUrlExpiry = 15;

    // S3 path prefixes for different file types
    public string $documentsPrefix = 'documents';
    public string $videosPrefix = 'videos';
    public string $thumbnailsPrefix = 'thumbnails';
    public string $certificatesPrefix = 'certificates';

    public function __construct()
    {
        parent::__construct();

        // Load from environment
        $this->accessKey = env('AWS_ACCESS_KEY_ID', '');
        $this->secretKey = env('AWS_SECRET_ACCESS_KEY', '');
        $this->region = env('AWS_REGION', $this->region);
        $this->bucketName = env('AWS_BUCKET', '');
    }
}
