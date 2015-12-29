<?php
namespace Common\Upload;

use Exception;
use finfo;
use Common\Exception\BusinessException;

/**
 * 图片处理类 
 * @author songlin
 */
class Uploader {
    
    /**
     * @var PhotoHandlerInterface
     */
    private $handler;
    
    public function __construct(PhotoHandlerInterface $handler) {
        $this->handler = $handler;
    }
    
    /**
     * 处理图片上传, 支持多个图片同时上传，以编号0-N来命名，如
     * array(photo_0 => array(name, type, tmp_name, error, size), photo_1 => ...)
     * @param array $files
     * @return array(array(timeuuid,name,width,height,filesize,
     *     filepath,filename,mime_content_type,origin_filename,created))
     */
    public function processMultiUpload(array $files) 
    {
        $result = array();
        foreach ($files as $fieldName => $data) {
            $index = $this->extractPhotoIndex($fieldName);
            try {
                $photo = $this->processUpload($data);
                $result[$index] = array('status' => 0, 'photo' => $photo);
            } catch (Exception $e) {
                $result[$index] = array('status' => $e->getCode(), 'message' => $e->getMessage()); 
            }
        }
        ksort($result);
        return $result;
    }
    
    /**
     * 处理单张图片
     * @param array $data array(name, type, tmp_name, error, size)
     * @return array(timeuuid,name,width,height,filesize,
     *     filepath,filename,mime_content_type,origin_filename,created)
     */
    public function processUpload(array $data) 
    {
        $photo = array();
        if ($data['error'] == UPLOAD_ERR_OK) {
            $photo['created'] = date('Y-m-d H:i:s', time());
            $photo['name'] = $photo['origin_filename'] = $data['name'];
            $photo['upload_client'] = $this->getUploadClient();
            
            $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $ext = $this->getFileExt($data['name'], $data['tmp_name'], $ext);
            $photo['filepath'] = $this->handler->getFilepath($photo['created']);
            $photo['filename'] = $this->handler->getFilename($photo['created'], $ext);
            $photo['filesize'] = $data['size'];
            $destination = $this->getFileDestinationPath($photo, $ext);
            if (! is_dir(dirname($destination))) {
    			mkdir(dirname($destination), 0777, true);
    		}
            if (! move_uploaded_file($data['tmp_name'], $destination)) {
                throw new BusinessException('move upload file failed: ' . $data['name'], 904);
            }
            $photo['timeuuid'] = $this->handler->uuid();
            return $photo;
        } else if ($data['error'] == UPLOAD_ERR_PARTIAL) {
            // TODO... 可能在此处理断点续传
        } else {
            throw new BusinessException($this->codeToMessage($data['error']), 906);
        }
    }

