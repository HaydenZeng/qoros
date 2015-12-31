<?php 
namespace Site\Controller;

use Common\Controller\Controller;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;

class BaseController extends Controller {

    public  function setWechat() {}
    public  function setUser() {}

    public function onDispatch(MvcEvent $e) {
        parent::onDispatch($e);
        $user = $this->identity();
        if(empty($user)){
            $request = $this->getRequest();
            $redirect = $_SERVER['REQUEST_URI'];
            if(parent::isWeixin() && !$request->isPost()){
                $code = $this->getParam('code', false);
                if(!$code){
                    $redirectUrl = $this->wechat->getOauthRedirect('http://'.$_SERVER['SERVER_NAME']
                        .'/qoros/user/agreement?redirect='.$redirect);
                    return $this->redirect()->toUrl($redirectUrl);
                }
                $tokenData = $this->wechat->getOauthData();
                $user = $this->userModel->getByOpenId($tokenData['openid']);
                if($user){
                    $result = $this->login($user->mobile, null, false);
                    if ($result->getCode() == Result::SUCCESS && $user->username != $user->openid) {
                        return $this->redirect()->toUrl('/');
                    }
                }
            }
        }
    }


}