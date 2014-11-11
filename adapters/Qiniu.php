<?php
namespace callmez\storage\adapters;

use Yii;
use callmez\file\system\adapters\Qiniu as QiniuAdapter;
use callmez\storage\FileProcessInterface;
//文件操作
require_once Yii::getAlias("@vendor/qiniu/php-sdk/qiniu/fop.php");
class Qiniu extends QiniuAdapter implements FileProcessInterface
{
    /**
     * 文件上传类
     * @var string
     */
    public $uploaderClass = 'callmez\storage\uploaders\Qiniu';

    /**
     * 生成图片缩略图路径
     * @param $path
     * @param array $options
     * @return string
     */
    public function getThumbnail($path, array $options)
    {
        $path .= '?imageView/2/';
        isset($options['width']) && $path .= 'w/' . $options['width'];
        isset($options['height']) && $path .= 'h/' . $options['height'];
        return $path;
    }

    /**
     * 获取图片宽
     * @param $path
     * @return float|int|mixed|\Services_JSON_Error|string|void
     */
    public function getWidth($path)
    {
        return $this->getImageInfo($path);
    }

    /**
     * 获取图片高
     * @param $path
     * @return float|int|mixed|\Services_JSON_Error|string|void
     */
    public function getHeight($path)
    {
        return $this->getImageInfo($path);
    }

    /**
     * 获取图片文件exif信息
     * @param $path
     * @return float|int|mixed|\Services_JSON_Error|string|void
     */
    public function getExif($path)
    {
        $data = json_decode(file_get_contents($this->getImageExifUrl($path)), true);
        return is_array($data) ? $data + ['path' => $path] : null;
    }

    /**
     * 获取图片文件信息
     * @param $path
     * @return array|null
     */
    public function getImageInfo($path)
    {
        $data = json_decode(file_get_contents($this->getImageInfoUrl($path)), true);
        return is_array($data) ? $data + ['path' => $path] : null;
    }

    /**
     * 图片文件信息地址(可以获取私有文件)
     * @param $path
     * @return string
     */
    public function getImageInfoUrl($path)
    {
        $getPolicy = new \Qiniu_RS_GetPolicy();
        return $getPolicy->MakeRequest((new \Qiniu_ImageInfo)->MakeRequest($this->getUrl($path)), null);
    }
    /**
     * 图片exif信息地址(可以获取私有文件)
     * @param $path
     * @return string
     */
    public function getImageExifUrl($path)
    {
        $getPolicy = new \Qiniu_RS_GetPolicy();
        return $getPolicy->MakeRequest((new \Qiniu_Exif)->MakeRequest($this->getUrl($path)), null);
    }
}