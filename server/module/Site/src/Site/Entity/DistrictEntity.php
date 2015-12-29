<?php
namespace Site\Entity;

use Doctrine\ORM\Mapping as ORM;
use Common\Entity\Entity;

/**
 * districts
 *
 * @ORM\Table(name="districts")
 * @ORM\Entity
 */
class DistrictEntity extends Entity
{
    const ALIAS = 'district';
    static $columns;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", nullable=false)
     */
    public $code;

    /**
     * @var string
     *
     * @ORM\Column(name="district", type="string", length=255, nullable=false)
     */
    public $district;

    /**
     * @var string
     *
     * @ORM\Column(name="pinyin", type="string", nullable=false)
     */
    public $pinyin;

    /**
     * @var string
     *
     * @ORM\Column(name="short", type="string", nullable=true)
     */
    public $short;
    /**
     * @var string
     *
     * @ORM\Column(name="parent_code", type="string", nullable=true)
     */
    public $parent_code;

    /**
     * @var DistrictEntity
     * @ORM\ManyToOne(targetEntity="Site\Entity\DistrictEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    public $parent;

    /**
     * @ORM\OneToMany(targetEntity="Site\Entity\DistrictEntity", mappedBy="parent")
     **/
    public $children;
    
    public function __construct() {
        parent::__construct();
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

    /**
     * 将获取的多表数据以alias.k=v形式， 以便做exchangeArray操作
     * @param $post
     * @return mixed
     * @author xlong
     */
    public function parsePostData($post){
        $taxProfileColumns = self::columns(self::ALIAS);
        foreach($taxProfileColumns as $key1 => $value1){
            foreach($post as $k1 => $v1){
                if($value1 == $k1){
                    unset($post[$k1]);
                    $post[$key1] = $v1;
                }
            }
        }
        return $post;
    }
    
    
}
