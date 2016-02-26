<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Site\Controller;

use Common\Controller\Controller;
use Common\View\UnifyJsonModel;
use Site\Entity\ShareEntity;
use Site\Entity\UserEntity;
use Site\Model\Activity;
use Site\Model\District;
use Site\Model\Share;
use Wechat\Entity\WechatMessageEntity;
use Wechat\Model\Wechat;
use Zend\View\Model\ViewModel;
use Site\Model\User;

class IndexController extends Controller{

    /**
     * @var User
     */
    public $userModel;

    /**
     * @var Wechat
     */
    public $wechat;

    /**
     * @var Share
     */
    public $share;

    /**
     * @var Activity
     */
    public $activity;

    /**
     * @var District
     */
    public $discrit;

    public function __construct(Wechat $wechatModel ,User $userModel,Share $share,Activity $activity,District $district) {
        $this->wechat = $wechatModel;
        $this->userModel = $userModel;
        $this->share = $share;
        $this->activity = $activity;
        $this->discrit = $district;
    }

    public function setWechat()
    {
        // TODO: Implement setWechat() method.
    }

    public function setUser()
    {
        // TODO: Implement setUser() method.
    }

    /**
     * 活动列表页
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction() {
        $user = $this->authentication()->getIdentity();
        if(!$user){
            //自动登陆
            $request = $this->getRequest();
            if(parent::isWeixin() && !$request->isPost()){
                $code = $this->getParam('code', false);
                if(!$code){
                    $redirectUrl = $this->wechat->getOauthRedirect('http://'.$_SERVER['SERVER_NAME'].'/qoros/index/index');
                    return $this->redirect()->toUrl($redirectUrl);
                }
                $tokenData = $this->wechat->getOauthData();
                $user = $this->userModel->getByOpenId($tokenData['openid']);
                if($user){
                    $result = $this->login($user->mobile, null, false);
                    if ($result->getCode() == Result::SUCCESS && $user->username != $user->openid) {
                        return $this->redirect()->toUrl('/qoros');
                    }
                }
            }
        }

        $user = $this->authentication()->getIdentity();
        if(!$user){
            return $this->redirect()->toRoute('user',array('action'=>'register'));
        }

        if(isset($user) && $user->role == UserEntity::ROLE_ADMIN){
//            return $this->redirect()->toUrl('/adm');
        }

        $state = $this->discrit->getDistrictByCode($user->state);
        $city = $this->discrit->getDistrictByCode($user->city);
        $activitys = $this->activity->getList();
        $view = new ViewModel();
        $view->setVariables(array('user'=>$user,'activitys'=>$activitys,'state'=>$state,'city'=>$city));
        return $view;
    }

    /**
     * 活动1
     */
    public function activityOneAction(){
        $view = new ViewModel();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $href = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $jsSign = $this->wechat->getJsSign($href);
        $activity = $this->activity->getById(1);
        $view->setVariables(array('activity'=>$activity,'now'=>time()));
        $view->setVariables(array('jsSign'=>$jsSign));
        return $view;
    }

    /**
     * 活动2
     */
    public function activityTwoAction(){
        $view = new ViewModel();
        $activity = $this->activity->getById(2);
        $view->setVariables(array('activity'=>$activity,'now'=>time()));
        return $view;
    }

    /**
     * 活动3
     */
    public function activityThreeAction(){
        $view = new ViewModel();
        $activity = $this->activity->getById(3);
        $view->setVariables(array('activity'=>$activity,'now'=>time()));
        return $view;
    }

    /**
     * 活动4
     */
    public function activityFourAction(){
        $view = new ViewModel();
        $activity = $this->activity->getById(4);
        $view->setVariables(array('activity'=>$activity,'now'=>time()));
        return $view;
    }

    /**
     * 活动5
     */
    public function activityFiveAction(){
        $view = new ViewModel();
        $activity = $this->activity->getById(5);
        $view->setVariables(array('activity'=>$activity,'now'=>time()));
        return $view;
    }

    /**
     * 活动6
     */
    public function activitySixAction(){
        $view = new ViewModel();
        $activity = $this->activity->getById(6);
        $view->setVariables(array('activity'=>$activity,'now'=>time()));
        return $view;
    }


    /**
     * 记录分享的用户
     */
    public function logShareAction(){
        $user = $this->authentication()->getIdentity();
        if(!$user){
            //如果用户不存在，则说明是普通人转发，不记录任消息
            return new UnifyJsonModel();
        }
        $activityId = $this->getParam('activityId',false);
        if(!$activityId){
            return new UnifyJsonModel();
        }
        $share = new ShareEntity();
        $share->openid = $user->open_id;
        $share->user = $user->id;
        $share->activityId = $activityId;

        if(!$this->share->getByActivityIdAndUserId($activityId,$user->id)){
            $this->share->save($share);

            //send share message
            $time_str = date('Y-m-d H:i');
            $data = array('first'=>'活动分享通知','keyword1'=>'暖心活动分享','keyword2'=>"{$time_str}",'keyword3'=>'完成',
                'remark'=>'敬爱的车主，恭喜您成功完成“暖心计划”首个神秘任务！我们将会为您寄出观致汽车暖心礼包。为便于礼物顺利到达您的手中，请务必核对手机首次注册时所填写的地址是否正确。如有变动，可联系观致小Q进行更改。再次感谢您对观致汽车“暖心计划”的支持，祝您生活愉快。');
            $tplId = WechatMessageEntity::TEMPLATE_FINISH_SHARE;
            $this->wechat->sendTemplateMessage($user->open_id, $tplId, $data);
        }



        return new UnifyJsonModel();
    }

    /**
     *
     */
    public function huanyingxinAction(){

        //自动登陆
        $request = $this->getRequest();
        if(parent::isWeixin() && !$request->isPost()){
            $code = $this->getParam('code', false);
            if(!$code){
                $redirectUrl = $this->wechat->getOauthRedirect('http://'.$_SERVER['SERVER_NAME'].'/qoros/index/huanyingxin');
                return $this->redirect()->toUrl($redirectUrl);
            }
            $tokenData = $this->wechat->getOauthData();
            $user = $this->userModel->getByOpenId($tokenData['openid']);
            if($user){
                $result = $this->login($user->mobile, null, false);
                if ($result->getCode() == Result::SUCCESS && $user->username != $user->openid) {
//                    return $this->redirect()->toUrl('/qoros');
                }
            }
        }


        $view = new ViewModel();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $href = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $jsSign = $this->wechat->getJsSign($href);

        $url = 'http://'.$_SERVER['HTTP_HOST'].'/qoros/img/share_image.png';
        $view->setVariables(array('jsSign'=>$jsSign,'url'=>$url));
        return $view;
    }

    /**
     * 登陆
     * @param $name
     * @param $pwd
     * @param bool $checkPassword
     * @return \Zend\Authentication\Result
     */
    protected function login($name ,$pwd, $checkPassword = true){
        $authService = $this->authentication()->getAuthService();
        $authService->clearIdentity();

        $authAdapter = $this->authentication()->getAuthAdapter();
        $authAdapter->setUsername($name);
        $authAdapter->setPassword($pwd);
        //when social login,pass check password
        $authAdapter->setCheckPassword($checkPassword);

        $result = $authService->authenticate();
        //reset check password
        $authAdapter->setCheckPassword(true);
        return $result;
    }
}
