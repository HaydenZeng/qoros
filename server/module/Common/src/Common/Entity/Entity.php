<?php
namespace Common\Entity;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\ObjectProperty as Hydrator;
use ArrayObject;

class Entity implements InputFilterAwareInterface, \IteratorAggregate{
    /**
     *
     * @var Hydrator
     */
    protected $hydrator;

    public function __construct() {
        $this->hydrator = new Hydrator ();
    }
    
    /**
     * 使用新的alias来做数据库join查询
     * @param string $alias
     * @return array(a.k => v, ...)
     */
    public static function columns($alias = null) {
        if (empty(static::$columns)) {
            //self::$columns = get_class_vars(get_called_class());
            $e = new static();
            foreach ($e as $key => $val) {
                static::$columns[$key] = $val;
            }
        }
        if (! $alias) {
            return static::$columns;
        } else {
            $columnsWithAlias = array();
            foreach (static::$columns as $col => $val) {
                $columnsWithAlias["{$alias}.{$col}"] = $col;
            }
            return $columnsWithAlias;
        }
    }
    
    public function getIterator() {
        return new \ArrayIterator($this);
    }
    
    /**
     *
     * @param array $data            
     */
    public function exchangeArray($data) {
        $this->getHydrator()->hydrate($data, $this);
    }
    
    /**
     *
     * @return array()
     */
    public function getArrayCopy() {
        return $this->getHydrator()->extract ( $this );
    }
    
    /**
     * 将zendframework的join查询出来的数组，还原成多维数组
     * array('A.a' => 'a', 'A.b' => 'b') 变成 array('A' => array('a' => 'a', 'b' => 'b'))
     * 
     * @param array $data            
     * @return array
     */
    protected function inflate(array $data)
    {
        $result = array();
        foreach ( $data as $k => $v ) {
            if (stristr($k, '.') !== false) {
                list($entity, $property) = explode('.', $k);
                $result[$entity][$property] = $v;
            } else {
                $result[$k] = $v;
            }
        }
        return $result;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter()
    {
        return $this->inputFilter;
    }
    
	/**
     * @param \Zend\Stdlib\Hydrator\ObjectProperty $hydrator
     */
    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @return Hydrator
     */
    public function getHydrator()
    {
        if (!isset($this->hydrator)) {
            $this->hydrator = new Hydrator ();
        }
        return $this->hydrator;
    }

    public function merge($entity) {
        $className = get_called_class();
        $class = new \ReflectionClass($className);
        $properties = $class->getProperties();
        foreach ($properties as $propertie) {
            $propertieName = $propertie->name;
            if (isset($entity->$propertieName)) {
                $this->$propertieName = $entity->$propertieName;
            }
        }
    }
}
