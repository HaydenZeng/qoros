<?php
namespace Common\View;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

/**
 * @author songlin
 * format:{'status'=>xxx,'message'=>'xxx',content=>'xxx'}
 */
class UnifyJsonModel extends JsonModel {
    
    /**
     * @param mixed $variables
     * @param string $options
     */
    public function __construct($variables = null, $options = null) 
    {
        parent::__construct($variables, $options);
    }
    
    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        $variables = $this->getVariables();
        if ($variables instanceof Traversable) {
            $variables = ArrayUtils::iteratorToArray($variables);
        }
        $ret = array('status' => 0, 'message' => 'success', 'content' => $variables);
        return Json::encode($ret);
    }
}