<?php

namespace App\Services;

use Aws\S3\S3Client;
use App\Interface\S3ServiceInterface;
use Illuminate\Support\Facades\Log;

class S3Service implements S3ServiceInterface
{
    protected $s3Client;
    protected $awsBucket;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'region'      => config('filesystems.disks.s3.region'),
            'version'     => 'latest',
            'credentials' => [
                'key'    => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
        $this->awsBucket = config('filesystems.disks.s3.bucket');
    }

    public function uploadFile($file, $bucketName = null, $destinationPath = null)
    {
        try {

            $filePath = $destinationPath ? $destinationPath : 'uploads/' . rand(0, 999999) . $file->getClientOriginalName();
            $bucket   = $bucketName ? $bucketName : $this->awsBucket;

            $result   = $this->s3Client->putObject([
                'Bucket'     => $bucket,
                'Key'        => $filePath,
                'SourceFile' => $file->getRealPath(),
            ]);
            return $result['ObjectURL'];
            // return $this->getPrivateImageUrlFromS3($filePath, $bucket);
        } catch (\Exception $e) {
            Log::error(['SC3BucketError' => $e->getMessage()]);
        }
        return false;
    }

    public function getPrivateImageUrlFromS3($filename, $bucketName = null)
    {
        $command = $this->getCommand($filename, $bucketName);
        return $this->createPresignedRequest($command);
    }

    public function getCommand($filename, $bucketName = null)
    {
        $bucket  = $bucketName ? $bucketName : $this->awsBucket;

        return $this->s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key'    => $filename
        ]);
    }

    public function createPresignedRequest($command, $expiresTime = null)
    {
        $expires = $expiresTime ? $expiresTime : strtotime('+1 day');
        $request = $this->s3Client->createPresignedRequest($command, $expires);

        return (string) $request->getUri();
    }
}
