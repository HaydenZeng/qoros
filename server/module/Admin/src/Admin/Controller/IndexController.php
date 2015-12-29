<?php
namespace Admin\Controller;

use Common\Controller\Controller;
use Site\Entity\UserEntity;
use Site\Model\Activity;
use Site\Model\Share;
use Site\Model\User;
use Wechat\Model\Wechat;
use Zend\View\Model\ViewModel;

class IndexController extends Controller {


    /**
     * @var Wechat
     */
    protected $wechat;
    /**
     * @var User
     */
    protected $userModel;

    /**
     * @var Share
     */
    public $share;


    /**
     * @var Activity
     */
    public $activity;


    public function __construct(Wechat $wechatModel ,User $userModel,Share $share,Activity $activity) {
        $this->wechat = $wechatModel;
        $this->userModel = $userModel;
        $this->share = $share;
        $this->activity = $activity;
    }


    /**
     * 后台主页
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction() {
        $user = $this->authentication()->getIdentity();
        if(!$user || $user->role != UserEntity::ROLE_ADMIN){
            return $this->redirect()->toUrl('/admin/admin-user/login');
        }

        return $this->redirect()->toUrl('/adm/admin-user/list');
    }

    public function shareListAction(){
        $view = new ViewModel();
        $page = $this->getEvent()->getRouteMatch()->getParam('page', 1);
        $size = $this->getEvent()->getRouteMatch()->getParam('size', 10);
        $activityId = $this->getParam('activityId',0);
        $paginator = $this->share->getList($activityId,$page,$size);
        $activities = array();
        foreach($paginator as $share){
            $activities[$share->id] = $this->activity->getById($share->activityId);
        }
        $view->setVariables(array('paginator' => $paginator, 'activities' => $activities,'activityId'=>$activityId,
            'page'=>$page));
        return $view;
    }

	
}
