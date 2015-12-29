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
 * @ORM\Table(name="award_items")
 * @ORM\Entity
 */
class AwardItemEntity extends Entity implements InputFilterAwareInterface
{
    const ALIAS = 'item';
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
     * @var AwardGoodsEntity
     * @ORM\OneToOne(targetEntity="Site\Entity\AwardGoodsEntity")
     * @ORM\JoinColumn(name="goods_id", referencedColumnName="id")
     **/
    public $goods;

    /**
     * @var integer
     *
     * @ORM\Column(name="activity_id", type="integer")
     */
    public $activityId;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer")
     */
    public $count;

    /**
     * @var string
     *
     * @ORM\Column(name="rate", type="decimal", precision=3, scale=2)
     */
    public $rate;

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
