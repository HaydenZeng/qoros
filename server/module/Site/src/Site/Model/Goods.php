<?php
namespace Site\Model;

use Common\Model\Model;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use Site\Entity\AwardGoodsEntity;
use Zend\Paginator\Paginator;

class Goods extends Model {

    /**
     * @param $id
     * @return null|AwardGoodsEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getById($id) {
        return $this->getEntityManager()->find('Site\Entity\AwardGoodsEntity', $id);
    }

    public function getList($activityId = false){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('goods')
            ->from('Site\Entity\AwardGoodsEntity', 'goods');
        if($activityId){
            $qb->where('goods.activityId = :activityId')
                ->setParameter('activityId', $activityId);
        }
        return $qb->getQuery()->getResult();
    }

   public function save(AwardGoodsEntity $goods){
       if(isset($goods->id)){
           //update
           $this->getEntityManager()->merge($goods);
           $this->getEntityManager()->flush($goods);
           return $goods;
       } else {
           if (! $goods->created) {
               $goods->created = new \DateTime('now');
           }
           $this->getEntityManager()->persist($goods);
           $this->getEntityManager()->flush($goods);
           return $goods;
       }
   }

}
?>
