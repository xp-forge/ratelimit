<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;

class PermitsTest extends AbstractRateLimitingTest {

  /** @return var[][] */
  protected function permits() { return [[1], [2], [1000], [3600]]; }

  #[@test, @values('permits')]
  public function one_per_default($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire());
  }

  #[@test, @values('permits')]
  public function try_one_per_default($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $this->assertTrue($fixture->tryAcquiring());
  }

  #[@test, @values('permits')]
  public function one_explicitely($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire(1));
  }

  #[@test, @values('permits')]
  public function try_one_explicitely($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $this->assertTrue($fixture->tryAcquiring(1));
  }

  #[@test, @values('permits')]
  public function exact_number_of_permits($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire($permits));
  }

  #[@test, @values('permits')]
  public function try_exact_number_of_permits($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $this->assertTrue($fixture->tryAcquiring($permits));
  }

  #[@test, @values('permits')]
  public function exact_number_of_permits_after_sleeping_until_next($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $fixture->acquire($permits);
    self::$clock->forward($fixture->rate()->unit()->seconds());
    $this->assertDouble(0.0, $fixture->acquire($permits));
  }

  #[@test, @values('permits')]
  public function try_exact_number_of_permits_after_sleeping_until_next($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $fixture->acquire($permits);
    self::$clock->forward($fixture->rate()->unit()->seconds());
    $this->assertTrue($fixture->tryAcquiring($permits));
  }

  #[@test, @values('permits')]
  public function one_more_than_permitted_causes_sleeping($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $fixture->acquire($permits);
    $this->assertEquals((double)$fixture->rate()->unit()->seconds(), $fixture->acquire());
  }

  #[@test, @values('permits')]
  public function fails_trying_to_acquire_one_more_than_permitted_without_timeout($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $fixture->acquire($permits);
    $this->assertFalse($fixture->tryAcquiring(1));
  }

  #[@test, @values('permits')]
  public function fails_trying_to_acquire_one_more_than_permitted_with_zero_timeout($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $fixture->acquire($permits);
    $this->assertFalse($fixture->tryAcquiring(1, 0.0));
  }

  #[@test, @values('permits')]
  public function succeeds_trying_to_acquire_one_more_than_permitted_after_exact_timeout($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $fixture->acquire($permits);
    $this->assertTrue($fixture->tryAcquiring(1, $fixture->rate()->unit()->seconds()));
  }

  #[@test, @values('permits')]
  public function succeeds_trying_to_acquire_one_more_than_permitted_after_longer_timeout($permits) {
    $fixture= new RateLimiting($permits, self::$clock);
    $fixture->acquire($permits);
    $this->assertTrue($fixture->tryAcquiring(1, $fixture->rate()->unit()->seconds() + 0.5));
  }
}