<?php
namespace Admin\Controller;

use Common\Controller\Controller;
use Common\Exception\BusinessException;
use Common\View\ErrorJsonModel;
use Common\View\UnifyJsonModel;
use Site\Entity\UserEntity;
use Site\Model\Goods;
use Site\Model\User;
use Wechat\Model\Wechat;
use Zend\View\Model\ViewModel;

class GoodsController extends Controller {


    /**
     * @var Goods
     */
    protected $goodsModel;


    public function __construct(Goods $goodsModel) {
        $this->goodsModel = $goodsModel;
    }


    /**
     * 奖品管理
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction() {
        $activityId = $this->getParam('activityId', 0);
        $goodsList = $this->goodsModel->getList($activityId);
        $view = new ViewModel();
        $view->setVariables(array('goodsList'=>$goodsList, 'activityId' => $activityId));
        return $view;
    }

    public function setGoodsInventoryAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $inventory = $this->getParam('inventory', null);
                $goodsId = $this->getParam('goodsId', null);
                if(empty($goodsId)){
                    throw new BusinessException('数据错误', 1022);
                }
                $goods = $this->goodsModel->getById($goodsId);
                $goods->inventory = $inventory;
                $this->goodsModel->save($goods);
                return new UnifyJsonModel();
            }catch (BusinessException $e) {
                return new ErrorJsonModel($e->getCode(), $e->getMessage());
            }catch (\Exception $e) {
                return new ErrorJsonModel(1001, '系统错误');
            }
        }
    }
	
}
