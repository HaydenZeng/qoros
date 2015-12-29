<?php
namespace Site\Model;

use Common\Model\Model;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use Site\Entity\AwardGoodsEntity;
use Site\Entity\ShareEntity;
use Zend\Paginator\Paginator;

class Share extends Model {

    /**
     * @param $id
     * @return null|ShareEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getById($id) {
        return $this->getEntityManager()->find('Site\Entity\ShareEntity', $id);
    }

    /**
     * @param $activityId
     * @param $userId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getByActivityIdAndUserId($activityId, $userId){
        $user = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $userId);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('share')
            ->from('Site\Entity\ShareEntity', 'share')
            ->where('share.activityId = :activityId AND share.user = :user')
            ->setParameter('activityId', $activityId)
            ->setParameter('user', $user)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getList($activityId = false ,$page = 1, $size = 10){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('share')
            ->from('Site\Entity\ShareEntity', 'share');

        if($activityId){
            $qb->andWhere('share.activityId = :activityId')
                ->setParameter('activityId',$activityId);
        }

        $paginator = new Paginator(new DoctrinePaginator(new OrmPaginator($qb->getQuery())));
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage($size);
        return $paginator;
    }

   public function save(ShareEntity $share){

       if(!empty($share->user) && is_numeric($share->user)){
           $share->user = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $share->user);
       }
       if(isset($share->id)){
           //update
           $this->getEntityManager()->merge($share);
           $this->getEntityManager()->flush($share);
           return $share;
       } else {
           if (! $share->created) {
               $share->created = new \DateTime('now');
           }
           $this->getEntityManager()->persist($share);
           $this->getEntityManager()->flush($share);
           return $share;
       }
   }

}
?>