    public function processMultiFileUpload(array $data, $index)
    {
        $photo = array();
        if ($data['error'][$index] == UPLOAD_ERR_OK) {
            $photo['created'] = date('Y-m-d H:i:s', time());
            $photo['name'] = $photo['origin_filename'] = $data['name'][$index];
            $photo['upload_client'] = $this->getUploadClient();

            $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $ext = $this->getFileExt($data['name'][$index], $data['tmp_name'][$index], $ext);
            $photo['filepath'] = $this->handler->getFilepath($photo['created']);
            $photo['filename'] = $this->handler->getFilename($photo['created'], $ext);
            $photo['filesize'] = $data['size'][$index];
            $destination = $this->getFileDestinationPath($photo, $ext);
            if (! is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0777, true);
            }
            if (! move_uploaded_file($data['tmp_name'][$index], $destination)) {
                throw new BusinessException('move upload file failed: ' . $data['name'][$index], 904);
            }
            $photo['timeuuid'] = $this->handler->uuid();
            return $photo;
        } else if ($data['error'][$index] == UPLOAD_ERR_PARTIAL) {
            // TODO... 可能在此处理断点续传
        } else {
            throw new BusinessException($this->codeToMessage($data['error'][$index]), 906);
        }
    }

    /**
     * 处理单张网路图片
     */
    public function processUrlUpload($url){

        if($url=="") return false;

        $data['name'] = time().'.jpg';

        $photo['created'] = date('Y-m-d H:i:s', time());
        $photo['name'] = $photo['origin_filename'] = $data['name'];
        $photo['upload_client'] = $this->getUploadClient();

        $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $ext = $this->getFileExt($data['name'],$data['name'], $ext);
        $photo['filepath'] = $this->handler->getFilepath($photo['created']);
        $photo['filename'] = $this->handler->getFilename($photo['created'], $ext);
        $destination = $this->getFileDestinationPath($photo, $ext);

        if (! is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0777, true);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_content = curl_exec($ch);
        curl_close($ch);

        $downloaded_file = fopen($destination, 'w');
        fwrite($downloaded_file, $file_content);
        fclose($downloaded_file);

//        ob_start();//打开输出缓冲区，也就是暂时不允许输出
//        readfile($url);//读一个文件写入到输出缓冲
//        $img = ob_get_contents();
//        ob_end_clean();//下载完删除缓冲区，而不是输出
//        $size = strlen($img); // 图片大小
//        $fp2=fopen($destination, "a");
//        fwrite($fp2,$img);
//        fclose($fp2);

        $photo['timeuuid'] = $this->handler->uuid();
        return $photo;
    }


    /**
     * 处理单张图片
     * @param array $data array(name, type, tmp_name, error, size)
     * @return array(timeuuid,name,width,height,filesize,
     *     filepath,filename,mime_content_type,origin_filename,created)
     */
    public function processUploadFile(array $data)
    {
        $file = array();
        if ($data['error'] == UPLOAD_ERR_OK) {
            $file['created'] = date('Y-m-d H:i:s', time());
            $file['name'] = $file['origin_filename'] = $data['name'];
            $file['upload_client'] = $this->getUploadClient();
    
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $ext = $this->getFileExt($data['name'], $data['tmp_name'], $ext);
            $file['filepath'] = $this->handler->getFilepath($file['created']);
            $file['filename'] = $this->handler->getFilename($file['created'], $ext);
            $destination = $this->getFileDestinationPath($file, $ext);
            if (! is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0777, true);
            }
            if (! move_uploaded_file($data['tmp_name'], $destination)) {
                throw new BusinessException('move upload file failed: ' . $data['name'], 904);
            }
            try {
//                 $file['filesize'] = sprintf('%u', filesize($destination));
//                 return $file;
                
                $file['filesize'] = sprintf('%u', filesize($destination));
                $file['mime_content_type'] = $this->getFileMimeType($destination);
                list($width, $height, $imageType) = getimagesize($destination);
                $mime = $this->getImageType($imageType, $file['mime_content_type']);
                if ($width > 0 && $height > 0) {
                    $file['width'] = $width;
                    $file['height'] = $height;
                    $this->handler->createThumbnails($destination, $width, $height, $mime);
                    $file['timeuuid'] = $this->handler->uuid();
                    return $file;
                } else {
                    throw new BusinessException('Probably not a image uploaded', 905);
                }
                
            } catch (BusinessException $e) {
                $this->handler->failed($destination);
                throw $e;
            }
        } else if ($data['error'] == UPLOAD_ERR_PARTIAL) {
            // TODO... 可能在此处理断点续传
        } else {
            throw new BusinessException($this->codeToMessage($data['error']), 906);
        }
    }
    
    private function extractPhotoIndex($fieldName) 
    {
        preg_match('/^\w*_(\d+)$/', $fieldName, $matches);
        return (count($matches) > 0 ? $matches[1] : 0) ;
    }
    
    private function getFileMimeType($file)
    {
        // @see http://us3.php.net/manual/en/function.finfo-open.php
        $finfo = new finfo(FILEINFO_MIME);
        return $finfo->file($file);
    }
    
    public function getFileDestinationPath($photo, $ext = null) 
    {
        $filename = pathinfo($photo['filename'], PATHINFO_FILENAME);
        if ($ext == null) {
            $ext = pathinfo($photo['filename'], PATHINFO_EXTENSION);
        }
        $destination =  getcwd()
            . DIRECTORY_SEPARATOR .'public' 
            . DIRECTORY_SEPARATOR .'upload'
            . DIRECTORY_SEPARATOR . $photo['filepath']
            . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
        return $destination;
    }
    
    /**
     * 删除图片
     * @param array $photo
     * @param array $thumbnails
     */
    public function trash($photo, array $prefixes = null) {
        $source = $this->getFileDestinationPath($photo);
        $trashFolder = getcwd() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'trash';
        $dest = $trashFolder 
            . DIRECTORY_SEPARATOR . $photo['filepath']
            . DIRECTORY_SEPARATOR . $photo['filename'];
        
        // create folder if not exists
        if (! is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0777, true);
        }
        
        $bool = @rename($source, $dest);
        if ($bool) {
            $orginalSrcFolder = dirname($source);
            $orginalSrcFilename = basename($source);
            $destTrashFolder = dirname($dest);
            foreach ((array)$prefixes as $p) {
                $src = $orginalSrcFolder . DIRECTORY_SEPARATOR . $p . "_" . $orginalSrcFilename;
                $dst = $destTrashFolder . DIRECTORY_SEPARATOR. $p . "_" . $orginalSrcFilename;
                @rename($src, $dst);
            }
        }
    }
    
    /**
     * @param int $imageType
     * @param string $mime
     * @return $mime Mime content type
     */
    private function getImageType($imageType, $mimeContentType)
    {
        $mime = image_type_to_mime_type($imageType);
        if (empty($mime)) {
            $parts = explode(';', $mimeContentType);
            $mime = count($parts) > 0 ? trim($parts[0]) : null;
        }
        return $mime;
    }
    
    /**
     * @return string
     */
    private function getUploadClient()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
    
    /**
     * 查看文件真正的后缀名
     * @param file $file
     * @return string
     */
    private function getFileExt($name, $tmpfile, $default) {
        $file_handle = fopen($tmpfile, "rb");
        $bin = fread($file_handle, 2); // 只读10字节
        if ($bin !== false) {
            fclose($file_handle);
            $strInfo = @unpack("c2chars", $bin);
            $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
            // 说明
            // array("D0CF11E0","xls/doc"),
            // array("504B0304","zip"),
            // array("52617221","rar"),
            if ($strInfo['chars1'] == '-1' && $strInfo['chars2'] == '-40') {
                $typeCode = '255216';
            }
            if ($strInfo['chars1'] == '-119' && $strInfo['chars2'] == '80') {
                $typeCode = '13780';
            }
            if (($typeCode == "7173") or ($typeCode == "255216") or ($typeCode == "6677") or ($typeCode == "13780")) {
                switch ($typeCode) {
                    case 7173 :
                        $string_return = "gif";
                        break;
                    case 255216 :
                        $string_return = "jpg";
                        break;
                    case 6677 :
                        $string_return = "bmp";
                        break;
                    case 13780 :
                        $string_return = "png";
                        break;
                }
                return $string_return;
            }
        } else{
            $arr_temp = explode('.', $name);
            if (count($arr_temp) > 1) {
                return array_pop($arr_temp);
            }
        }
        return $default;
    }
    
    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;
            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }
}