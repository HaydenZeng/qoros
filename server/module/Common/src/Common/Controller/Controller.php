<?php
namespace Common\Controller;

use Zend\Http\Request;
use Zend\Mime\Decode;

use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Request as HttpRequest;
use User\Model\User;
use Zend\Mvc\MvcEvent;

/**
 * @author songlin
 * @method \Common\Controller\Plugin\Authentication authentication()
 */
class Controller extends AbstractActionController{
    private $data;

    public function onDispatch(MvcEvent $e){

        parent::onDispatch($e);
    }



    /**
     * 使用ua判断是否使用微信浏览器打开
     * @return bool
     */
    public function isWeixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }


    /**
     * 获得翻译后的String
     * @author jianping
     * @param String $message
     * @return string
     */
    protected  function translate($message) {
        $translator = $this->getServiceLocator()->get('translator');
        return $message = $translator->translate($message);
    }
    
//     /**
//      * @return User
//      */
//     protected function getUserModel() {
//         return $this->getServiceLocator()->get('User\Model\User');
//     }
    
    /**
     * 通用分页信息处理
     * @author jianping
     * @return multitype:number NULL
     */
    protected function getPager() {
        $pager = array("page" => 1, "count" => 10);
        if (null !== $this->getRequest()->getPost('page_index')) {
            $pager["page"] = $this->getRequest()->getPost('page_index');
        }
        if (null !== $this->getRequest()->getPost('page_size')) {
        	$pager["count"] = $this->getRequest()->getPost('page_size');
        }
        return $pager;
    }
    
    /**
     * 兼容处理Json请求
     * @return array
     * @author songlin
     */
    public function getRequestData() {
        if ($this->data != null) {
            return $this->data;
        }
        
        $request = $this->getRequest();
        $contentType = $request->getHeaders('Content-Type');
        if ($contentType && stristr($contentType->getFieldValue(), 'application/json')  !==  false) {
            $this->data = Json::decode($request->getContent(), Json::TYPE_ARRAY);
        } else {
            // normal application/www_url_encoded request
            if ($request->getMethod() == HttpRequest::METHOD_GET) {
                $this->data = $request->getQuery()->getArrayCopy();
            } else {
                $this->data = $request->getPost()->getArrayCopy();
            }
        }
        return $this->data;
    }
    
    /**
     * 返回请求参数
     * @param string|null $key
     * @param string|null $default
     * @return string|null $value
     * @author songlin
     */
    public function getParam($key, $default = null) {
        if (array_key_exists($key, $this->getRequestData())) {
            $value = $this->data[$key];
            return $value;
//             try{
//                 if (is_string($value)) {
//                     return Json::decode($value, Json::TYPE_ARRAY);
//                 }else {
//                     return $value;
//                 }
//             } catch (\Exception $e) {
//                 return $value;
//             }
        }
        return $default;
    }
    
    public function getDecodeParam($key, $default = null) {
        if (array_key_exists($key, $this->getRequestData())) {
            $value = $this->data[$key];
            try{
                return Json::decode($value, Json::TYPE_ARRAY);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $default;
    }
    
    public function getErrorMessage($inputFilter)
    {
        foreach ($inputFilter->getInvalidInput() as $error) {
            return $error->getErrorMessage();
        }
    }

    public function isAgency($userId){
        $agency = $this->getServiceLocator()->get('User\Model\User')->isAgency($userId);
        if ($agency) {
            return true;
        }else {
            return false;
        }
    }
    
    public function getCityCode() {
        //默认苏州市
        return isset($_COOKIE["city_code"]) ? $_COOKIE["city_code"] : 320500;
    }

    public function setHeaderTitle($headerTitle) {
        $this->layout()->headerTitle = $headerTitle;
        return true;
    }

    /**
     * @return string
     */
    public function getBaseUrl() {
        $uri = $this->getRequest()->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $base = sprintf('%s://%s', $scheme, $host);
        return $base;
    }
}

?>