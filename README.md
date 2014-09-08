# Hawk Server Bundle

A bundle that implements [HAWK](https://github.com/dflydev/dflydev-hawk) authorization for Symfony.

## Installation

Add to `composer.json`

    {
        "require": {
            "hashnz/hawk-server-bundle": "dev-master"
        }
    }
    
You may need to allow `@dev` for the underlying dflydev/hawk package:

    {
        "require": {
            "hashnz/hawk-server-bundle": "dev-master"
            "dflydev/hawk": "@dev"
        }
    }
    
Register the bundle in `app/AppKernel.php`

    $bundles = array(
        // ...
        new Hashnz\HawkServerBundle\HashnzHawkServerBundle(),
    );

## Usage

Secure a firewall in `security.yml`

    security:
        firewalls:
            hawk_secured:
                pattern: ^/hawk-test
                stateless: true
                hawk: true
