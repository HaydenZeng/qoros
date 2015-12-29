<?php
namespace Common\View;

use Zend\Json\Json;

class ResponseJsonModel {

    protected $returnEntity;

    public function __construct($status, $message, $content)
    {
        $this->returnEntity = array();
        $this->returnEntity['status'] = $status;
        $this->returnEntity['message'] = $message;
        $this->returnEntity['content'] = $content;
    }

    public function response()
    {
        $resultJson = Json::encode($this->returnEntity);
        $response = new \Zend\Http\Response();
        $response->getHeaders()->addHeaderLine('Content-Type', 'text/html; charset=utf-8');
        $response->setContent($resultJson);
        return $response;
    }
}