<?php
namespace Site\Entity;

use Common\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use BjyAuthorize\Provider\Role\ProviderInterface as RoleProviderInterface;

/**
 * Users
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class UserEntity extends Entity implements InputFilterAwareInterface,RoleProviderInterface
{
    const ALIAS = 'user';
    static $columns;

    const DEFAULT_PASSWORD = '111111';

    const ROLE_GUEST = 'guest';
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';

    const NOT_DELETE = 0 ;          //未删除
    const DELETE = 1 ;              //已删除

    protected $inputFilter;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     */
    public $username;

    /**
     * @var string
     *
     * @ORM\Column(name="open_id", type="string", length=255)
     */
    public $open_id;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=45, nullable=false)
     */
    public $mobile;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     */
    public $password;


    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=45, nullable=true)
     */
    public $status;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=45, nullable=false)
     */
    public $role;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=45)
     */
    public $state;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=45)
     */
    public $city;

    /**
     * @var string
     *
     * @ORM\Column(name="district", type="string", length=45)
     */
    public $district;

    /**
     * @var string
     *
     * @ORM\Column(name="addr_detail", type="string", length=255)
     */
    public $addr_detail;

    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=45)
     */
    public $postcode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    public $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    public $updated;
    /**
     * @var \is_delete
     *
     * @ORM\Column(name="is_delete", type="integer", nullable=false)
     */
    public $is_delete = self::NOT_DELETE;

    /**
     * @var \is_delete
     *
     * @ORM\Column(name="is_luck_draw_1", type="integer", nullable=false)
     */
    public $is_luck_draw_1;

    /**
     * @var \is_delete
     *
     * @ORM\Column(name="is_luck_draw_2", type="integer", nullable=false)
     */
    public $is_luck_draw_2;


    /**
     * @var \is_delete
     *
     * @ORM\Column(name="is_luck_draw_3", type="integer", nullable=false)
     */
    public $is_luck_draw_3;

    /**
     * @var \is_delete
     *
     * @ORM\Column(name="is_luck_draw_4", type="integer", nullable=false)
     */
    public $is_luck_draw_4;

    /**
     * @var \avatar
     *
     * @ORM\Column(name="avatar",type="string", length=255)
     */
    public $avatar;




    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("not used");
    }

    /* (non-PHPdoc)
     * @see \BjyAuthorize\Provider\Role\ProviderInterface::getRoles()
     */
    public function getRoles()
    {
        if (isset($this->role)) {
            return array($this->role);
        }else {
            return array('guest');
        }
    }



    /**
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $associatedData = $this->inflate($data);
        if (isset($associatedData[self::ALIAS])) {
            parent::exchangeArray($associatedData[self::ALIAS]);
        } else {
            parent::exchangeArray($data);
        }
    }

    public function getAddress(){
        return $this->state.$this->city.$this->addr_detail;
    }
}
