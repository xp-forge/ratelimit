<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;
use util\invoke\Per;
use util\invoke\Rate;

class RateLimitingTest extends AbstractRateLimitingTest {
  const RATE = 1000;

  #[@test, @values([
  #  [self::RATE],
  #  [new Rate(self::RATE)],
  #  [new Rate(self::RATE, Per::$SECOND)]
  #])]
  public function can_create($rate) {
    new RateLimiting($rate);
  }

  #[@test, @values([0, -1]), @expect('lang.IllegalArgumentException')]
  public function rate_cannot_be_zero_or_negative($rate) {
    new RateLimiting($rate);
  }

  #[@test]
  public function rate() {
    $rate= new Rate(self::RATE, Per::$SECOND);
    $this->assertEquals($rate, (new RateLimiting($rate))->rate());
  }

  #[@test]
  public function rate_defaults_to_per_second() {
    $this->assertEquals(new Rate(self::RATE, Per::$SECOND), (new RateLimiting(self::RATE))->rate());
  }

  #[@test]
  public function throttle() {
    $fixture= new RateLimiting(self::RATE);
    $fixture->throttle(100);
    $this->assertEquals(new Rate(self::RATE - 100, Per::$SECOND), $fixture->rate());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_throttle_to_zero() {
    (new RateLimiting(self::RATE))->throttle(self::RATE);
  }

  #[@test]
  public function increase() {
    $fixture= new RateLimiting(self::RATE);
    $fixture->increase(100);
    $this->assertEquals(new Rate(self::RATE + 100, Per::$SECOND), $fixture->rate());
  }
}