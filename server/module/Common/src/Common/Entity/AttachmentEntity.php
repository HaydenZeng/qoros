<?php

namespace Common\Entity;

class AttachmentEntity extends Entity
{
    const ALIAS = 'attachement';
    
    public $created;
    public $origin_filename;
    public $name;
    public $upload_client;
    public $filesize;
    public $mime_content_type;
    public $filepath;
    public $filename;
    public $timeuuid;
    
    /**
     * @param string $prefix
     * @return string
     */
    public function getUrl() {
        $url = '/upload/' . $this->filepath . '/';
        $url .= $this->filename;
        return $url;
    }
}
