<?php
namespace Site\Controller;

use Common\Exception\BusinessException;
use Common\View\ErrorJsonModel;
use Common\View\UnifyJsonModel;
use Site\Entity\MessagesLogEntity;
use Site\Entity\ProfileEntity;
use Site\Entity\UserEntity;
use Site\Model\Festival;
use Site\Model\GiftList;
use Site\Model\GiftPackage;
use Wechat\Entity\WechatMessageEntity;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Site\Model\User;
use Site\Model\Messages;
use Site\Controller\Result;
use Wechat\Model\Wechat;
use Zend\View\View;

class UserController extends BaseController{

    /**
     * @var User
     */
    public $userModel;

    /**
     * @var Wechat
     */
    public $wechat;


    public function __construct(Wechat $wechatModel ,User $userModel) {
        $this->wechat = $wechatModel;
        $this->userModel = $userModel;
    }

    public function indexAction(){
        $user = $this->authentication()->getIdentity();
        $view = new ViewModel();
        return $view;
    }

    /**
     * 用户协议：免责声明
     */
    public function agreementAction(){
        $user = $this->authentication()->getIdentity();
        if($user){
            return $this->redirect()->toUrl('/qoros');
        }
        $view = new ViewModel();
        return $view;
    }

    /**
     * 用户通过手机号来注册
     */
    public function registerAction(){
        $view = new ViewModel();
        $request = $this->getRequest();
        $user = $this->authentication()->getIdentity();
        if($user){
            return $this->redirect()->toUrl('/qoros');
        }else{
            //get user info
            if(parent::isWeixin() && !$request->isPost()){
                $code = $this->getParam('code', false);
                if(!$code){
                    $redirectUrl = $this->wechat->getOauthRedirect('http://'.$_SERVER['SERVER_NAME']
                        .'/test_qoros/user/completeInfo');
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
                $view->setVariables(array('openid'=>$tokenData['openid']));
            }
        }

        if ($request->isPost()) {
            $mobile = $this->getParam('mobile');
            $openid = $this->getParam('openid',false);
            $exsit = $this->userModel->getUserByMobile($mobile);
            if($exsit){
                //to login
                $this->login($mobile,false,false);

                $user = $this->authentication()->getIdentity();
                if(isset($openid) && $openid){
                    $userInfo = $this->wechat->getUserInfo($openid);
                    if(isset($userInfo['headimgurl'])){
                        $user->avatar = $userInfo['headimgurl'];
                    }
                    $user->open_id = $openid;
                    $this->userModel->updateUserInfo($user);
                }
                return new UnifyJsonModel();
            }else{
                return new ErrorJsonModel(1001,'手机号码不存在');
            }
        }
        return $view;
    }


    /**
     * 用户完善信息页面
     */
    public function completeInfoAction(){
        $user = $this->authentication()->getIdentity();
        $request = $this->getRequest();
        if ($request->isPost()) {
            //update user info
            $data = $this->getRequestData();
            $user->username = $data['username'];
            $user->state = $data['state'];
            $user->city = $data['city'];
            $user->addr_detail =  $data['addr_detail'];
            $user->postcode = $data['postcode'];
            $this->userModel->updateUserInfo($user);
            return $this->redirect()->toUrl('/test_qoros/');
//            return new UnifyJsonModel();
        }

        $view = new ViewModel();
        $view->setVariables(array('user'=>$user));
        return $view;
    }

    /**
     * 退出登陆
     */
    public function logoutAction(){
        $authService = $this->authentication()->getAuthService();
        $authService->clearIdentity();
        return $this->redirect()->toRoute('user',array('action'=>'register'));
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



    /**
     * 权限不足页面 403
     */
    public function unauthorizedAction(){
        $view = new ViewModel();
        $user = $this->authentication()->getIdentity();
        $redirect = $this->getParam('redirect','/qoros');
        if(!$user){
            return $this->redirect()->toUrl('/test_qoros/adm/admin-user/login?redirect='.urlencode($redirect));
        }
        $view->setVariables(array('redirectUri'=>$redirect));
        return $view;
    }



    public function createUserAction(){
        $user = new UserEntity();
        $user->username = 'admin';
        $user->mobile = '18989898989';
        $this->userModel->createUser($user);
    }



































//
//    public function loginAction() {
//        $request = $this->getRequest();
//        if ($request->isPost()) {
//            try {
//                $mobile = $this->getParam('mobile', null);
//                $password = $this->getParam('password', null);
//                if (empty($mobile) || empty($password)) {
//                    throw new BusinessException('用户名或密码不能为空');
//                }
//                $result = $this->login($mobile, $password);
//                switch ($result->getCode()) {
//                    case Result::SUCCESS:
//                        return $this->redirect()->toUrl('/adm');
//                    default:
//                        throw new BusinessException('用户名或密码错误');
//                }
//                return $this->redirect()->toUrl('/');
//            }catch (BusinessException $e) {
//                $this->flashMessenger()->addErrorMessage($e->getMessage());
//            }catch (\Exception $e) {
//                $this->flashMessenger()->addErrorMessage('系统错误');
//            }
//            return $this->redirect()->toRoute('user', array('action' => 'login'));
//        }
//
//        //toroute 跳转过来带的参数
//        $redirect = $this->getEvent()->getRouteMatch()->getParam('id',0);
//        if(!$redirect){
//            $redirect = $this->getParam('redirect', '/');
//        }
//
//        //如果用户是微信登陆，则获取基本信息
//        if(parent::isWeixin() && !$request->isPost()){
//            $code = $this->getParam('code', false);
//            if(!$code){
//                $redirectUrl = $this->wechat->getOauthRedirect('http://'.$_SERVER['SERVER_NAME'].'/user/login?redirect='.$redirect);
//                return $this->redirect()->toUrl($redirectUrl);
//            }
//
//            $tokenData = $this->wechat->getOauthData();
//            $user = $this->userModel->getByOpenId($tokenData['openid']);
//            $result = $this->login($user->mobile, null, false);
//
//            if ($result->getCode() == Result::SUCCESS) {
//                return $this->redirect()->toUrl('http://'.$_SERVER['SERVER_NAME'].$redirect);
////                return $this->redirect()->toRoute('user', array('action' => 'changeMobile'));
//            }else{
//                return $this->redirect()->toRoute('user', array('action' => 'bind'));
//            };
//        }
//        $view = new ViewModel();
//        $this->layout('layout/login');
//        return $view;
//    }
//
//    public function registerAction() {
//        $this->layout()->title = '绑定帐号';
//        $this->layout('layout/login');
//        $request = $this->getRequest();
//        if ($request->isPost()) {
//            try {
//                /* @var $filter PersonRegister */
//                $filter = $this->getServiceLocator()->get('Site\InputFilter\PersonRegister');
//                $inputFilter = $filter->getInputFilter();
//
//                $post = $this->getRequestData();
//                $post['username']=$post['mobile'];
//                $inputFilter->setData($post);
//                if (! $inputFilter->isValid()) {
//                    return new JsonModel(array('status'=>201,'message'=>$this->getErrorMessage($inputFilter)));
//                }
//
//                $user = new UserEntity();
//                $profile = new ProfileEntity();
//                $user->profile = $profile;
//                $user->exchangeArray($post);
//                $id = $this->userModel->createUser($user);
//                if (! $id) {
//                    return array('filter' => $inputFilter);
//                }
//                //注册成功后自动登陆一下
//                $this->login($user->mobile, $user->password, false);
//                return  new JsonModel(array('status'=>200,'message'=>'登陆成功'));
//
//            }catch (BusinessException $e) {
//                return new ErrorJsonModel($e->getCode(), $e->getMessage());
//            }catch(\Exception $e){
//                return new ErrorJsonModel(1001, '系统错误');
//            }
//            return $this->redirect()->toRoute('user', array('action' => 'login'));
//        }
//
//        $view = new ViewModel();
//        $redirect = $this->getParam('redirect','/user');
//        $view->setVariable('redirect',$redirect);
//        return $view;
//    }
//
//
//    public function forgetAction(){
//        $request = $this->getRequest();
//        if ($request->isPost()) {
//            try {
//                $mobile = $this->getParam('mobile', null);
//                $password = $this->getParam('password', null);
//                $existedUser = $this->userModel->getUserByMobile($mobile);
//                $existedUser->password = $password;
//                $this->userModel->changePassword($existedUser);
//                $this->flashMessenger()->addSuccessMessage('密码已经修改，请登陆');
//                return new UnifyJsonModel();
//            }catch (BusinessException $e) {
//                return new ErrorJsonModel($e->getCode(), $e->getMessage());
//            }catch (\Exception $e) {
//                return new ErrorJsonModel(1001, '系统错误');
//            }
//        }
//        $mobile = $this->getParam('mobile', null);
//        $view = new ViewModel();
//        $this->layout('layout/login');
//        $view->setVariable('mobile', $mobile);
//        return $view;
//    }
//
//    public function validateSmsCodeAction(){
//        try {
//            $mobile = $this->getParam('mobile', null);
//            $verifyCode = $this->getParam('verifyCode', null);
////            if (!DW_ENV_DEBUG) {
//            $this->userModel->validateVerifyCode($mobile, $verifyCode);
////            }
//            return new UnifyJsonModel();
//        }catch (BusinessException $e) {
//            return new ErrorJsonModel($e->getCode(), $e->getMessage());
//        }catch(\Exception $e){
//            return new ErrorJsonModel(1001, '系统错误');
//        }
//    }
//
//
//    /**
//     * 获取手机验证码
//     * @return JsonModel
//     * @throws BusinessException
//     */
//    public function getSmsCodeAction(){
//        try{
//            $mobile = $this->getParam("mobile", null);
//            $validOpenid = $this->getParam('valid_openid', 'true');
//            if (!isset($mobile) || empty($mobile)) {
//                throw new BusinessException("手机号码输入错误", 1301);
//            }
//            $existedUser = $this->userModel->getUserByMobile($mobile);
//            if (!isset($existedUser)) {
////                throw new BusinessException("该手机不存在，请联系客服添加", 1302);
//            }
//            if (!empty($existedUser->openid) && $validOpenid == 'true') {
//                throw new BusinessException("该手机号已经绑定，请先解除绑定", 1303);
//            }
//            //todo 测试环境省略此步骤
////            if (!DW_ENV_DEBUG) {
//                $this->userModel->getSmsVerifyCode($mobile);
////            }
//            return new UnifyJsonModel();
//        }catch (BusinessException $e) {
//            return new ErrorJsonModel($e->getCode(), $e->getMessage());
//        }catch(\Exception $e){
//            return new ErrorJsonModel(1001, '系统错误');
//        }
//    }
//
//    /**
//     * 注册后 登陆一下
//     * @param $name
//     * @param $pwd
//     * @param bool $checkPassword
//     * @return \Zend\Authentication\Result
//     */
//    protected function login($name ,$pwd, $checkPassword = true){
//        $authService = $this->authentication()->getAuthService();
//        $authService->clearIdentity();
//
//        $authAdapter = $this->authentication()->getAuthAdapter();
//        $authAdapter->setUsername($name);
//        $authAdapter->setPassword($pwd);
//        //when social login,pass check password
//        $authAdapter->setCheckPassword($checkPassword);
//
//        $result = $authService->authenticate();
//        //reset check password
//        $authAdapter->setCheckPassword(true);
//        return $result;
//    }
//
//
//    /**
//     * 权限不足页面 403
//     */
//    public function unauthorizedAction(){
//        $view = new ViewModel();
//        $user = $this->authentication()->getIdentity();
//        $redirect = $this->getParam('redirect','/');
//        if(!$user){
//            return $this->redirect()->toRoute('user',array('action'=>'login'),array('query'=>array('redirect'=>urlencode($redirect))));
//        }
//        $view->setVariables(array('redirectUri'=>$redirect));
//        return $view;
//    }
//
//    /**
//     * 推出登陆
//     */
//    public function logoutAction(){
//        $this->userModel->delWaitSendStatus($this->identity()->id);
//        $authService = $this->authentication()->getAuthService();
//        $authService->clearIdentity();
//        return $this->redirect()->toRoute('user',array('action'=>'login'));
//    }
//
//    /**
//     * 获取手机验证码
//     * @return JsonModel
//     * @throws BusinessException
//     */
//    public function getChangeMobileSmsCodeAction(){
//        try{
//            $mobile = $this->getParam("mobile", null);
//            if (!isset($mobile) || empty($mobile)) {
//                throw new BusinessException("手机号码输入错误");
//            }
//            $existedUser = $this->userModel->getUserByMobile($mobile);
//            if (isset($existedUser)) {
//                throw new BusinessException("该手机已存在");
//            }
//            //todo 测试环境省略此步骤
//            if (!DW_ENV_DEBUG) {
//                $this->userModel->getSmsVerifyCode($mobile);
//            }
//            return new UnifyJsonModel();
//        }catch (BusinessException $e) {
//            return new ErrorJsonModel($e->getCode(), $e->getMessage());
//        }catch(\Exception $e){
//            return new ErrorJsonModel(1001, '系统错误');
//        }
//    }
//
//    /**
//     * 更换手机号码
//     * @return JsonModel
//     */
//    public function changeMobileAction(){
//        $this->layout()->title = '更换手机号';
//        $request = $this->getRequest();
//        if ($request->isPost()) {
//            $mobile = $this->getParam("mobile");
//            $code = $this->getParam("code");
//            try {
//                $user = $this->userModel->getById($this->identity()->id);
//                $existedUser = $this->userModel->getUserByMobile($mobile);
//
//                if (isset($existedUser)) {
//                    throw new BusinessException("该手机已存在");
//                }
////                if (!DW_ENV_DEBUG) {
//                    $this->userModel->validateVerifyCode($mobile, $code);
////                }
//                $user->mobile = $mobile;
//                $user->bind_time = new \DateTime('now');
//                $this->userModel->updateUserInfo($user);
//                $this->identity()->mobile = $mobile;
//                //登录一下
//                $this->login($mobile, null, false);
//                return new UnifyJsonModel();
//            } catch (BusinessException $e) {
//                return new ErrorJsonModel($e->getCode(), $e->getMessage());
//            } catch (\Exception $e) {
//                return new ErrorJsonModel(1001, '系统错误');
//            }
//        }
//        $view = new ViewModel();
//        return $view;
//    }




}
