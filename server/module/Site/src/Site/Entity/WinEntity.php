<?php
namespace Site\Entity;

use Common\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use BjyAuthorize\Provider\Role\ProviderInterface as RoleProviderInterface;

/**
 * Activities
 *
 * @ORM\Table(name="wins")
 * @ORM\Entity
 */
class WinEntity extends Entity implements InputFilterAwareInterface
{
    const ALIAS = 'win';
    static $columns;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var AwardItemEntity
     * @ORM\OneToOne(targetEntity="Site\Entity\AwardItemEntity")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     **/
    public $item;

    /**
     * @var UserEntity
     * @ORM\OneToOne(targetEntity="Site\Entity\UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    public $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="activity_id", type="integer")
     */
    public $activityId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    public $created;

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
}
