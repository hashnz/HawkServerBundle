<?php

namespace Hashnz\HawkServerBundle\Tests;

use Hashnz\HawkServerBundle\Authentication\HawkListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class HawkListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->headers = $this->getMockBuilder('Symfony\Component\HttpFoundation\HeaderBag')->getMock();
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
        $this->getEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
        $this->filterEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')->disableOriginalConstructor()->getMock();
        $this->token = $this->getMockBuilder('Hashnz\HawkServerBundle\Authentication\HawkToken')->getMock();
        $this->context = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')->getMock();
        $this->manager = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface')->getMock();
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
    }

    public function testConstructor_addsSubscriber()
    {
        $this->dispatcher->expects($this->once())->method('addSubscriber');

        $listener = new HawkListener($this->context, $this->manager, $this->dispatcher);
    }

    public function testHandle()
    {
        $this->headers
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue('foo'))
        ;

        $this->request->headers = $this->headers;

        $this->getEvent
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request))
        ;

        $this->context
            ->expects($this->once())
            ->method('setToken')
            ->with($this->isInstanceOf($this->token))
        ;

        $this->manager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Hashnz\HawkServerBundle\Authentication\HawkToken'))
            ->will($this->returnValue($this->token))
        ;

        $listener = new HawkListener($this->context, $this->manager, $this->dispatcher);
        $listener->handle($this->getEvent);
    }

    public function testHandle_authFail()
    {
        $this->headers
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue('foo'))
        ;

        $this->request->headers = $this->headers;

        $this->getEvent
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request))
        ;

        $this->getEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Response'))
        ;

        $this->manager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException(new AuthenticationException()))
        ;

        $listener = new HawkListener($this->context, $this->manager, $this->dispatcher);
        $listener->handle($this->getEvent);
    }

    public function testOnKernelResponse()
    {
        $hawkCredentials = $this->getMockBuilder('Dflydev\Hawk\Credentials\CredentialsInterface')->getMock();
        $hawkArtifacts = $this->getMockBuilder('Dflydev\Hawk\Crypto\Artifacts')->disableOriginalConstructor()->getMock();

        $hawkResponse = $this->getMockBuilder('Dflydev\Hawk\Server\Response')->disableOriginalConstructor()->getMock();
        $hawkResponse
            ->expects($this->once())
            ->method('credentials')
            ->will($this->returnValue($hawkCredentials))
        ;
        $hawkResponse
            ->expects($this->once())
            ->method('artifacts')
            ->will($this->returnValue($hawkArtifacts))
        ;

        $hawkHeader = $this->getMockBuilder('Dflydev\Hawk\Header\Header')->disableOriginalConstructor()->getMock();
        $hawkHeader
            ->expects($this->once())
            ->method('fieldName')
            ->will($this->returnValue('fname'))
        ;
        $hawkHeader
            ->expects($this->once())
            ->method('fieldValue')
            ->will($this->returnValue('fval'))
        ;

        $hawkServer = $this->getMockBuilder('Dflydev\Hawk\Server\Server')->disableOriginalConstructor()->getMock();
        $hawkServer
            ->expects($this->once())
            ->method('createHeader')
            ->will($this->returnValue($hawkHeader))
        ;

        $this->token
            ->expects($this->once())
            ->method('getHawkResponse')
            ->will($this->returnValue($hawkResponse))
        ;
        $this->token
            ->expects($this->once())
            ->method('getHawkServer')
            ->will($this->returnValue($hawkServer))
        ;

        $this->context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($this->token));
        ;

        $this->headers
            ->expects($this->any())
            ->method('set')
            ->with('fname', 'fval')
        ;

        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $response->headers = $this->headers;

        $this->filterEvent
            ->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($response))
        ;

        $listener = new HawkListener($this->context, $this->manager, $this->dispatcher);
        $listener->onKernelResponse($this->filterEvent);

    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(array(KernelEvents::RESPONSE   => 'onKernelResponse'), HawkListener::getSubscribedEvents());
    }
} 