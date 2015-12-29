<?php
namespace Site\Model;

use Common\Model\Model;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use Site\Entity\ActivityEntity;
use Site\Entity\AwardGoodsEntity;
use Zend\Paginator\Paginator;

class Activity extends Model {

    /**
     * @param $id
     * @return null|ActivityEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getById($id) {
        return $this->getEntityManager()->find('Site\Entity\ActivityEntity', $id);
    }

    public function getList(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('Site\Entity\ActivityEntity', 'a');
        return $qb->getQuery()->getResult();
    }


    public function save(ActivityEntity $activity){
        if(!$activity->startTime instanceof \DateTime){
            $activity->startTime = new \DateTime($activity->startTime);
        }
        if(!$activity->endTime instanceof \DateTime){
            $activity->endTime = new \DateTime($activity->endTime);
        }
        if(isset($activity->id)){
            //update
            $this->getEntityManager()->merge($activity);
            $this->getEntityManager()->flush($activity);
            return $activity;
        } else {
            if (! $activity->created) {
                $activity->created = new \DateTime('now');
            }
            $this->getEntityManager()->persist($activity);
            $this->getEntityManager()->flush($activity);
            return $activity;
        }
    }
}
?>
