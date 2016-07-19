<?php

namespace JohnLui\AliyunOSS;

require_once __DIR__.'/oss/aliyun.php';

use Aliyun\OSS\OSSClient;
use Aliyun\OSS\Models\OSSOptions;

/**
* \OssService
*/
class AliyunOSS {

  protected $ossClient;
  protected $bucket;

  public function __construct($serverName, $AccessKeyId, $AccessKeySecret)
  {
    $this->ossClient = OSSClient::factory([
      OSSOptions::ENDPOINT => $serverName,
      'AccessKeyId' => $AccessKeyId,
      'AccessKeySecret' => $AccessKeySecret
    ]);
  }

  public static function boot($serverName, $AccessKeyId, $AccessKeySecret)
  {
    return new AliyunOSS($serverName, $AccessKeyId, $AccessKeySecret);
  }

  public function setBucket($bucket)
  {
    $this->bucket = $bucket;
    return $this;
  }

  //  这里有问题!!!!!, 这个地方我修改过代码, 所以从服务器上更新下来的会有问题
  public function uploadFile($key, $file,$content_type = "application/octst-stream")
  {
    $file_content =  fopen($file, 'rb');
    $result = $this->ossClient->putObject(array(
        'Bucket' => $this->bucket,
        'Key' => $key,
        'Content' =>$file_content,
        'ContentType' =>$content_type,
        'ContentLength' => filesize($file)
        
    ));

    fclose($file_content);
    
    return $result;

  }

  public function uploadContent($key, $content)
  {
    return $this->ossClient->putObject(array(
        'Bucket' => $this->bucket,
        'Key' => $key,
        'Content' => $content,
        'ContentLength' => strlen($content)
    ));
  }

  public function getObject($key){
    return $this->ossClient->getObject([
        'Bucket' => $this->bucket,
        'Key' => $key,

      ]);
  }

  public function getUrl($key, $expire_time)
  {
    return $this->ossClient->generatePresignedUrl([
      'Bucket' => $this->bucket,
      'Key' => $key,
      'Expires' => $expire_time
    ]);
  }

  public function createBucket($bucketName)
  {
    return $this->ossClient->createBucket(['Bucket' => $bucketName]);
  }


  public function getAllObjectKey($bucketName,$path,$maxkeys)
  {
    $config = array(
      'Bucket' => $bucketName,
      'MaxKeys' => $maxkeys,
    );

    if($path){
      $config['Prefix'] = $path;
    }

    $objectListing = $this->ossClient->listObjects($config);

    $objectKeys = [];
    foreach ($objectListing->getObjectSummarys() as $objectSummary) {
      $url = $objectSummary->getKey();

      $objectKeys[] = [
        'url' =>  $url,
        'lastModified' =>  $objectSummary->getLastModified(),
      ];
    }
    return $objectKeys;
  }
}
