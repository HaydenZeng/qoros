<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Site\Controller;

use Common\View\ErrorJsonModel;
use Common\View\UnifyJsonModel;
use Site\Entity\UserEntity;
use Site\Entity\WinEntity;
use Site\Model\Award;
use Zend\View\Model\ViewModel;
use Site\Model\User;

class PrizeController extends BaseController{

    /**
     * @var Award
     */
    protected $awardModel;
    /**
     * @var User
     */
    protected $userModel;

    public function __construct(Award $awardModel, User $userModel) {
        $this->awardModel = $awardModel;
        $this->userModel = $userModel;
    }

    public function indexAction() {
        $view = new ViewModel();
        $activityId = $this->getParam('activityId', 2);
        $view->setVariables(array('activityId'=>$activityId));
        return $view;
    }

    /**
     * ajax获取随机的抽奖奖项
     * 从长度为100的数组中随机一个值
     */
    public function RandAwardAction(){
        $activityId = $this->getParam('activityId', null);
        $user = $this->userModel->getById($this->identity()->id);
        $is_lucky = 0;
        switch($activityId){
            case 2:
                $is_lucky = $user->is_luck_draw_1;
                break;
            case 3:
                $is_lucky = $user->is_luck_draw_2;
                break;
            case 4:
                $is_lucky = $user->is_luck_draw_3;
                break;
            case 6:
                $is_lucky = $user->is_luck_draw_4;
                break;
        }
        if($is_lucky == 0){
            return new ErrorJsonModel(1022, '抱歉！您不满足参与该活动的条件');
        }
        $existed = $this->awardModel->isWin($activityId, $this->identity()->id);
        if(!empty($existed)){
            return new ErrorJsonModel(1021, '对不起！您已经参与过该活动');
        }
        //随机奖项
        $items = $this->awardModel->getList($activityId);
        $randArr = array();
        foreach($items as $item){
            if($item->count != 0){
                $randArr[] = $item->goods->id;
            }
        }
        if(count($randArr) == 0){
            $randArr[] = 7;
            $randArr[] = 8;
            $randArr[] = 9;
        }
        $r = rand(0, count($randArr) - 1);
        $goodsId = $randArr[$r];

        if($goodsId < 7){
            //奖项数量-1
            $item = $this->awardModel->getByActivityIdAndGoodsId($activityId, $goodsId);
            $item->count -= 1;
            $this->awardModel->save($item);
            //添加到中奖列表
            $win = new WinEntity();
            $win->item = $item;
            $win->user = $this->identity()->id;
            $win->activityId = $activityId;
            $this->awardModel->saveWin($win);
        } else {
            //添加到中奖列表
            $win = new WinEntity();
            $win->item = null;
            $win->activityId = $activityId;
            $win->user = $this->identity()->id;
            $this->awardModel->saveWin($win);
        }

        return new UnifyJsonModel(array($goodsId));
    }

}
