<?php
namespace Admin\Controller;

use Common\Controller\Controller;
use Common\Exception\BusinessException;
use Common\View\ErrorJsonModel;
use Common\View\UnifyJsonModel;
use Site\Entity\UserEntity;
use Site\Model\Activity;
use Site\Model\Award;
use Site\Model\Goods;
use Site\Model\User;
use Wechat\Model\Wechat;
use Zend\View\Model\ViewModel;

class AwardController extends Controller {


    /**
     * @var Award
     */
    protected $awardModel;

    /**
     * @var Activity
     */
    protected $activityModel;


    public function __construct(Award $awardModel,Activity $activityModel) {
        $this->awardModel = $awardModel;
        $this->activityModel = $activityModel;
    }


    /**
     * 奖项管理
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction() {
        $view = new ViewModel();
        $activities = $this->activityModel->getList();
        $view->setVariables(array('activities' => $activities));
        return $view;
    }

    public function setAwardAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $itemId = $this->getParam('itemId', null);
                $count = $this->getParam('count', null);
                $rate = $this->getParam('rate', null);
                if(empty($itemId)){
                    throw new BusinessException('数据错误', 1022);
                }
                $item = $this->awardModel->getById($itemId);
                $item->count = $count;
                $item->rate = round($rate / 100, 2);
                $this->awardModel->save($item);

                return new UnifyJsonModel();
            }catch (BusinessException $e) {
                return new ErrorJsonModel($e->getCode(), $e->getMessage());
            }catch (\Exception $e) {
                return new ErrorJsonModel(1001, '系统错误');
            }
        }
        $activityId = $this->getEvent()->getRouteMatch()->getParam('id', 0);
        $itemList = $this->awardModel->getList($activityId);
        $view = new ViewModel();
        $view->setVariables(array('itemList' => $itemList, 'activityId' => $activityId));
        return $view;
    }


    public function setActivityTimeAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $start_time = $this->getParam('start_time', null);
                $end_time = $this->getParam('end_time', null);
                $activityId = $this->getParam('activityId', null);
                if(empty($activityId)){
                    throw new BusinessException('数据错误', 1022);
                }
                $activity = $this->activityModel->getById($activityId);
                $activity->startTime = $start_time;
                $activity->endTime = $end_time;
                $this->activityModel->save($activity);

                return new UnifyJsonModel();
            }catch (BusinessException $e) {
                return new ErrorJsonModel($e->getCode(), $e->getMessage());
            }catch (\Exception $e) {
                return new ErrorJsonModel(1001, '系统错误');
            }
        }
    }


    /**
     * 中奖列表
     * @return \Zend\Http\Response|ViewModel
     */
    public function winListAction() {
        $view = new ViewModel();
        $activityId = $this->getParam('activityId',0);
        $page = $this->getEvent()->getRouteMatch()->getParam('page', 1);
        $size = $this->getEvent()->getRouteMatch()->getParam('size', 10);
        $paginator = $this->awardModel->getWinList($activityId,$page,$size);
        $activities = array();
        foreach($paginator as $win){
            $activities[$win->id] = $this->activityModel->getById($win->activityId);
        }

        $view->setVariables(array('paginator' => $paginator, 'activities' => $activities ,'activityId'=>$activityId,
            'page'=>$page));
        return $view;
    }
}
