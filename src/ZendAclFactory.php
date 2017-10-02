<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authorization-acl for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authorization-acl/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Authorization\Acl;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Authorization\AuthorizationInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Exception\ExceptionInterface as AclExceptionInterface;

class ZendAclFactory
{
    /**
     * @throws Exception\InvalidConfigException
     */
    public function __invoke(ContainerInterface $container) : AuthorizationInterface
    {
        $config = $container->get('config')['authorization'] ?? null;
        if (null === $config) {
            throw new Exception\InvalidConfigException(
                'No authorization config provided'
            );
        }
        if (! isset($config['roles'])) {
            throw new Exception\InvalidConfigException(
                'No authorization roles configured for ZendAcl'
            );
        }
        if (! isset($config['resources'])) {
            throw new Exception\InvalidConfigException(
                'No authorization resources configured for ZendAcl'
            );
        }

        $acl = new Acl();

        $this->injectRoles($acl, $config['roles']);
        $this->injectResources($acl, $config['resources']);
        $this->injectPermissions($acl, $config['allow'] ?? [], 'allow');
        $this->injectPermissions($acl, $config['deny'] ?? [], 'deny');

        return new ZendAcl($acl);
    }

    /**
     * @throws Exception\InvalidConfigException
     */
    private function injectRoles(Acl $acl, array $roles) : void
    {
        foreach ($roles as $role => $parents) {
            foreach ($parents as $parent) {
                if (! $acl->hasRole($parent)) {
                    try {
                        $acl->addRole($parent);
                    } catch (AclExceptionInterface $e) {
                        throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
                    }
                }
            }
            try {
                $acl->addRole($role, $parents);
            } catch (AclExceptionInterface $e) {
                throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * @throws Exception\InvalidConfigException
     */
    private function injectResources(Acl $acl, array $resources) : void
    {
        foreach ($resources as $resource) {
            try {
                $acl->addResource($resource);
            } catch (AclExceptionInterface $e) {
                throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * @throws Exception\InvalidConfigException
     */
    private function injectPermissions(Acl $acl, array $permissions, string $type) : void
    {
        if (! in_array($type, ['allow', 'deny'], true)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Invalid permission type "%s" provided in configuration; must be one of "allow" or "deny"',
                $type
            ));
        }

        foreach ($permissions as $role => $resources) {
            try {
                $acl->$type($role, $resources);
            } catch (AclExceptionInterface $e) {
                throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
