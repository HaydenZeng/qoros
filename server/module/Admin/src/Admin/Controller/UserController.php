<?php
namespace Admin\Controller;

use Common\Controller\Controller;
use Common\Entity\AttachmentEntity;
use Common\Exception\BusinessException;
use Common\Upload\ExcelFileHandler;
use Common\Upload\Uploader;
use Common\Upload\UploadHandler;
use Common\Upload\VoiceHandler;
use Common\View\ErrorJsonModel;
use Common\View\PartialViewModel;
use Common\View\UnifyJsonModel;
use Site\Controller\BaseController;
use Site\Controller\Result;
use Site\Entity\MessagesLogEntity;
use Site\Entity\UserPackageEntity;
use Site\Model\District;
use Site\Model\GiftList;
use Site\Model\Messages;
use Site\Entity\UserAddressEntity;
use Site\Model\GiftPackage;
use Wechat\Entity\WechatMessageEntity;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Site\Entity\UserEntity;
use Site\Entity\ProfileEntity;
use Site\Model\User;

class UserController extends \Site\Controller\UserController {

    /**
     * @var User
     */
    public $userModel;
    /**
     * @var District
     */
    public $districtModel;


    /**
     * 用户列表
     * @return \Zend\Http\Response|ViewModel
     */
    public function listAction(){
        $user = $this->authentication()->getIdentity();
        $page = $this->getEvent()->getRouteMatch()->getParam('page', 1);
        $size = $this->getEvent()->getRouteMatch()->getParam('size', 10);
        if(!$user || $user->role != UserEntity::ROLE_ADMIN){
            return $this->redirect()->toUrl('/qoros/admin/admin-user/login');
        }

        $paginator = $this->userModel->getUsers(null,$page,$size);
        $view = new ViewModel();
        $view->setVariables(array('paginator'=>$paginator));
        return $view;
    }


    /**
     * 导入用户
     * @author bzhang
     */
    public function importUserAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {

            $user = $this->authentication()->getIdentity();
            if(empty($_FILES['userList'])){
                throw new BusinessException('请选择用户列表文件', 1021);
            }

            $fileHandler = new ExcelFileHandler($user->id);
            $uploader = new Uploader($fileHandler);
            $data = $uploader->processUpload($_FILES['userList']);
            $attachment = new AttachmentEntity();
            $attachment->exchangeArray($data);
            $attachment->filepath = str_replace("\\", "/", $attachment->filepath);
            if (!isset($attachment->filename) || !isset($attachment->filepath)) {
                throw new BusinessException('用户列表Excel导入失败',1303);
            }

            $userArr = $this->userModel->getUserAndAddressFromExcel($attachment);
            $this->userModel->batchCreateUsers($userArr);
            return new UnifyJsonModel();

        }
        $view = new ViewModel();
        return $view;
    }


    /**
     * 管理员登陆
     */
    public function loginAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $mobile = $this->getParam('mobile', null);
                $password = $this->getParam('password', null);
                if (empty($mobile) || empty($password)) {
                    throw new BusinessException('用户名或密码不能为空');
                }
                $result = $this->login($mobile, $password);
                switch ($result->getCode()) {
                    case Result::SUCCESS:
                        return $this->redirect()->toUrl('/adm');
                    default:
                        throw new BusinessException('用户名或密码错误');
                }
                return $this->redirect()->toUrl('/');
            }catch (BusinessException $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
            return $this->redirect()->toUrl('/qoros/adm/admin-user/login');
        }

        //toroute 跳转过来带的参数
        $redirect = $this->getEvent()->getRouteMatch()->getParam('id',0);
        if(!$redirect){
            $redirect = $this->getParam('redirect', '/');
        }

        $view = new ViewModel();
        $this->layout('layout/login');
        return $view;
    }




}
