Rate limiting
=============

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/ratelimit.svg)](http://travis-ci.org/xp-forge/ratelimit)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)

A rate limiting can be used to limit the rate at which physical or logical resources are accessed, e.g. to protect them from intentional or unintentional overuse.

Restricting usage
-----------------
Imagine you don't want to run more than two tasks per second:

```php
$rateLimiter= new RateLimiting(2);
foreach ($tasks as $task) {
  $rateLimiter->acquire();    // will wait if necessary
  $task->run();
}
```

Restricting bandwidth
---------------------
You can implement bandwidth throttling by acquiring a permit for each byte:

```php
$rateLimiter= new RateLimiting(new Rate(1000000, Per::$MINUTE));
while ($bytes= $source->read()) {
  $rateLimiter->acquire(strlen($bytes));
  $target->write($bytes);
}
```

Further reading
---------------

* [RateLimiter - discovering Google Guava](http://www.nurkiewicz.com/2012/09/ratelimiter-discovering-google-guava.html) by Tomasz Nurkiewicz
* [Guava's RateLimiter class](http://docs.guava-libraries.googlecode.com/git/javadoc/com/google/common/util/concurrent/RateLimiter.html) - which is what this project was inspired from.