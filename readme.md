Dialogflow Bridge
=============

A middleware + helper objects for responding to Dialogflow webhooks.

Requirements + Installation
---------------------------

To take full advantage of this library, you'll need:

1. A PSR-11 (formerly Container Interop) compatible container to pull dependencies from.
2. A framework that either uses double-pass middlewares (e.g. Slim 3) or the proposed PSR-15
middleware (e.g. Zend Expressive 2.0+), using PSR-7 requests and responses, to use the
middleware component.

To install, use Composer:

```
composer require iansltx/dialogflow-bridge
```

Getting Started
===============

The easiest way to integrate this library is by adding it as a route middleware to
a supported (micro)framework. For example, with Slim 3:

```php
<?php

use iansltx\DialogflowBridge\{Middleware, Router, Question, Answer};

// require Composer autoloader here

$app = new Slim\App();
$app->post('/hook', new Middleware\DoublePass(Router::buildFromClosureArray([
    'hello' => function(Question $question, Answer $answer) : Answer {
        return $answer->withSpeechAndText("Hello {$question->getParam('name', 'world')}!");
    }
])));
$app->run();
```

To see your new app in action, point Dialogflow's web hook configuration at `/hook`
on wherever you're hosting your app, create an intent with (or without) a `name`
parameter, set the action name on that intent to `hello`, check the box to use
the Web Hook for fulfillment, then call your new intent.

Now, let's break down what just happened:

1. Dialogflow called our web hook, which Slim routed to the `DoublePass` middleware.
2. That middleware handed the request off to the `Router`, which marshaled
`Question` and `Answer` classes.
3. the `Router` saw that the `Question` had an action of `hello`, which matched
one of the handlers passed to it, so it called the handler, getting an `Answer`
back.
4. The `Router` passed the `Answer` back to the middleware, which updated the
HTTP `Response` with the proper JSON payload.

Yu can learn more about each of these components later in this document.

Adding a Fallback
-----------------

This library comes with a built-in, albeit generic, handler for when the action
on an incoming web hook request doesn't match anything in your route mapping.
The function signature is the same as a normal action handler, and any callable
(a class with `__invoke()`, a `Closure`, etc.) can be used here. Just pass the
callable as an additional parameter when creating your `Router`. Tweaking the
previous example:

```php
<?php

use iansltx\DialogflowBridge\{Middleware, Router, Question, Answer};

// require Composer autoloader here

$app = new Slim\App();
$app->post('/hook', new Middleware\DoublePass(Router::buildFromClosureArray([
    'hello' => function(Question $question, Answer $answer) : Answer {
        return $answer->withSpeechAndText("Hello {$question->getParam('name', 'world')}!");
    }
], function (Question $question, Answer $answer) : Answer {
    return $answer->withSpeechAndText("Sorry, I'm not sure what to do here.");
})));
$app->run();
```

As your application gets more copmlex, you'll want to start...

Managing Handler Dependencies
=============================

An array of anonymous functions is a quick way to start handling web hook requests,
but you'll probably want to put your handlers in your main application DI container
as your project grows. For this case, you'll want to use `Router::build()` instead
of `Router::buildFromClosureArray()`. `build()` takes three parameters:

1. An object that implements the PSR-11 `ContainerInterface` (e.g. Slim 3's container)
2. An array of mappings between action names (as keys) and container keys (as values).
This provides a whitelist so incoming requests aren't pulling random services out
of the container, and allows you to map multiple action names to a single dependency
if you like.
3. The optional fallback callable.

Taking the previous example, but assuming we've now moved our "hello" handler into
the container as "helloHandler", we'd end up with:

```php
<?php

use iansltx\DialogflowBridge\{Middleware, Router, Question, Answer};

// require Composer autoloader here

$app = new Slim\App();
$app->post('/hook', new Middleware\DoublePass(Router::build($app->getContainer(), [
    'hello' => 'helloHandler'
], function (Question $question, Answer $answer) : Answer {
    return $answer->withSpeechAndText("Sorry, I'm not sure what to do here.");
})));
$app->run();
```

As you switch to class-based action handlers, you can (but don't need to) implement
`HandlerInterface` to ensure your handler method has the right signature.

More on Middlewares
===================

The `DoublePass` class can be used as an actual middleware, rather than as a
Slim route callable, as needed; its third parameter is unused and not typehinted
to allow for this.

If you'd rather use a framework that relies on the proposed PSR-15 middleware
spec, set up an instance of the `PSR15` class. In that case you'll need both
a `Router` instance (just like `DoublePass`) and a callable that, given
something JSON-serializeable as its only parameter, returns a PSR-7
ResponseInterface with the parameter JSON-encoded, plus the proper Content-Type
header. If you're using Zend Diactoros, building the middleware will look like:

```php
$middleware = new Middleware\PSR15($router, function($data) {
    return new \Zend\Diactoros\JsonResponse($data);
});
```

Objects
=======

This library provides a couple of **immutable** objects that make interacting with
Dialogflow web hooks a bit easier.

Question
--------

Question wraps Dialogflow's web hook request JSON, allowing cleaner access to parameters,
contexts, and other aspects of the web hook request. Normally you'll be provided
a Question by the router, but you can also build a Question independently:

```php
/** @var \Psr\Http\Message\ServerRequestInterface $request */
$question = Question::fromRequest($request); // from a PSR-7 request

/** @var array $data */
$question = new Question($data); // from an array, e.g. by JSON-decoding the web hook request body
```

See method docblocks in the Question class for more information.

Answer
------

An Answer can be passed directly into `json_decode()` to produce the response body
needed for an Dialogflow web hook call. Normally this will get generated by calling
`getBaseAnswer()` on a Question. Doing so informs the Answer of which contexts are
currently set, so they can be dropped if needed. This is the way the router handles
Questions and Answers, but you can also call `new Answer()` directly.

See method docblocks in the Answer class for more information.
