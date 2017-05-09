<?php
namespace VKR\S3UploaderBundle\Tests\Services;

use Aws\CloudTrail\Exception\S3BucketDoesNotExistException;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use VKR\S3UploaderBundle\Decorators\S3ClientDecorator;
use VKR\S3UploaderBundle\Services\S3Uploader;
use VKR\SettingsBundle\Services\SettingsRetriever;

class S3UploaderTest extends TestCase
{
    private $defaultFilename;

    private $settings = [
        's3_publishable_key' => 'my_key',
        's3_secret_key' => 'my_secret',
    ];

    /**
     * @var S3Uploader
     */
    private $s3Uploader;

    public function setUp()
    {
        $this->defaultFilename = 'test.txt';

        $s3Decorator = $this->mockS3Decorator();
        $settingsRetriever = $this->mockSettingsRetriever();
        $this->s3Uploader = new S3Uploader($settingsRetriever, [], null, $s3Decorator);
    }

    public function testUploadUrl()
    {
        $this->settings['upload_url'] = 'https://s3-us-west-2.amazonaws.com/my-test-bucket/my-folder/';
        $this->s3Uploader->setUploadDir('upload_url');
        $file = $this->mockFile();
        $this->s3Uploader->setFile($file, null, false)->upload();
        $expectedUrl = $this->settings['upload_url'] . 'test.txt';
        $newFile = $this->s3Uploader->getUploadDir() . '/' . $this->s3Uploader->getNewFilename();
        $this->assertEquals($expectedUrl, $newFile);
    }

    public function testShortUrl()
    {
        $this->settings['upload_url'] = 'https://s3-us-west-2.amazonaws.com/my-test-bucket/';
        $this->s3Uploader->setUploadDir('upload_url');
        $file = $this->mockFile();
        $this->expectException(S3BucketDoesNotExistException::class);
        $this->expectExceptionMessage('Upload directory is not a valid S3 bucket');
        $this->s3Uploader->setFile($file, null, false)->upload();
    }

    public function testNonS3Url()
    {
        $this->settings['upload_url'] = 'https://ec2-us-west-2.amazonaws.com/my-test-bucket/my-folder/';
        $this->s3Uploader->setUploadDir('upload_url');
        $file = $this->mockFile();
        $this->expectException(S3BucketDoesNotExistException::class);
        $this->expectExceptionMessage('Upload directory is not a valid S3 bucket');
        $this->s3Uploader->setFile($file, null, false)->upload();
    }

    public function testWithNonAllowableCharactersInFilename()
    {
        $this->settings['upload_url'] = 'https://s3-us-west-2.amazonaws.com/my-test-bucket/my-folder/';
        $this->s3Uploader->setUploadDir('upload_url');
        $this->defaultFilename = 'te&st+.txt';
        $file = $this->mockFile();
        $this->s3Uploader->setFile($file, null, false)->upload();
        $expectedUrl = $this->settings['upload_url'] . 'test.txt';
        $newFile = $this->s3Uploader->getUploadDir() . '/' . $this->s3Uploader->getNewFilename();
        $this->assertEquals($expectedUrl, $newFile);
    }

    private function mockSettingsRetriever()
    {
        $settingsRetriever = $this->createMock(SettingsRetriever::class);
        $settingsRetriever->method('get')->willReturnCallback([$this, 'getSettingCallback']);
        return $settingsRetriever;
    }

    private function mockS3Decorator()
    {
        $s3Decorator = $this->createMock(S3ClientDecorator::class);
        $s3Decorator->method('factory')->willReturn($this->mockS3Client());
        return $s3Decorator;
    }

    private function mockS3Client()
    {
        $s3Client = $this->createMock(S3Client::class);
        return $s3Client;
    }

    private function mockFile()
    {
        $file = $this->createMock(File::class);
        $file->method('getFilename')->willReturnCallback([$this, 'fileGetNameCallback']);
        return $file;
    }

    public function getSettingCallback($settingName)
    {
        return $this->settings[$settingName];
    }

    public function fileGetNameCallback()
    {
        return $this->defaultFilename;
    }
}
