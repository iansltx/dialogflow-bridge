API.ai Bridge
=============

A middleware + helper objects for responding to API.ai webhooks.

Requirements + Installation
---------------------------

To take full advantage of this library, you'll need:

1. A PSR-11 (formerly Container Interop) compatible container to pull dependencies from.
2. A framework that either uses double-pass middlewares (e.g. Slim 3) or the proposed PSR-15
middleware (e.g. Zend Expressive 2.0+), using PSR-7 requests and responses, to use the
middleware component.

To install, use Composer:

```
composer require iansltx/api-ai-bridge
```
