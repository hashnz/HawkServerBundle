<?php

namespace Hashnz\HawkServerBundle\Tests;


use Hashnz\HawkServerBundle\Authentication\HawkToken;

class HawkTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor_roles()
    {
        $token = new HawkToken(array('ROLE'));
        $this->assertTrue($token->isAuthenticated());
    }

    public function testConstructor_noRoles()
    {
        $token = new HawkToken();
        $this->assertFalse($token->isAuthenticated());
    }
}