<?php

namespace Hashnz\HawkServerBundle\Tests;

use Hashnz\HawkServerBundle\Authentication\HawkFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HawkFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = new ContainerBuilder();
        $factory = new HawkFactory();

        $id = 'id';
        $providerId = 'security.authentication.provider.hawk.'.$id;
        $listenerId = 'security.authentication.listener.hawk.'.$id;


        $this->assertEquals(
            array($providerId, $listenerId, 'entry'),
            $factory->create($container, $id, 'config', 'user', 'entry')
        );
        $this->assertTrue($container->hasDefinition($providerId));
        $this->assertTrue($container->hasDefinition($listenerId));
    }

    public function testGetPosition()
    {
        $factory = new HawkFactory();
        $this->assertEquals('pre_auth', $factory->getPosition());
    }

    public function testGetKey()
    {
        $factory = new HawkFactory();
        $this->assertEquals('hawk', $factory->getKey());
    }
}
