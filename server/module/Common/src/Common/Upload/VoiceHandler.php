<?php
namespace Common\Upload;

use Common\Uuid;

class VoiceHandler extends PhotoHandlerInterface {

    public static $THUMBNAILS = array(
    );

    private $rootDir = 'voice';
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Upload\PhotoHandlerInterface::uuid()
     */
    public function uuid()
    {
        return Uuid::generate()->string;
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Upload\PhotoHandlerInterface::getFilepath()
     */
    public function getFilepath($created)
    {
    	if ($this->userId == 0) {
    		return $this->rootDir.'/tmp';
    	}
        return $this->rootDir
            . DIRECTORY_SEPARATOR . date('Ym', strtotime($created));
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Upload\PhotoHandlerInterface::getFilename()
     */
    public function getFilename($created, $ext)
    {
        return uniqid(date('YmdHis', strtotime($created)) . '_') . '.' . $ext;
    }

    /**
     * (non-PHPdoc)
     * @see \Application\Upload\PhotoHandlerInterface::createThumbnails()
     */
    public function createThumbnails($file, $width, $height, $mime)
    {
    	//do nothing
    }

    public function failed()
    {

    }
}
