<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Site\Controller;

use Common\View\UnifyJsonModel;
use Site\Entity\ShareEntity;
use Site\Entity\UserEntity;
use Site\Model\Activity;
use Site\Model\District;
use Site\Model\Share;
use Wechat\Model\Wechat;
use Zend\View\Model\ViewModel;
use Site\Model\User;

class IndexController extends BaseController{

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
        }
        return new UnifyJsonModel();
    }

    /**
     *
     */
    public function huanyingxinAction(){
        $view = new ViewModel();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $href = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $jsSign = $this->wechat->getJsSign($href);
        $view->setVariables(array('jsSign'=>$jsSign));
        return $view;
    }
}
