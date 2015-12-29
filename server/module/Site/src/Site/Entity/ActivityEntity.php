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
 * @ORM\Table(name="activities")
 * @ORM\Entity
 */
class ActivityEntity extends Entity implements InputFilterAwareInterface
{
    const ALIAS = 'activity';

    const TYPE_NORMAL = 'normal';
    const TYPE_DRAW = 'draw';

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
     * @ORM\Column(name="title", type="string", length=255)
     */
    public $title;

    /**
     * @var type
     *
     * @ORM\Column(name="type", type="string", length=45)
     */
    public $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime")
     */
    public $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="datetime")
     */
    public $endTime;

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
