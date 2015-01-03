<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;

class VaryingRatesTest extends AbstractRateLimitingTest {

  /** @return var[][] */
  protected function rates() { return [[1], [2], [1000], [3600]]; }

  #[@test, @values('rates')]
  public function one_per_default($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire());
  }

  #[@test, @values('rates')]
  public function try_one_per_default($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $this->assertTrue($fixture->tryAcquiring());
  }

  #[@test, @values('rates')]
  public function one_explicitely($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire(1));
  }

  #[@test, @values('rates')]
  public function try_one_explicitely($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $this->assertTrue($fixture->tryAcquiring(1));
  }

  #[@test, @values('rates')]
  public function exact_number_of_permits($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire($rate));
  }

  #[@test, @values('rates')]
  public function try_exact_number_of_permits($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $this->assertTrue($fixture->tryAcquiring($rate));
  }

  #[@test, @values('rates')]
  public function exact_number_of_permits_after_sleeping_until_next($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $fixture->acquire($rate);
    self::$clock->forward($fixture->rate()->unit()->seconds());
    $this->assertDouble(0.0, $fixture->acquire($rate));
  }

  #[@test, @values('rates')]
  public function try_exact_number_of_permits_after_sleeping_until_next($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $fixture->acquire($rate);
    self::$clock->forward($fixture->rate()->unit()->seconds());
    $this->assertTrue($fixture->tryAcquiring($rate));
  }

  #[@test, @values('rates')]
  public function one_more_than_permitted_causes_sleeping($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $fixture->acquire($rate);
    $this->assertEquals((double)$fixture->rate()->unit()->seconds(), $fixture->acquire());
  }

  #[@test, @values('rates')]
  public function fails_trying_to_acquire_one_more_than_permitted_without_timeout($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $fixture->acquire($rate);
    $this->assertFalse($fixture->tryAcquiring(1));
  }

  #[@test, @values('rates')]
  public function fails_trying_to_acquire_one_more_than_permitted_with_zero_timeout($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $fixture->acquire($rate);
    $this->assertFalse($fixture->tryAcquiring(1, 0.0));
  }

  #[@test, @values('rates')]
  public function succeeds_trying_to_acquire_one_more_than_permitted_after_exact_timeout($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $fixture->acquire($rate);
    $this->assertTrue($fixture->tryAcquiring(1, $fixture->rate()->unit()->seconds()));
  }

  #[@test, @values('rates')]
  public function succeeds_trying_to_acquire_one_more_than_permitted_after_longer_timeout($rate) {
    $fixture= new RateLimiting($rate, self::$clock);
    $fixture->acquire($rate);
    $this->assertTrue($fixture->tryAcquiring(1, $fixture->rate()->unit()->seconds() + 0.5));
  }
}