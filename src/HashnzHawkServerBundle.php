<?php

namespace Hashnz\HawkServerBundle;

use Hashnz\HawkServerBundle\Authentication\HawkFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HashnzHawkServerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new HawkFactory());
    }
}
