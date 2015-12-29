<?php
namespace Common\View;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

/**
 * @author songlin
 * format:{'status'=>xxx,'message'=>'xxx',content=>'xxx'}
 */
class ErrorJsonModel extends JsonModel {
    
    protected $errCode;
    protected $errMessage;


    /**
     * @param int $errCode
     * @param array|null|\Traversable $errMessage
     * @param null $options
     */
    public function __construct($errCode, $errMessage, $options = null) 
    {
        $this->errCode = $errCode;
        $this->errMessage = $errMessage;
        parent::__construct(null, $options);
    }
    
    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        if (!isset($this->errCode) || $this->errCode == 0) {
            $this->errCode = 1001;
        }
        $ret = array('status' => $this->errCode, 'message' => $this->errMessage, 'content' => null);
        return Json::encode($ret);
    }
}