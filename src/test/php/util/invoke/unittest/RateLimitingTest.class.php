<?php namespace util\invoke\unittest;

use lang\IllegalArgumentException;
use test\Assert;
use test\{Expect, Test, Values};
use util\invoke\{Per, Rate, RateLimiting};

class RateLimitingTest extends AbstractRateLimitingTest {
  const RATE = 1000;

  #[Test, Values(eval: '[[self::RATE], [new Rate(self::RATE)], [new Rate(self::RATE, Per::$SECOND)]]')]
  public function can_create($rate) {
    new RateLimiting($rate);
  }

  #[Test, Values([0, -1]), Expect(IllegalArgumentException::class)]
  public function rate_cannot_be_zero_or_negative($rate) {
    new RateLimiting($rate);
  }

  #[Test]
  public function rate() {
    $rate= new Rate(self::RATE, Per::$SECOND);
    Assert::equals($rate, (new RateLimiting($rate))->rate());
  }

  #[Test]
  public function rate_defaults_to_per_second() {
    Assert::equals(new Rate(self::RATE, Per::$SECOND), (new RateLimiting(self::RATE))->rate());
  }

  #[Test]
  public function throttle() {
    $fixture= new RateLimiting(self::RATE);
    $fixture->throttle(100);
    Assert::equals(new Rate(self::RATE - 100, Per::$SECOND), $fixture->rate());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_throttle_to_zero() {
    (new RateLimiting(self::RATE))->throttle(self::RATE);
  }

  #[Test]
  public function increase() {
    $fixture= new RateLimiting(self::RATE);
    $fixture->increase(100);
    Assert::equals(new Rate(self::RATE + 100, Per::$SECOND), $fixture->rate());
  }
}