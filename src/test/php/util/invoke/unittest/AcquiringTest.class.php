<?php namespace util\invoke\unittest;

use test\{Assert, Test};
use util\invoke\RateLimiting;

class AcquiringTest extends AbstractRateLimitingTest {
  private $rate= 1;

  /**
   * Creates a new fixture
   *
   * @param  int $time
   * @return util.invoke.RateLimiting
   */
  private function fixture($time= self::CLOCK_START) {
    return new RateLimiting($this->rate, $this->clock->resetTo($time));
  }

  #[Test]
  public function first_call_returns_immediately() {
    $fixture= $this->fixture();

    $this->assertDouble(0.0, $fixture->acquire(1));
  }

  #[Test]
  public function sleeps_for_one_second_if_rate_exceeded() {
    $fixture= $this->fixture();
    $fixture->acquire($this->rate);

    $this->assertDouble(1.0, $fixture->acquire(1));
  }

  #[Test]
  public function returns_immediately_after_having_slept_for_one_second() {
    $fixture= $this->fixture();
    $fixture->acquire($this->rate);
    $this->clock->forward(1.0);

    $this->assertDouble(0.0, $fixture->acquire(1));
  }
}