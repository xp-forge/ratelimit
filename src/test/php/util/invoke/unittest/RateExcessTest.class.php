<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;
use util\invoke\Per;
use util\invoke\Rate;

class RateExcessTest extends AbstractRateLimitingTest {

  #[@test]
  public function try_acquiring_100_more_than_limit() {
    $fixture= new RateLimiting(1, self::$clock);
    $this->assertTrue($fixture->tryAcquiring(11));
    $this->assertEquals(10.0, $fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 - 12 
    // [11] *    *    *    *    *    *    *    *    *    *    [1]
  }

  #[@test]
  public function try_acquiring_one_more_than_limit() {
    $fixture= new RateLimiting(10, self::$clock);
    $this->assertTrue($fixture->tryAcquiring(11));
    $this->assertEquals(1.0, $fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 - 12 
    // [11] [1]
  }

  #[@test, @ignore]
  public function try_acquiring_twice_the_limit() {
    $fixture= new RateLimiting(10, self::$clock);
    $this->assertTrue($fixture->tryAcquiring(20));
    $this->assertEquals(2.0, $fixture->acquire(1));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 - 12 
    // [20] *    [1]
  }

  #[@test, @ignore]
  public function try_acquiring_excess_twice() {
    $fixture= new RateLimiting(10, self::$clock);
    $this->assertTrue($fixture->tryAcquiring(11));
    $this->assertEquals(2.0, $fixture->acquire(11));

    // 01 - 02 - 03 - 04 - 05 - 06 - 07 - 08 - 09 - 10 - 11 - 12 
    // [20] *    [11]
  }

  #[@test]
  public function remaining_after_having_acquired_limit_with_excess_also_zero() {
    $fixture= new RateLimiting(1000, self::$clock);
    $fixture->acquire(1001);
    $this->assertEquals(0, $fixture->remaining());
  }

  #[@test, @values([0.0, 1.0, 1800.1, 3600.0, 6666.6])]
  public function resetTime_after_excess_and_sleeping($sleep) {
    $fixture= new RateLimiting(new Rate(1000, per::$HOUR), self::$clock);
    $fixture->acquire(1001);
    self::$clock->forward($sleep);
    $this->assertDouble(self::CLOCK_START + 3600.0, $fixture->resetTime());
  }
}