<?php
namespace Wechat\Controller;

use Common\Upload\BillPhotoHandler;
use Common\Upload\ChatImageHandler;
use Common\Upload\Uploader;
//use DoctrineORMModule\Proxy\__CG__\Site\Entity\UserEntity;
use Site\Entity\UserEntity;
use Symfony\Component\Console\Tests\CustomApplication;
use Wechat\Entity\WechatMessageEntity;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Wechat\Model\Wechat;
use Site\Model\User;

class IndexController extends \Site\Controller\IndexController
{
    /**
     * @var Wechat
     */
    public $wechat;

    /**
     * @var User
     */
    public $userModel;


    public function __construct(Wechat $wechat ,User $user) {
        $this->wechat = $wechat;
        $this->userModel = $user;
    }

    /**
     * 微信接入
     */
    public function indexAction(){
        $view = new ViewModel();
        $this->wechat->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败

        //用户关注回复
        $type = $this->wechat->getRevType();
        if($type == Wechat::MSGTYPE_EVENT){
//            $this->doEvent();
        }
//        $openId = $this->wechat->getOpenId();
//        if($type == Wechat::MSGTYPE_TEXT ||$type == Wechat::MSGTYPE_IMAGE ||$type == Wechat::MSGTYPE_VOICE){
//            $this->checkUserLogin($openId);
//        }
        //转发消息给多客服
        $this->wechat->transfer_customer_service();
        $view->result = '';
        return $view;
    }


    /**
     * 处理事件
     */
    public function doEvent(){
        $event = $this->wechat->getRevEvent();
        switch($event['event']){
            case Wechat::EVENT_SUBSCRIBE:
                $openId = $this->wechat->getOpenId();
                if($this->userModel->getByOpenId($openId)){
                    //如果已经绑定过，则发送欢迎词
                    $this->wechat->textReply($this->wechat->getFastKeyText());
                }
                break;
        }
    }

    /**
     * 检查用户是否登陆过
     */
    public function checkUserLogin($openId){
        try{

//            if(DW_ENV_DEBUG){
                //测试环境没有unionid
                $userOauth = $this->userModel->getByOpenId($openId);
//            }else{
//                $userInfo = $this->wechat->getUserInfo($openId);
//                $userOauth = $this->userModel->getUserOauthByUnionIdAndVender($userInfo['unionid'],UserOauthEntity::VENDER_WECHAT);
//            }

            if(!$userOauth){
                //提示用户  去绑定帐号
                $rst = $this->wechat->textReply('您好，继续其他操作请先绑定账号：'.'http://'.$_SERVER['SERVER_NAME'].'/user/bind');
                return false;
            }else{
                //如果用户存在，则直接去登陆一下
            }
        }catch (\Exception $e){
            $this->wechat->log($e->getMessage());
        }
        return true;
    }

    /**
     * Console方法: php index.php wechat accessToken [--renew]
     * 获取微信服务号的access token
     */
    public function accessTokenAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }
        $renew = $request->getParam('renew');
        //$verbose = $request->getParam('verbose') || $request->getParam('v');

        $token = $this->wechat->getAccessToken($renew);
        return "TOKEN:$token \n";
    }

    /**
     * Console方法: php index.php wechat menu [--create]
     * 自定义菜单
     */
    public function menuAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $create = $request->getParam('create');
        $delete = $request->getParam('delete');
        if ($create) {
            $result = $this->wechat->createMenu();
            return "menu create result = " . ($result ? "success" : "failed") . "\n";
        }

        if ($delete) {
            $result = $this->wechat->deleteMenu();
            return "menu delete result = " . ($result ? 'success' : 'failed') . "\n";
        }
    }

    /**
     * http 方法
     * 自定义菜单
     */
    public function  createMenuAction(){
        $user = $this->authentication()->getIdentity();
        if($user->role != UserEntity::ROLE_ADMIN){
            echo '您无权限此操作，请联系管理员';
        }
        $result = $this->wechat->createMenu();
        echo '菜单创建'. ($result ? "成功" : "失败");
        $this->layout()->setTerminal(true);
    }

    /**
     * 上传素材
     */
    public function uploadMediaAction(){
        $view = new ViewModel();
        return $view;
    }

    /**
     * 获取永久素材列表
     */
    public function getForeverListAction(){
        $view = new ViewModel();
        $list = $this->wechat->getForeverList(Wechat::MSGTYPE_IMAGE,1,10);
        print_r($list);
        return $view;
    }

    /**
     * 获取临时素材
     */
    public function getMediaAction(){
        $view = new ViewModel();
        $list = $this->wechat->getMedia('Yyb1Jbwbq3unQYQXbIH7xpvkixydVnhSRbLD1MpiGXvXU0inb2ywsjtu_qgwB5LL');
        print_r($list);
        return $view;
    }

}