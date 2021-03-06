<?php namespace util\invoke\unittest;

use unittest\{Test, Values};
use util\invoke\{Rate, RateLimiting};

/**
 * Base class for `RateLimiting::remaining()` tests.
 *
 * @see  xp://util.invoke.unittest.RemainingPerSecondTest
 * @see  xp://util.invoke.unittest.RemainingPerHourTest
 */
abstract class RemainingTest extends AbstractRateLimitingTest {
  const RATE = 1000;
  private $fixture;

  /**
   * Returns the unit to use. Implemented in subclasses.
   *
   * @return util.invoke.Per
   */
  protected abstract function unit();

  /**
   * Sets up fixture
   *
   * @return void
   */
  public function setUp() {
    parent::setUp();
    $this->fixture= new RateLimiting(new Rate(self::RATE, $this->unit()), self::$clock);
  }

  #[Test]
  public function initially_equals_rate() {
    $this->assertEquals(self::RATE, $this->fixture->remaining());
  }

  #[Test]
  public function after_having_acquired_one() {
    $this->fixture->acquire();
    $this->assertEquals(self::RATE - 1, $this->fixture->remaining());
  }

  #[Test]
  public function after_having_acquired_limit() {
    $this->fixture->acquire(self::RATE);
    $this->assertEquals(0, $this->fixture->remaining());
  }

  #[Test, Values([2, 3, 4])]
  public function after_having_acquired_one_multiple_times($times) {
    for ($i= 0; $i < $times; $i++) {
      $this->fixture->acquire();
    }
    $this->assertEquals(self::RATE - $times, $this->fixture->remaining());
  }

  #[Test]
  public function after_having_acquired_limit_and_then_acquiring_one_more() {
    $this->fixture->acquire(self::RATE);
    $this->fixture->acquire();
    $this->assertEquals(self::RATE - 1, $this->fixture->remaining());
  }

  #[Test]
  public function after_having_acquired_limit_and_then_acquiring_limit() {
    $this->fixture->acquire(self::RATE);
    $this->fixture->acquire(self::RATE);
    $this->assertEquals(0, $this->fixture->remaining());
  }

  #[Test]
  public function reset_after_having_waited_until_next_end_of_unit() {
    $this->fixture->acquire();
    self::$clock->forward($this->unit()->seconds());
    $this->assertEquals(self::RATE, $this->fixture->remaining());
  }
}