<?php
namespace Common\Form;

use Zend\InputFilter\InputFilterAwareInterface;

abstract class FormFilter implements InputFilterAwareInterface
{
    protected $errors;
    
    public function getErrorMessage($name)
    {
        if (! $this->errors) {
            foreach ($this->getInputFilter()->getInvalidInput() as $invalidInput) {
                $this->errors[$invalidInput->getName()] = $invalidInput->getErrorMessage();
            }
        }
        return (@$this->errors[$name] ?: "");
    }   
    
    public function getValue($name) 
    {
        return $this->getInputFilter()->getValue($name);
    }
}