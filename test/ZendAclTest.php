<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authorization-acl for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authorization-acl/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Authorization\Acl;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Authorization\Acl\Exception;
use Zend\Expressive\Authorization\Acl\ZendAcl;
use Zend\Expressive\Router\RouteResult;
use Zend\Permissions\Acl\Acl;

class ZendAclTest extends TestCase
{
    public function setUp()
    {
        $this->acl = $this->prophesize(Acl::class);
    }

    public function testConstructor()
    {
        $zendAcl = new ZendAcl($this->acl->reveal());
        $this->assertInstanceOf(ZendAcl::class, $zendAcl);
    }

    public function testIsGrantedWithoutRouteResult()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn(false);

        $zendAcl = new ZendAcl($this->acl->reveal());

        $this->expectException(Exception\RuntimeException::class);
        $zendAcl->isGranted('foo', $request->reveal());
    }

    public function testIsGranted()
    {
        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)
                ->willReturn($routeResult->reveal());

        $this->acl->isAllowed('foo', 'home')->willReturn(true);
        $zendAcl = new ZendAcl($this->acl->reveal());

        $this->assertTrue($zendAcl->isGranted('foo', $request->reveal()));
    }

    public function testIsNotGranted()
    {
        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)
                ->willReturn($routeResult->reveal());

        $this->acl->isAllowed('foo', 'home')->willReturn(false);
        $zendAcl = new ZendAcl($this->acl->reveal());

        $this->assertFalse($zendAcl->isGranted('foo', $request->reveal()));
    }
}
