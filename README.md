Rate limiting
=============

[![Build status on GitHub](https://github.com/xp-forge/ratelimit/workflows/Tests/badge.svg)](https://github.com/xp-forge/ratelimit/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/ratelimit/version.png)](https://packagist.org/packages/xp-forge/ratelimit)

A rate limiting can be used to limit the rate at which physical or logical resources are accessed, e.g. to protect them from intentional or unintentional overuse.

Restricting usage
-----------------
Imagine you don't want to run more than two tasks per second:

```php
use util\invoke\RateLimiting;

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
use util\invoke\{RateLimiting, Rate, Per};

$rateLimiter= new RateLimiting(new Rate(1000000, Per::$MINUTE));
while ($bytes= $source->read()) {
  $rateLimiter->acquire(strlen($bytes));
  $target->write($bytes);
}
```

Rate-limiting users
-------------------
Implement a filter like the following:

```php
use web\{Filter, Error};
use util\invoke\{RateLimiting, Rate, Per};

class RateLimitingFilter implements Filter {
  private $rates, $rate, $timeout;

  public function __construct(KeyValueStorage $rates) {
    $this->rates= $rates;
    $this->rate= new Rate(5000, Per::$HOUR);
    $this->timeout= 0.2;
  }

  public function filter($request, $response, $invocation) {
    $remote= $request->header('Remote-Addr');

    $limits= $this->rates->get($remote) ?: new RateLimiting($this->rate);
    $permitted= $limits->tryAcquiring(1, $this->timeout);
    $this->rates->put($remote, $limits);

    $response->header('X-RateLimit-Limit', $limits->rate()->value());
    $response->header('X-RateLimit-Remaining', $limits->remaining());
    $response->header('X-RateLimit-Reset', $limits->resetTime());

    if (!$permitted) {
      throw new Error(429, 'Rate limit exceeded');
    }

    return $invocation->proceed($request, $response);
  }
}
```

Further reading
---------------

* [RateLimiter - discovering Google Guava](http://www.nurkiewicz.com/2012/09/ratelimiter-discovering-google-guava.html) by Tomasz Nurkiewicz
* [Guava's RateLimiter class](http://docs.guava-libraries.googlecode.com/git/javadoc/com/google/common/util/concurrent/RateLimiter.html) - which is what this project was inspired from.