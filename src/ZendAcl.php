<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authorization-acl for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authorization-acl/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\Authorization\Acl;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authorization\AuthorizationInterface;
use Zend\Expressive\Authorization\Exception;
use Zend\Expressive\Router\RouteResult;
use Zend\Permissions\Acl\Acl;

use function sprintf;

class ZendAcl implements AuthorizationInterface
{
    /**
     * @var Acl
     */
    private $acl;

    public function __construct(Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function isGranted(string $role, ServerRequestInterface $request) : bool
    {
        $routeResult = $request->getAttribute(RouteResult::class, false);
        if (false === $routeResult) {
            throw new Exception\RuntimeException(sprintf(
                'The %s attribute is missing in the request; cannot perform ACL authorization checks',
                RouteResult::class
            ));
        }
        $routeName = $routeResult->getMatchedRouteName();

        return $this->acl->isAllowed($role, $routeName);
    }
}
