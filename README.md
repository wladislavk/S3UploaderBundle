About
=====

Symfony bundle that handles file uploads to Amazon S3, based on [Symfony Web Uploader library](https://github.com/wladislavk/SymfonyWebUploader).
It also depends on AWS SDK and VKRSettingsBundle.

Installation
============

This bundle requires two keys to be added to ```parameters.yml```: ```s3_publishable_key```
and ```s3_secret_key```. You will also need a setting with arbitrary name that will keep
full URL of the directory where you intend to keep uploaded files:

```
s3_upload_dir: https://s3-us-west-2.amazonaws.com/my-bucket/upload-dir/
```

Remember that this directory must be open for write.

You also can add keys from Symfony Web Uploader, for example:

```
allowed_upload_size: 10000 # max file size in bytes
allowed_upload_types:
    - video/mp4
    - image/jpeg
```

Usage
=====

The public interface of this bundle is the same as of Symfony Web Uploader library, so
you can consult library's manual for usage specifics. Basic usage from your controller
will look like this:

```
$s3Uploader = $this->get('vkr_s3_uploader.s3_uploader');
$s3Uploader->setUploadDir('s3_upload_dir');
$file = new Symfony\Component\HttpFoundation\File\File('path/to/file/filename');
try {
    $uploader->setFile($file)->upload()->checkIfSuccessful();
} catch (\Exception $e) {
    ...
}
```
