<?php
namespace Common\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\AuthenticationService;
use Common\Authentication\DoctrineAdapter as AuthAdapter;
use Site\Entity\UserEntity;

class Authentication extends AbstractPlugin
{
    /**
     * @var AuthAdapter
     */
    protected $_authAdapter = null;
    
    /**
     * @var AuthenticationService
     */
    protected $_authService = null;
    
    /**
     * Check if Identity is present
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return $this->getAuthService()->hasIdentity();
    }
    
    /**
     * Return current Identity
     *
     * @return UserEntity|null
     */
    public function getIdentity()
    {
        return $this->getAuthService()->getIdentity();
    }
    
    /**
     * Sets Auth Adapter
     *
     * @param AuthAdapter
     * @return Authentication
     */
    public function setAuthAdapter(AuthAdapter $authAdapter)
    {
        $this->_authAdapter = $authAdapter;
        return $this;
    }
    
    /**
     * Returns Auth Adapter
     *
     * @return AuthAdapter
     */
    public function getAuthAdapter()
    {
        if ($this->_authAdapter === null) {
            $this->setAuthAdapter(new AuthAdapter());
        }
        return $this->_authAdapter;
    }
    
    /**
     * Sets Auth Service
     * @param AuthenticationService $authService
     * @return \Common\Controller\Plugin\Authentication
     */
    public function setAuthService(AuthenticationService $authService)
    {
        $this->_authService = $authService;
        return $this;
    }
    
    /**
     * Gets Auth Service
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if ($this->_authService === null) {
            $this->setAuthService(new AuthenticationService());
        }
        return $this->_authService;
    }
}