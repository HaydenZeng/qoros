<?php
namespace Site\Model;

use Common\Entity\AttachmentEntity;
use Common\Exception\BusinessException;
use Common\Model\Model;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use PHPExcel;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Reader_Excel5;
use PHPExcel_Writer_Excel5;
use Site\Entity\UserAddressEntity;
use Site\Entity\UserEntity;
use Site\Entity\ProfileEntity;
use Site\Entity\VerifyCodeEntity;
use Site\Entity\UserPackageEntity;
use Zend\Paginator\Paginator;
use Zend\Crypt\Password\Bcrypt;
use Common\HttpApi\SmsApi;

class User extends Model {

    /**
     * @param mixed $search
     * @param int $page
     * @param int $size
     * @return Paginator
     */
    public function getUsers($search = null, $page = 0, $size = 10) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb ->select('user')
            ->from('Site\Entity\UserEntity', 'user')
            ->where('user.is_delete = :is_delete')
            ->setParameter('is_delete',UserEntity::NOT_DELETE)
            ->orderBy('user.created', 'DESC');
        if(!empty($search)){
            if(is_numeric($search)){
                $qb->andWhere("user.mobile like '%".$search."%'");
            } else {
                $qb->andWhere("user.username like '%".$search."%'");
            }
        }

        if($size == 0){
            return $qb->getQuery()->getResult();
        }

