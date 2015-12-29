<?php
namespace Site\Model;

use Common\Model\Model;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use Site\Entity\AwardItemEntity;
use Site\Entity\UserEntity;
use Site\Entity\WinEntity;
use Zend\Paginator\Paginator;

class Award extends Model {

    /**
     * @param $id
     * @return null|AwardItemEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getById($id) {
        return $this->getEntityManager()->find('Site\Entity\AwardItemEntity', $id);
    }

    public function getList($activityId){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('award')
            ->from('Site\Entity\AwardItemEntity', 'award')
            ->where('award.activityId = :activityId')
            ->setParameter('activityId', $activityId);

        return $qb->getQuery()->getResult();
    }

    public function save(AwardItemEntity $award){
        if(isset($award->id)){
            //update
            $this->getEntityManager()->merge($award);
            $this->getEntityManager()->flush($award);
            return $award;
        } else {
            if (! $award->created) {
                $award->created = new \DateTime('now');
            }
            $this->getEntityManager()->persist($award);
            $this->getEntityManager()->flush($award);
            return $award;
        }
    }

    /**
     * @param $activityId
     * @param $goodsId
     * @return AwardItemEntity | null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getByActivityIdAndGoodsId($activityId, $goodsId){
        $goods = $this->getEntityManager()->getReference('Site\Entity\AwardGoodsEntity', $goodsId);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('award')
            ->from('Site\Entity\AwardItemEntity', 'award')
            ->where('award.activityId = :activityId AND award.goods = :goods')
            ->setParameter('activityId', $activityId)
            ->setParameter('goods', $goods)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * 插入中奖纪录
     * @param WinEntity $win
     * @return WinEntity
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveWin(WinEntity $win){
        if(!empty($win->user) && is_numeric($win->user)){
            $win->user = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $win->user);
        }
        if(!empty($win->item) && is_numeric($win->item)){
            $win->item = $this->getEntityManager()->getReference('Site\Entity\AwardItemEntity', $win->item);
        }
        if(isset($win->id)){
            //update
            $this->getEntityManager()->merge($win);
            $this->getEntityManager()->flush($win);
            return $win;
        } else {
            if (! $win->created) {
                $win->created = new \DateTime('now');
            }
            $this->getEntityManager()->persist($win);
            $this->getEntityManager()->flush($win);
            return $win;
        }
    }

    public function getWinList($activityId = false,$page = 1, $size = 10){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('win')
            ->from('Site\Entity\WinEntity', 'win')
            ->where('win.item is not null');

        if($activityId){
            $qb->andWhere('win.activityId = :activityId')
                ->setParameter('activityId',$activityId);
        }
        $paginator = new Paginator(new DoctrinePaginator(new OrmPaginator($qb->getQuery())));
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage($size);
        return $paginator;
    }

    /**
     * 判断用户是否已经抽过奖
     * @param $activityId
     * @param $userId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function isWin($activityId, $userId){
        $user = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $userId);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('win')
            ->from('Site\Entity\WinEntity', 'win')
            ->where('win.user = :user AND win.activityId = :activityId')
            ->setParameter('activityId', $activityId)
            ->setParameter('user', $user)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}
?>
