<?php
namespace VKR\S3UploaderBundle\Services;

use Aws\CloudTrail\Exception\S3BucketDoesNotExistException;
use Aws\S3\S3Client;
use VKR\S3UploaderBundle\Decorators\S3ClientDecorator;
use VKR\SettingsBundle\Services\SettingsRetriever;
use VKR\SymfonyWebUploader\Decorators\GetHeadersDecorator;
use VKR\SymfonyWebUploader\Services\AbstractUploader;

/**
 * Uploads multimedia files to external host on Amazon S3.
 */

class S3Uploader extends AbstractUploader
{
    /**
     * @var null|S3ClientDecorator
     */
    protected $s3ClientDecorator;

    /**
     * @param SettingsRetriever|null $settingsRetriever
     * @param array $settings
     * @param GetHeadersDecorator|null $getHeadersDecorator
     * @param S3ClientDecorator|null $s3ClientDecorator
     */
    public function __construct(
        SettingsRetriever $settingsRetriever = null,
        array $settings = [],
        GetHeadersDecorator $getHeadersDecorator = null,
        S3ClientDecorator $s3ClientDecorator = null
    ) {
        parent::__construct($settingsRetriever, $settings);
        $this->s3ClientDecorator = $s3ClientDecorator;
    }

    /**
     * @return string
     */
    public function getNewFilename()
    {
        return $this->filename;
    }

    /**
     * Uploads the video file to S3 storage service
     *
     * @return S3Uploader
     */
    public function upload()
    {
        $s3URLData = $this->parseS3URL($this->uploadURL);
        $newFilenameWithDir = $s3URLData['dir'] . '/' . $this->filename;
        $s3key = $this->settingsRetriever->get('s3_publishable_key');
        $s3secret = $this->settingsRetriever->get('s3_secret_key');
        $config = [
            'key' => $s3key,
            'secret' => $s3secret,
        ];
        if ($this->s3ClientDecorator) {
            $s3v2 = $this->s3ClientDecorator->factory($config);
        } else {
            $s3v2 = S3Client::factory($config);
        }
        $result = $s3v2->putObject([
            'Bucket' => $s3URLData['bucket'],
            'Key' => $newFilenameWithDir,
            'SourceFile' => $this->file->getRealPath(),
        ]);
        $s3v2->waitUntil('ObjectExists', [
            'Bucket' => $s3URLData['bucket'],
            'Key' => $newFilenameWithDir,
        ]);
        return $this;
    }

    /**
     * Parses external URL for file storing into host, bucket and dir.
     *
     * @param string $url
     * @return array
     * @throws S3BucketDoesNotExistException
     */
    protected function parseS3URL($url)
    {
        $parsedData = [];
        $regexp = '/^http(?:s)?:\/\/(s3.+?)\/(.+?)\/(.+?)\/?$/i';
        $doesMatch = preg_match($regexp, $url, $matches);
        if (!$doesMatch || sizeof($matches) < 2) {
            throw new S3BucketDoesNotExistException('Upload directory is not a valid S3 bucket');
        }
        $parsedData['host'] = $matches[1];
        $parsedData['bucket'] = $matches[2];
        $parsedData['dir'] = $matches[3];
        return $parsedData;
    }
}
