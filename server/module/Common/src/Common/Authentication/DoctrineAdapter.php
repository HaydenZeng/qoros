<?php
namespace Common\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Doctrine\ORM\EntityManager;


class DoctrineAdapter implements AdapterInterface
{
    private $username;
    private $password;
    private $checkPassword;
    /**
     * @var EntityManager
     */
    private $em;
    
    public function __construct(EntityManager $em) {
        $this->em = $em;
        $this->checkPassword = true;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        //先尝试手机登陆
        $er = $this->em->getRepository('Site\Entity\UserEntity');
        $user = $er->findOneBy(array('mobile' => $this->username));
        if (! $user) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, 0);
        }
        if ($this->checkPassword) {
            $bcrypt = new Bcrypt();
            if (! $bcrypt->verify($this->password, $user->password)) {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, 0);
            }
        }
        $result = new Result(Result::SUCCESS, $user);
        return $result;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }
    
	/**
     * @param boolean $checkPassword
     */
    public function setCheckPassword($checkPassword)
    {
        $this->checkPassword = $checkPassword;
    }
}