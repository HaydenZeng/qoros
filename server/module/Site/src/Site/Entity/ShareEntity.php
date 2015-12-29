<?php
namespace Site\Entity;

use Common\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use BjyAuthorize\Provider\Role\ProviderInterface as RoleProviderInterface;

/**
 * Shares
 *
 * @ORM\Table(name="shares")
 * @ORM\Entity
 */
class ShareEntity extends Entity implements InputFilterAwareInterface
{


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var UserEntity
     * @ORM\OneToOne(targetEntity="Site\Entity\UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    public $user;

    /**
     * @var string
     *
     * @ORM\Column(name="open_id", type="string", length=255)
     */
    public $openid;

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
