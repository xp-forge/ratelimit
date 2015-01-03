<?php namespace util\invoke;

/**
 * A rate limiting can be used to limit the rate at which physical or
 * logical resources are accessed, e.g. to protect them from intentional
 * or unintentional overuse. It is common to apply rate limiting to
 * publicly available APIs by user or IP address.
 *
 * @test  xp://util.invoke.unittest.RateLimitingTest
 */
class RateLimiting extends \lang\Object {
  const TIMED_OUT = -1.0;
  const ONE_MICRO = 0.000001;

  private $rate, $clock, $offset, $permits= 0, $next= null, $reset= null;

  /**
   * Creates a new rate limiting instance
   *
   * @param  var $arg Either a rate instance or an integer referring to a rate per second
   * @param  util.invoke.Clock The clock to use; defaults to system clock.
   * @throws lang.IllegalArgumentException
   */
  public function __construct($arg, Clock $clock= null) {
    if ($arg instanceof Rate) {
      $this->rate= $arg;
    } else {
      $this->rate= new Rate($arg, Per::$SECOND);
    }
    $this->clock= $clock ?: new SystemClock();
  }

  /** @return util.invoke.Rate */
  public function rate() { return $this->rate; }

  /** @return int */
  public function remaining() {
    if (null === $this->next) return $this->rate->value();

    $slot= (int)$this->clock->time();
    if ($slot > $this->next) {
      $this->permits= 0;
      return $this->rate->value();
    } else {
      return max(0, $this->rate->value() - $this->permits);
    } 
  }

  /** @return int */
  public function resetTime() { return $this->reset; } 

  /** @param int $by */
  public function throttle($by) {
    $this->rate= new Rate($this->rate->value() - $by, $this->rate->unit());
  }
  
  /** @param int $by */
  public function increase($by) {
    $this->rate= new Rate($this->rate->value() + $by, $this->rate->unit());
  }

  /**
   * Wait for the given number of permits
   *
   * @param  int $permits
   * @param  double $timeout. Use NULL to wait forever.
   * @return double The time spent sleeping, or TIMEOUT
   */
  protected function waitFor($permits, $timeout) {
    $time= $this->clock->time();
    $slot= (int)$time;
    $sleep= 0.0;

    if (null === $this->next) {         // First time use
      $this->next= $slot;
      $this->offset= $time - $slot;
      $this->reset= $slot + $this->rate->unit()->seconds() + $this->offset;
    } else if ($this->next > $slot) {
      $sleep= $this->next - $time + $this->offset;
      if (null !== $timeout && $sleep > $timeout) return self::TIMED_OUT;

      $this->clock->wait($sleep + self::ONE_MICRO);
      $this->permits= 0;
      $this->next= (int)$this->clock->time();
      $this->reset= $this->next + $this->rate->unit()->seconds() + $this->offset;
    } else if ($slot > $this->next) {
      $this->permits= 0;
      $this->reset= $slot + $this->rate->unit()->seconds() + $this->offset;
    }

    $this->permits+= $permits;
    $rest= $this->rate->value() - $this->permits;
    if (0 === $rest) {
      $this->next+= $this->rate->unit()->seconds();
    } else if ($rest < 0) {
      $exceed= -$rest;
      $buffers= (int)ceil($exceed / $this->rate->value());
      $this->next+= $this->rate->unit()->seconds() * $buffers;
    }

    return $sleep;
  }

  /**
   * Acquire the given number of permits, blocking until the request can be
   * granted.
   *
   * @param  int $permits
   * @return double The time spent waiting, if any.
   */
  public function acquire($permits= 1) {
    return $this->waitFor($permits, null);
  }

  /**
   * Try acquiring the given number of permits within a given timeout. Returns
   * immediately if the permits cannot be acquired within the given timeout,
   * waits if necessary otherwise.
   *
   * @param  int $permits
   * @param  double $timeout
   * @return bool FALSE if the request could not be granted before the timeout expired.
   */
  public function tryAcquiring($permits= 1, $timeout= 0.0) {
    return self::TIMED_OUT !== $this->waitFor($permits, $timeout);
  }
}