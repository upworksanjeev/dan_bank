<?php

namespace App\Interface;

interface S3ServiceInterface
{
    public function uploadFile($filePath, $bucketName = null, $destinationPath = null);
    
    public function getCommand($filename, $bucketName = null);
    
    public function createPresignedRequest($command, $expiresTime = null);
    
    public function getPrivateImageUrlFromS3($filename, $bucketName = null);
}
