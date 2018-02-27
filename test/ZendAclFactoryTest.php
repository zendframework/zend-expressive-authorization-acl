<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authorization-acl for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authorization-acl/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\Authorization\Acl;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Authorization\Acl\ZendAcl;
use Zend\Expressive\Authorization\Acl\ZendAclFactory;
use Zend\Expressive\Authorization\Exception;

class ZendAclFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryWithoutConfig()
    {
        $this->container->get('config')->willReturn([]);

        $factory = new ZendAclFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithoutZendAclConfig()
    {
        $this->container->get('config')->willReturn(['authorization' => []]);

        $factory = new ZendAclFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithoutResources()
    {
        $this->container->get('config')->willReturn([
            'authorization' => [
                'roles' => [],
            ],
        ]);

        $factory = new ZendAclFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithEmptyRolesResources()
    {
        $this->container->get('config')->willReturn([
            'authorization' => [
                'roles' => [],
                'resources' => [],
            ],
        ]);

        $factory = new ZendAclFactory();
        $zendAcl = $factory($this->container->reveal());
        $this->assertInstanceOf(ZendAcl::class, $zendAcl);
    }

    public function testFactoryWithoutAllowOrDeny()
    {
        $config = [
            'authorization' => [
                'roles' => [
                    'admini'      => [],
                    'editor'      => ['administrator'],
                    'contributor' => ['editor'],
                ],
                'resources' => [
                    'admin.dashboard',
                    'admin.posts',
                    'admin.publish',
                    'admin.settings',
                ],
            ],
        ];
        $this->container->get('config')->willReturn($config);

        $factory = new ZendAclFactory();
        $zendAcl = $factory($this->container->reveal());
        $this->assertInstanceOf(ZendAcl::class, $zendAcl);
    }

    public function testFactoryWithInvalidRole()
    {
        $this->container->get('config')->willReturn([
            'authorization' => [
                'roles' => [
                    1 => [],
                ],
                'permissions' => [],
            ],
        ]);

        $factory = new ZendAclFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithUnknownRole()
    {
        $this->container->get('config')->willReturn([
            'authorization' => [
                'roles' => [
                    'administrator' => [],
                ],
                'resources' => [
                    'admin.dashboard',
                    'admin.posts',
                ],
                'allow' => [
                    'editor' => ['admin.dashboard'],
                ],
            ],
        ]);

        $factory = new ZendAclFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }
}
