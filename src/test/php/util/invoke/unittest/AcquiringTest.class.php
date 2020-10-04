<?php namespace util\invoke\unittest;

use unittest\Test;
use util\invoke\RateLimiting;

class AcquiringTest extends AbstractRateLimitingTest {

  /**
   * Constructor
   *
   * @param string $name
   * @param int $rate Defaults to 1
   */
  public function __construct($name, $rate= 1) {
    parent::__construct($name);
    $this->rate= (int)$rate;
  }

  #[Test]
  public function first_call_returns_immediately() {
    $fixture= new RateLimiting($this->rate, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire(1));
  }

  #[Test]
  public function sleeps_for_one_second_if_rate_exceeded() {
    $fixture= new RateLimiting($this->rate, self::$clock);
    $fixture->acquire($this->rate);
    $this->assertDouble(1.0, $fixture->acquire(1));
  }

  #[Test]
  public function returns_immediately_after_having_slept_for_one_second() {
    $fixture= new RateLimiting($this->rate, self::$clock);
    $fixture->acquire($this->rate);
    self::$clock->forward(1.0);
    $this->assertDouble(0.0, $fixture->acquire(1));
  }
}