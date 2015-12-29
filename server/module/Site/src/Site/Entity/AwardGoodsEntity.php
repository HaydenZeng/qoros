<?php
namespace Site\Entity;

use Common\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use BjyAuthorize\Provider\Role\ProviderInterface as RoleProviderInterface;

/**
 * AwardGoods
 *
 * @ORM\Table(name="award_goods")
 * @ORM\Entity
 */
class AwardGoodsEntity extends Entity implements InputFilterAwareInterface
{
    const ALIAS = 'goods';
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    public $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="inventory", type="integer")
     */
    public $inventory = 0;

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
