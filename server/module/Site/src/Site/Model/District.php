<?php
namespace Site\Model;

use Common\Model\Model;
use Site\Entity\DistrictEntity;

class District extends Model {
    
    public function getCitysA2Z(){
        $districts = array();

        $sql = "select * from districts where parent_code <> 1 and code like '%00' ORDER BY convert( district USING gbk ) COLLATE gbk_chinese_ci ASC";
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        foreach($stmt->fetchAll() as $node){
            $district = new DistrictEntity();
            $node = $district->parsePostData($node);
            $district->exchangeArray($node);

            $districts[substr($district->pinyin, 0, 1)][] = $district;
        }
        return $districts;
    }

    /**
     * 根据上一级地址获取所有子地址
     * @param $parentCode
     * @return array
     */
    public function getDistricts($parentCode){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select("d")
            ->from('Site\Entity\DistrictEntity', "d")
            ->where('d.parent_code = :parentCode')
            ->setParameter('parentCode', $parentCode)
            ->getQuery();
        $districts = $query->getResult();
        return $districts;
    }

    /**
     * 根据code获取地址
     * @param $code
     * @return null | \Site\Entity\DistrictEntity
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDistrictByCode($code){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select("dis")
            ->from('Site\Entity\DistrictEntity', "dis")
            ->where('dis.code = :code')
            ->setParameter('code',$code);
        $district = $qb->getQuery()->getOneOrNullResult();
        return $district;
    }

    public function getJsLocation(){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query1 = $qb->select("d")
            ->from('Site\Entity\DistrictEntity', "d")
            ->where('d.parent_code = 0')
            ->getQuery();
        $provinces = $query1->getResult();

        $location = array();
        $location['0']['0'] = '--请选择--';
        foreach($provinces as $province){
            $location['0'][$province->code] = $province->district;

            $sql = "select * from districts where parent_code=:parentCode";
            $params = array("parentCode" => $province->code);
            $em = $this->getEntityManager();
            $stmt1 = $em->getConnection()->prepare($sql);
            $stmt1->execute($params);

            $city = new DistrictEntity();
            foreach($stmt1->fetchAll() as $i => $node1){
                if($i == 0){
                    $location['0,'.$province->code]['0'] = '--请选择--';
                }
                $node1 = $city->parsePostData($node1);
                $city->exchangeArray($node1);

                $location['0,'.$province->code][$city->code] = $city->district;

                $sql = "select * from districts where parent_code=:parentCode";
                $params = array("parentCode" => $city->code);
                $em = $this->getEntityManager();
                $stmt2 = $em->getConnection()->prepare($sql);
                $stmt2->execute($params);

                $district = new DistrictEntity();
                foreach($stmt2->fetchAll() as $j => $node2){
                    if($j == 0){
                        $location['0,'.$province->code.','.$city->code]['0'] = '--请选择--';
                    }
                    $node2 = $district->parsePostData($node2);
                    $district->exchangeArray($node2);

                    $location['0,'.$province->code.','.$city->code][$district->code] = $district->district;
                }
            }
        }

        return $location;
    }

    public function getDistrictByName($cityName) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select("dis")
            ->from('Site\Entity\DistrictEntity', "dis")
            ->where("dis.district LIKE :district")
            ->setParameter('district', $cityName . '%')
            ->setMaxResults(1);
        $district = $qb->getQuery()->getOneOrNullResult();
        return $district;
    }
}
?>
