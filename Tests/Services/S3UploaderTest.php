<?php
namespace VKR\S3UploaderBundle\Tests\Services;

use Aws\CloudTrail\Exception\S3BucketDoesNotExistException;
use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\File;
use VKR\S3UploaderBundle\Decorators\S3ClientDecorator;
use VKR\S3UploaderBundle\Services\S3Uploader;
use VKR\SettingsBundle\Services\SettingsRetriever;

class S3UploaderTest extends \PHPUnit_Framework_TestCase
{
    private $defaultFilename = 'test.txt';

    protected $settings = [
        's3_publishable_key' => 'my_key',
        's3_secret_key' => 'my_secret',
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $settingsRetriever;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $s3Decorator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $s3Client;

    /**
     * @var S3Uploader
     */
    protected $s3Uploader;

    public function setUp()
    {
        $this->mockS3Client();
        $this->mockS3Decorator();
        $this->mockSettingsRetriever();
        $this->s3Uploader = new S3Uploader($this->settingsRetriever, [], null, $this->s3Decorator);
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
        $this->setExpectedException(S3BucketDoesNotExistException::class, 'Upload directory is not a valid S3 bucket');
        $newFileUrl = $this->s3Uploader->setFile($file, null, false)->upload();
    }

    public function testNonS3Url()
    {
        $this->settings['upload_url'] = 'https://ec2-us-west-2.amazonaws.com/my-test-bucket/my-folder/';
        $this->s3Uploader->setUploadDir('upload_url');
        $file = $this->mockFile();
        $this->setExpectedException(S3BucketDoesNotExistException::class, 'Upload directory is not a valid S3 bucket');
        $newFileUrl = $this->s3Uploader->setFile($file, null, false)->upload();
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
        $this->settingsRetriever = $this
            ->getMockBuilder(SettingsRetriever::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsRetriever->expects($this->any())
            ->method('get')
            ->will($this->returnCallback([$this, 'getSettingCallback']));
    }

    private function mockS3Decorator()
    {
        $this->s3Decorator = $this
            ->getMockBuilder(S3ClientDecorator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->s3Decorator->expects($this->any())
            ->method('factory')
            ->will($this->returnValue($this->s3Client));
    }

    private function mockS3Client()
    {
        $this->s3Client = $this
            ->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockFile()
    {
        $file = $this
            ->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->expects($this->any())
            ->method('getFilename')
            ->will($this->returnCallback([$this, 'fileGetNameCallback']));
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
