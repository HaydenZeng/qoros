<?php
namespace Common\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use User\Table\UserTable;
use User\Entity\UserEntity;

class DbAdapter implements AdapterInterface
{
    /**
     * @var UserTable
     */
    private $userTable;

    private $username;
    private $password;
    
    public function __construct(UserTable $table)
    {
        $this->userTable = $table;
    }
    
    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        //先尝试手机登陆
        $user = $this->userTable->findByMobile($this->username);
        if (! $user) {
            //手机登陆不成，再尝试用户名登陆
        $user = $this->userTable->findByUsername($this->username);
        }
        if (! $user) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, 0);
        }
        $bcrypt = new Bcrypt();
        if (! $bcrypt->verify($this->password, $user->password)) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, 0);
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
}