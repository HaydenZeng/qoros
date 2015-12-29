<?php
namespace Common\Authentication;

use BjyAuthorize\Provider\Identity\ProviderInterface;
use BjyAuthorize\Provider\Role\ProviderInterface as RoleProviderInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Authentication\AuthenticationService;

class BjyAuthorizeIdentityProvider implements ProviderInterface
{
    /**
     * @var AuthenticationService
     */
    protected $authService;
    
    /**
     * @var string|\Zend\Permissions\Acl\Role\RoleInterface
     */
    protected $defaultRole = 'guest';
    
    /**
     * @var string|\Zend\Permissions\Acl\Role\RoleInterface
     */
    protected $authenticatedRole = 'user';
    
    /**
     * @param AuthenticationService $authService
     */
    public function __construct(AuthenticationService $authService = null)
    {
        $this->authService = $authService;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        if (! $identity = $this->authService->getIdentity()) {
            return array($this->defaultRole);
        }

        if ($identity instanceof RoleInterface) {
            return array($identity);
        }

        if ($identity instanceof RoleProviderInterface) {
            return $identity->getRoles();
        }

        return array($this->authenticatedRole);
    }
}