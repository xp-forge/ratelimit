<?php namespace util\invoke\unittest;

use test\{Assert, Test};
use util\invoke\{Per, Rate, RateLimiting};

class RateExcessTest extends AbstractRateLimitingTest {

  /**
   * Creates a new fixture
   *
   * @param  int $time
   * @return util.invoke.RateLimiting
   */
  private function fixture($time= self::CLOCK_START) {
    return new RateLimiting(10, $this->clock->resetTo($time));
  }

  #[Test]
  public function try_acquiring_10_times_than_limit() {
    $fixture= $this->fixture();
    Assert::true($fixture->tryAcquiring(100));
    $this->assertDouble(10.0, $fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [100 *    *    *    *    *    *    *    *    *    [1
    // [10  [10  [10  [10  [10  [10  [10  [10  [10  [10  [1
  }

  #[Test]
  public function try_acquiring_one_more_than_limit() {
    $fixture= $this->fixture();
    Assert::true($fixture->tryAcquiring(11));
    $this->assertDouble(1.1, $fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [11  [1 
    // [10  [1   [1
  }

  #[Test]
  public function try_acquiring_the_limit() {
    $fixture= $this->fixture();
    Assert::true($fixture->tryAcquiring(10));
    $this->assertDouble(1.0, $fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [10  [1
  }

  #[Test]
  public function try_acquiring_twice_the_limit() {
    $fixture= $this->fixture();
    Assert::true($fixture->tryAcquiring(20));
    $this->assertDouble(2.0, $fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [20  *    [1
    // [10  [10  [1
  }

  #[Test]
  public function try_acquiring_excess_twice() {
    $fixture= $this->fixture();
    Assert::true($fixture->tryAcquiring(11));
    $this->assertDouble(1.1, $fixture->acquire(11));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [11  *    [11
    // [10  [1   [10  [1
  }

  #[Test]
  public function remaining_after_acquiring_excess() {
    $fixture= $this->fixture();
    $fixture->acquire(11);
    Assert::equals(0, $fixture->remaining());
  }

  #[Test]
  public function resetTime_after_acquiring_excess() {
    $fixture= $this->fixture();
    $fixture->acquire(11);
    $this->assertDouble(self::CLOCK_START + 1.0, $fixture->resetTime());
  }
}