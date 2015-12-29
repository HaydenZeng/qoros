<?php
namespace Common\Table;

use Zend\Db\TableGateway\TableGateway;

class Table
{
    /**
     * @var TableGateway
     */
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
}