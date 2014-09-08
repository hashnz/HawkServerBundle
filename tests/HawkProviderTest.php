<?php
/**
 * Created by PhpStorm.
 * User: hash
 * Date: 8/09/14
 * Time: 1:04 PM
 */

namespace Hashnz\HawkServerBundle\Tests;


use Hashnz\HawkServerBundle\Authentication\HawkProvider;
use Hashnz\HawkServerBundle\Authentication\HawkToken;
use Dflydev\Hawk\Client\ClientBuilder;
use Dflydev\Hawk\Credentials\Credentials;

class HawkProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->userProvider = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')->getMock();
    }

    public function testAuthenticate()
    {
        // credentials
        $username = 'name';
        $password = '123456';

        // build hawk request
        $credentials = new Credentials($password, 'sha256', $username);
        $client = ClientBuilder::create()->build();
        $request = $client->createRequest(
            $credentials,
            'http://localhost/hawk-test',
            'GET'
        );
        // mock token from request
        $token = $this->getMockBuilder('Hashnz\HawkServerBundle\Authentication\HawkToken')->getMock();
        $token->expects($this->any())->method('getMethod')->will($this->returnValue($request->artifacts()->method()));
        $token->expects($this->any())->method('getHost')->will($this->returnValue($request->artifacts()->host()));
        $token->expects($this->any())->method('getPort')->will($this->returnValue($request->artifacts()->port()));
        $token->expects($this->any())->method('getUri')->will($this->returnValue($request->artifacts()->resource()));
        $token->expects($this->any())->method('getContentType')->will($this->returnValue($request->artifacts()->contentType()));
        $token->expects($this->any())->method('getContent')->will($this->returnValue($request->artifacts()->payload()));
        $token->expects($this->any())->method('getAuth')->will($this->returnValue($request->header()->fieldValue()));

        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')->getMock();
        $user
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue($username))
        ;
        $user
            ->expects($this->any())
            ->method('getPassword')
            ->will($this->returnValue($password))
        ;
        $user
            ->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue(array()))
        ;
        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->will($this->returnValue($user))
        ;

        $provider = new HawkProvider($this->userProvider);
        $provider->authenticate($token);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticate_authFail()
    {
        $token = $this->getMockBuilder('Hashnz\HawkServerBundle\Authentication\HawkToken')->getMock();
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')->getMock();
        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->will($this->returnValue($user))
        ;

        $provider = new HawkProvider($this->userProvider);
        $provider->authenticate($token);
    }

    public function testSupports()
    {

        $token = new HawkToken();
        $provider = new HawkProvider($this->userProvider);

        $this->assertTrue($provider->supports($token));
    }
} 