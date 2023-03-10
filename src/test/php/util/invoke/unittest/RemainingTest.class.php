<?php namespace util\invoke\unittest;

use test\{Assert, Test, Values};
use util\invoke\{Rate, RateLimiting};

/**
 * Base class for `RateLimiting::remaining()` tests.
 *
 * @see  util.invoke.unittest.RemainingPerSecondTest
 * @see  util.invoke.unittest.RemainingPerHourTest
 */
abstract class RemainingTest extends AbstractRateLimitingTest {
  const RATE= 1000;

  /**
   * Returns the unit to use. Implemented in subclasses.
   *
   * @return util.invoke.Per
   */
  protected abstract function unit();

  /**
   * Creates a new fixture
   *
   * @param  int $time
   * @return util.invoke.RateLimiting
   */
  protected function fixture($time= self::CLOCK_START) {
    return new RateLimiting(new Rate(self::RATE, $this->unit()), $this->clock->resetTo($time));
  }

  #[Test]
  public function initially_equals_rate() {
    Assert::equals(self::RATE, $this->fixture()->remaining());
  }

  #[Test]
  public function after_having_acquired_one() {
    $fixture= $this->fixture();
    $fixture->acquire();

    Assert::equals(self::RATE - 1, $fixture->remaining());
  }

  #[Test]
  public function after_having_acquired_limit() {
    $fixture= $this->fixture();
    $fixture->acquire(self::RATE);

    Assert::equals(0, $fixture->remaining());
  }

  #[Test, Values([2, 3, 4])]
  public function after_having_acquired_one_multiple_times($times) {
    $fixture= $this->fixture();
    for ($i= 0; $i < $times; $i++) {
      $fixture->acquire();
    }

    Assert::equals(self::RATE - $times, $fixture->remaining());
  }

  #[Test]
  public function after_having_acquired_limit_and_then_acquiring_one_more() {
    $fixture= $this->fixture();
    $fixture->acquire(self::RATE);
    $fixture->acquire();

    Assert::equals(self::RATE - 1, $fixture->remaining());
  }

  #[Test]
  public function after_having_acquired_limit_and_then_acquiring_limit() {
    $fixture= $this->fixture();
    $fixture->acquire(self::RATE);
    $fixture->acquire(self::RATE);

    Assert::equals(0, $fixture->remaining());
  }

  #[Test]
  public function reset_after_having_waited_until_next_end_of_unit() {
    $fixture= $this->fixture();
    $fixture->acquire();
    $this->clock->forward($this->unit()->seconds());

    Assert::equals(self::RATE, $fixture->remaining());
  }
}