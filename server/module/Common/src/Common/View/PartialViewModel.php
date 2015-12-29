<?php
namespace Common\View;

use Zend\View\Model\ViewModel;

class PartialViewModel extends ViewModel
{
    /**
     * @param mixed $variables
     * @param string $options
     */
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        $this->setTerminal(true);
    }
}