        $paginator = new Paginator(new DoctrinePaginator(new OrmPaginator($qb->getQuery())));
        $paginator->setCurrentPageNumber($page)
            ->setItemCountPerPage($size);
        return $paginator;
    }


    public function changePassword(UserEntity $user){
        $bcrypt = new Bcrypt();
        $user->password = $bcrypt->create($user->password);
        $this->saveUser($user);
        return true;
    }


    /**
     * @param $id
     * @return UserEntity
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('user')->from('Site\Entity\UserEntity', 'user');
        $qb->where('user.id = :id');
        $qb->setParameter('id' , $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param UserEntity $user
     * @return UserEntity
     * @throws BusinessException
     * @throws \Exception
     */
    public function createUser(UserEntity $user) {
        if (isset($user->creator) && is_numeric($user->creator)) {
            $user->creator = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $user->creator);
        }
        try {
//            $this->getEntityManager()->getConnection()->beginTransaction();
            $user = $this->saveUser($user);
//            $this->getEntityManager()->commit();
            return $user;
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }

    /**
     * @param array $users
     * @return bool
     * @throws \Exception
     */
    public function batchCreateUsers(array $users){
        try {
//            $this->getEntityManager()->getConnection()->beginTransaction();
            foreach ($users as $user) {
                /**
                 * @var \Site\Entity\UserEntity $user
                 */
                $this->createUser($user);
            }
//            $this->getEntityManager()->commit();
            return true;
        }catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }

    /**
     * @param ProfileEntity $profile
     * @return ProfileEntity
     */
    protected function saveProfile(ProfileEntity $profile) {
        if(isset($profile->id)){
            //update
            if (is_string($profile->birthday)) {
                $profile->birthday = new \DateTime($profile->birthday);
            }
            $this->getEntityManager()->merge($profile);
            $this->getEntityManager()->flush($profile);
            return $profile;
        } else {
            if (! $profile->created) {
                $profile->created = new \DateTime('now');
            }
            if(! $profile->birthday || $profile->birthday == null){
                $profile->birthday = new \DateTime('now');
            }else if (is_string($profile->birthday)) {
                $profile->birthday = new \DateTime($profile->birthday);
            }
            $this->getEntityManager()->persist($profile);
            $this->getEntityManager()->flush($profile);
            return $profile;
        }
    }

    /**
     * @param UserEntity $user
     * @return UserEntity
     * @throws BusinessException
     */
    protected function saveUser(UserEntity $user) {
        if(isset($user->id)){
            $user->updated = new \DateTime('now');
            //update
            $this->getEntityManager()->merge($user);
            $this->getEntityManager()->flush($user);
            return $user;
        } else {
            if (! $user->created) {
                $user->created = new \DateTime('now');
            }
            if ($this->findByMobile($user->mobile)) {
                return true;
//                throw new BusinessException("手机号已经存在", 1037);
            }
            if (!isset($user->role)) {
                $user->role = UserEntity::ROLE_USER;
            }
            if (!isset($user->password)) {
                $user->password = UserEntity::DEFAULT_PASSWORD;
            }
            $bcrypt = new Bcrypt();
            $user->password = $bcrypt->create($user->password);

            $user->updated = new \DateTime('now');
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush($user);
            return $user;
        }
    }

    /**
     * get user by username
     * @param string $mobile
     * @return UserEntity
     */
    public function findByMobile($mobile) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
            ->from('Site\Entity\UserEntity', 'u')
            ->where('u.mobile = :mobile')
            ->setParameter('mobile', $mobile);
        $user = $qb->getQuery()->getOneOrNullResult();
        return $user;
    }


    public function getByIds(array $ids ,$page=1,$size = 10) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('user')->from('Site\Entity\UserEntity', 'user');
        $qb->where('user.id in (:id)');
        $qb->setParameter('id' , $ids);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param UserAddressEntity $userAddress
     * @return UserAddressEntity
     * @throws BusinessException
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveUserAddress(UserAddressEntity $userAddress) {
        $inputFilter = $userAddress->getInputFilter();
        $inputFilter->setData($userAddress->getArrayCopy());
        if (! $inputFilter->isValid()) {
            throw new BusinessException($this->getErrorMessage($inputFilter), 1120);
        }

        if (is_numeric($userAddress->user)) {
            $user = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $userAddress->user);
            $userAddress->user = $user;
        }

        if(isset($userAddress->id)){
            $userAddress->updated = new \DateTime('now');
            //update
            $this->getEntityManager()->merge($userAddress);
            $this->getEntityManager()->flush();
            return $userAddress;
        } else {
            if (! $userAddress->created) {
                $userAddress->created = new \DateTime('now');
            }
            if (empty($userAddress->origin)) {
                $userAddress->origin = UserAddressEntity::ORIGIN_ADMIN_CREATED;
            }
            if (!empty($userAddress->user) && is_numeric($userAddress->user)) {
                $userAddress->user = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $userAddress->user);
            }
            if (empty($userAddress->isDefault)) {
                $userAddress->isDefault = false;
            }
            $userAddress->updated = new \DateTime('now');
            $this->getEntityManager()->persist($userAddress);
            $this->getEntityManager()->flush();
            return $userAddress;
        }
    }

    /**
     * @param $userId
     * @return array | \Site\Entity\UserAddressEntity
     * @throws \Doctrine\ORM\ORMException
     */
    public function getUserAddresses($userId, $default = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ua')->from('Site\Entity\UserAddressEntity', 'ua');
        $user = $this->getEntityManager()->getReference('Site\Entity\UserEntity', $userId);
        $qb->where('ua.user = :user');
        $qb->setParameter('user' , $user);
        if($default){
            $qb->andWhere('ua.isDefault = :isDefault');
            $qb->setParameter('isDefault' , true);

            return $qb->getQuery()->getOneOrNullResult();
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $id
     * @return UserAddressEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getUserAddressById($id) {
        return $this->getEntityManager()->find('Site\Entity\UserAddressEntity', $id);
    }

    /**
     * 删除用户地址
     */
    public function deleteAddress($id){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('Site\Entity\UserAddressEntity', 'ua')
            ->where('ua.id = :id')
            ->setParameter('id', $id);
        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * 根据手机号获得用户
     * @param $mobile
     * @return UserEntity
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserByMobile($mobile) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('user')->from('Site\Entity\UserEntity', 'user');
        $qb->where('user.mobile = :mobile')
            ->setParameter('mobile', $mobile)
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }



    public function updateUserInfo(UserEntity $user){
        $user->updated = new \DateTime("now");
        $this->getEntityManager()->merge($user);
        $this->getEntityManager()->flush();
        return true;
    }


    /**
     * 根据上传的excel文件获取用户列表信息
     * @param AttachmentEntity $attachment
     * @param int $creatorId
     * @return array
     * @throws BusinessException
     */
    public function getUserAndAddressFromExcel(AttachmentEntity $attachment) {
        require_once(ROOT . "/vendor/phpoffice/phpexcel/Classes/PHPExcel.php");

        $filePath = ROOT . '/public' . $attachment->getUrl();
        $PHPExcel = new PHPExcel();

        /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                throw new BusinessException('请导入excel格式的报表', 1022);
            }
        }

        $PHPExcel = $PHPReader->load($filePath);
        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
        $userArr = array();
        /**从第二行记录开始读取*/
        for($currentRow = 2;$currentRow <= $allRow;$currentRow++){
            $user = new UserEntity();
            /** 根据导出的Excel格式 */
            $mobile = $currentSheet->getCellByColumnAndRow(1, $currentRow)->getFormattedValue();
            if(empty($mobile)){
                break;
            }
            $existUser = $this->getUserByMobile($mobile);
            if(!empty($existUser)){
                $user = $existUser;//更新用户
            } else {
                $user->mobile = $mobile;//新增用户
            }
            $user->username = $currentSheet->getCellByColumnAndRow(0, $currentRow)->getFormattedValue();
            $user->is_luck_draw_1 = $currentSheet->getCellByColumnAndRow(2, $currentRow)->getFormattedValue();
            $user->is_luck_draw_2 = ($currentSheet->getCellByColumnAndRow(3, $currentRow)->getFormattedValue());
            $user->is_luck_draw_3 = $currentSheet->getCellByColumnAndRow(4, $currentRow)->getFormattedValue();
            $user->is_luck_draw_4 = $currentSheet->getCellByColumnAndRow(5, $currentRow)->getFormattedValue();
            $userArr[] = $user;
        }

        return $userArr;
    }

    /**
     * 批量保存从excel获取的用户信息和地址
     * @param array $addressArr
     * @return bool
     * @throws \Exception
     */
    public function batchCreateUserAndAddress(array $addressArr){
        try {
            $this->getEntityManager()->getConnection()->beginTransaction();
            foreach ($addressArr as $address) {
                /**
                 * @var \Site\Entity\UserAddressEntity $address
                 */
                $user = $address->user;
                $inputFilter = $user->getInputFilter();
                $inputFilter->setData($user->getArrayCopy());
                if (! $inputFilter->isValid()) {
                    throw new BusinessException($this->getErrorMessage($inputFilter), 1120);
                }
                $profile = isset($user->profile) ? $user->profile : new ProfileEntity();
                $inputFilter = $profile->getInputFilter();
                $inputFilter->setData($profile->getArrayCopy());
                if (! $inputFilter->isValid()) {
                    throw new BusinessException($this->getErrorMessage($inputFilter), 1120);
                }
                $user->profile = null;
                $user = $this->saveUser($user);
                $profile->setUser($user);
                $this->saveProfile($profile);
                $address->user = $user;
                $this->saveUserAddress($address);
            }
            $this->getEntityManager()->commit();
            return true;
        }catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }
    }


    /**
     * 根据openid获取用户信息
     */
    public function getByOpenId($openId){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('user')->from('Site\Entity\UserEntity', 'user');
        $qb->where('user.open_id = :open_id')
            ->setParameter('open_id', $openId)
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }


    /**
     * 发送手机短信
     */
    public function sendSms($cellphone,$content){
        if($cellphone == null){
            return false;
        }
        try{
            $this->smsApi->sendTextVerify($content ,$cellphone);
        }catch (\Exception $e){
            //todo better to wright log
        }
    }


}
?>
