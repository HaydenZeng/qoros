<?php

namespace Common\Upload;

use Common\Exception\BusinessException;
use Common\Exception\SystemException;

abstract class PhotoHandlerInterface {
    /**
     * 借鉴ios的ContentMode概念
     * 
     * @see https://developer.apple.com/library/ios/documentation/uikit/reference/uiview_class/uiview/uiview.html
     */
    const SCALE_ASPECTFIT = 'AspectFit';
    const SCALE_ASPECTFILL = 'AspectFill';
    
    /**
     *
     * @return 图片的uuid，唯一标识图片，预留将来使用
     */
    abstract function uuid();
    /**
     *
     * @param timestamp $created            
     * @return 图片存放目录
     */
    abstract function getFilepath($created);
    /**
     *
     * @param timestamp $created            
     * @return 图片唯一名称，不包含后缀
     */
    abstract function getFilename($created, $ext);
    /**
     * 生成缩略图
     * 
     * @param string $file            
     * @param int $width            
     * @param int $height            
     * @param string $mime            
     */
    abstract function createThumbnails($file, $width, $height, $mime);
    public function getThumbnailsConfig() {
        return static::$THUMBNAILS;
    }
    
    /**
     * 图片上传失败后的，后续处理
     */
    abstract function failed();
    
    // function onPreProcess();
    // function onPostProcess();
    
    /**
     *
     * @param string $file            
     * @param int $width            
     * @param int $height            
     * @param string $mime            
     * @param int $dstWidth            
     * @param int $dstHeight            
     * @param string $dstImage            
     * @param array $crop            
     * @throws BusinessException
     * @return string filename
     */
    protected function resample($file, $width, $height, $mime, $dstWidth, $dstHeight, $dstImage, $scale = null, $crop = null) {
        switch ($mime) {
            case "image/gif" :
                $source = imagecreatefromgif ( $file );
                break;
            case "image/pjpeg" :
            case "image/jpeg" :
            case "image/jpg" :
                $source = imagecreatefromjpeg ( $file );
                break;
            case "image/x-ms-bmp" :
                $source = imagecreatefromwbmp ( $file );
                break;
            case "image/png" :
                $source = imagecreatefrompng ( $file );
                break;
            case "image/bmp" :
                $source = imagecreatefromjpeg ( $file );
                break;
            case "image/x-png" :
                $source = imagecreatefrompng ( $file );
                break;
            default :
                throw new SystemException ( 'invalid source image mime type', 901 );
        }
        
        // 先剪裁
        if (! empty ( $crop )) {
            if (! isset ( $crop ['x1'] ) && ! isset ( $crop ['y1'] ) && ! isset ( $crop ['x2'] ) && ! isset ( $crop ['y2'] )) {
                throw new SystemException ( 'invalid crop parameters, must set all x1,x2,y1,y2', 902 );
            }
            $src_x = $crop ['x1'];
            $src_y = $crop ['y1'];
            $src_w = $crop ['x2'] - $crop ['x1'];
            $src_w = $crop ['y2'] - $crop ['y1'];
        } else {
            // 不剪裁
            $src_x = 0;
            $src_y = 0;
            $src_w = $width;
            $src_h = $height;
        }
        /**
         * 压缩方式，借鉴ios contentMode概念
         * 
         * @see https://developer.apple.com/library/ios/documentation/uikit/reference/uiview_class/uiview/uiview.html
         */
        $scale = ($scale == null) ? self::SCALE_ASPECTFILL : $scale;
        switch ($scale) {
            // The option to scale the content to fill the size of the view.
            // Some portion of the content may be clipped to fill the view’s bounds.
            // 并且超出的维度，取中间部分
            case self::SCALE_ASPECTFILL :
                $ratioX = $dstWidth / $src_w;
                $ratioY = $dstHeight / $src_h;
                $ratio = max ( $ratioX, $ratioY );
                if ($ratioX > $ratioY) {
                    // 高度超出,取中间部分
                    $offset = ($src_h - $dstHeight / $ratio) / 2;
                    $src_h -= 2 * $offset;
                    $src_y += $offset;
                } else {
                    // 宽度超出,取中间部分
                    $offset = ($src_w - $dstWidth / $ratio) / 2;
                    $src_w -= 2 * $offset;
                    $src_x += $offset;
                }
                break;
            
            case self::SCALE_ASPECTFIT:
                // no cut
                $src_x = 0;
                $src_y = 0;
                
                $ratioX = $dstWidth / $src_w;
                $ratioY = $dstHeight / $src_h;
                $ratio = max ( $ratioX, $ratioY );
                
                $dstWidth = $src_w * $ratio;
                $dstHeight = $src_h * $ratio;
        }
        $target = imagecreatetruecolor ( $dstWidth, $dstHeight );
        imagecopyresampled ( $target, $source, 0, 0, $src_x, $src_y, $dstWidth, $dstHeight, $src_w, $src_h );
        // create folder if not exists
        if (! is_dir ( dirname ( $dstImage ) )) {
            mkdir ( dirname ( $dstImage ), 0777, true );
        }
        // 保存图片
        switch ($mime) {
            case "image/gif" :
                imagegif ( $target, $dstImage );
                break;
            case "image/pjpeg" :
            case "image/jpeg" :
            case "image/jpg" :
                imagejpeg ( $target, $dstImage, 90 );
                break;
            case "image/x-ms-bmp" :
                imagewbmp ( $target, $dstImage );
                break;
            case "image/png" :
                imagepng ( $target, $dstImage );
                break;
            case "image/bmp" :
                imagegif ( $target, $dstImage );
                break;
            case "image/x-png" :
                imagepng ( $target, $dstImage );
                imagepng ();
                break;
        }
        if (! $dstImage) {
            throw new SystemException ( 'save resampled image failed', 903 );
        }
        return $dstImage;
    }
}