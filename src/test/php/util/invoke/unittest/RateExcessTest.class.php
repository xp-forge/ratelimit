<?php namespace util\invoke\unittest;

use unittest\Test;
use util\invoke\{Per, Rate, RateLimiting};

class RateExcessTest extends AbstractRateLimitingTest {
  private $fixture;

  /**
   * Sets up fixture
   *
   * @return void
   */
  public function setUp() {
    parent::setUp();
    $this->fixture= new RateLimiting(10, self::$clock);
  }

  #[Test]
  public function try_acquiring_10_times_than_limit() {
    $this->assertTrue($this->fixture->tryAcquiring(100));
    $this->assertDouble(10.0, $this->fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [100 *    *    *    *    *    *    *    *    *    [1
    // [10  [10  [10  [10  [10  [10  [10  [10  [10  [10  [1
  }

  #[Test]
  public function try_acquiring_one_more_than_limit() {
    $this->assertTrue($this->fixture->tryAcquiring(11));
    $this->assertDouble(1.1, $this->fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [11  [1 
    // [10  [1   [1
  }

  #[Test]
  public function try_acquiring_the_limit() {
    $this->assertTrue($this->fixture->tryAcquiring(10));
    $this->assertDouble(1.0, $this->fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [10  [1
  }

  #[Test]
  public function try_acquiring_twice_the_limit() {
    $this->assertTrue($this->fixture->tryAcquiring(20));
    $this->assertDouble(2.0, $this->fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [20  *    [1
    // [10  [10  [1
  }

  #[Test]
  public function try_acquiring_excess_twice() {
    $this->assertTrue($this->fixture->tryAcquiring(11));
    $this->assertDouble(1.1, $this->fixture->acquire(11));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 
    // [11  *    [11
    // [10  [1   [10  [1
  }

  #[Test]
  public function remaining_after_acquiring_excess() {
    $this->fixture->acquire(11);
    $this->assertEquals(0, $this->fixture->remaining());
  }

  #[Test]
  public function resetTime_after_acquiring_excess() {
    $this->fixture->acquire(11);
    $this->assertDouble(self::CLOCK_START + 1.0, $this->fixture->resetTime());
  }
}