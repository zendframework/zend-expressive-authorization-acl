<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-authorization-acl for the canonical source repository
 * @copyright Copyright (c) 2017-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-authorization-acl/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Authorization\Acl;

use PHPUnit\Framework\TestCase;
use Zend\Expressive\Authorization\Acl\ConfigProvider;
use Zend\Expressive\Authorization\Acl\ZendAcl;

class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider */
    private $provider;

    protected function setUp()
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'];
        $this->assertInternalType('array', $factories);
        $this->assertArrayHasKey(ZendAcl::class, $factories);
    }
}
