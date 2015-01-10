Rate limiting
=============

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/ratelimit.svg)](http://travis-ci.org/xp-forge/ratelimit)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Required HHVM 3.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_4plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/ratelimit/version.png)](https://packagist.org/packages/xp-forge/ratelimit)

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

Rate-limiting users
-------------------
Implement a scriptlet filter like the following:

```php
use scriptlet\HttpScriptletException;
use peer\http\HttpConstants;

class RateLimitingFilter extends \lang\Object implements \scriptlet\Filter {
  private $rates, $rate, $timeout;

  public function __construct(KeyValueStorage $rates) {
    $this->rates= $rates;
    $this->rate= new Rate(5000, Per::$HOUR);
    $this->timeout= 0.2;
  }

  public function filter($request, $response, $invocation) {
    $remote= $request->getEnvValue('REMOTE_ADDR');

    $rateLimiter= $this->rates->get($remote) ?: new RateLimiting($this->rate);
    $permitted= $rateLimiter->tryAcquiring(1, $this->timeout);
    $this->rates->put($remote, $rateLimiter);

    $response->setHeader('X-RateLimit-Limit', $rateLimiter->rate()->value());
    $response->setHeader('X-RateLimit-Remaining', $rateLimiter->remaining());
    $response->setHeader('X-RateLimit-Reset', $rateLimiter->resetTime());

    if (!$permitted) {
      throw new HttpScriptletException('Nope', HttpConstants::STATUS_TOO_MANY_REQUESTS);
    }

    return $invocation->proceed($request, $response);
  }
}
```

Further reading
---------------

* [RateLimiter - discovering Google Guava](http://www.nurkiewicz.com/2012/09/ratelimiter-discovering-google-guava.html) by Tomasz Nurkiewicz
* [Guava's RateLimiter class](http://docs.guava-libraries.googlecode.com/git/javadoc/com/google/common/util/concurrent/RateLimiter.html) - which is what this project was inspired from.