<?php
namespace Site\InputFilter;

use Zend\InputFilter\InputFilterAwareInterface;  
use Zend\InputFilter\InputFilterInterface;
use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Db\AbstractDb;

class PersonRegister implements InputFilterAwareInterface {
    /**
     * @var inputFilter
     */
    protected $inputFilter;
    
    /**
     * @var Database Adapter
     */
    protected $dbAdapter;
    
    /**
     * @param Adapter $dbAdapter
     */
    public function __construct(Adapter $dbAdapter) {
        $this->dbAdapter = $dbAdapter;
    }
    
    /**
     * @param InputFilterInterface $inputFilter
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }
    
    /**
     *
     * @return Adapter
     */
    public function getDbAdapter() {
        return $this->dbAdapter;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Zend\InputFilter\InputFilterAwareInterface::getInputFilter()
     * @return InputFilterInterface
     */
    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $inputFilter->add(array(
                    'name'     => 'username',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StringTrim'),
                    ),
                    'error_message' => '姓名不能为空，且至少2~20汉字或英文字符',
                    'validators' => array(
                            array(
                                    'name' => 'Alnum',
                            ),
                            array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 2,
                                            'max'      => 20,
                                    ),
                            ),
                    ),
            ));
            $inputFilter->add(array(
                'name'       => 'passwordVerify',
                'error_message' => '两次输入的密码不一样',
                'required'   => true,
                'filters'    => array(array('name' => 'StringTrim')),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'min' => 6,
                        ),
                    ),
                    array(
                        'name'    => 'Identical',
                        'options' => array(
                            'token' => 'password',
                        ),
                    ),
                ),
            ));
            $inputFilter->add(array(
                    'name'     => 'password',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StringTrim'),
                    ),
                    'error_message' => '密码至少6个字符',
                    'validators' => array(
                            array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min'      => 6
                                    ),
                            ),
                    ),
            ));
            $inputFilter->add(
                array(
                    'name'     => 'mobile',
                    'required' => true,
                    'filters'  => array(
                            array('name' => 'StringTrim'),
                    ),
                    'error_message' => '请输入正确的手机号码',
                    'validators' => array(
                            array(
                                    'name'    => 'Regex',
                                    'options' => array(
                                            'pattern' => '/^(?:13\d|15\d|18\d)\d{5}(\d{3}|\*{3})$/',
                                            'message'=>"请输入正确的手机号码"
                                    ),
                            ),
                    ),
            ));
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}