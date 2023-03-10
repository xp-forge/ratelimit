<?php namespace util\invoke\unittest;

use test\Assert;
use test\{Test, Values};
use util\invoke\RateLimiting;

class VaryingRatesTest extends AbstractRateLimitingTest {

  /** @return var[][] */
  protected function rates() { return [[1], [2], [1000], [3600]]; }

  #[Test, Values(from: 'rates')]
  public function one_per_default($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $this->assertDouble(0.0, $fixture->acquire());
  }

  #[Test, Values(from: 'rates')]
  public function try_one_per_default($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    Assert::true($fixture->tryAcquiring());
  }

  #[Test, Values(from: 'rates')]
  public function one_explicitely($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $this->assertDouble(0.0, $fixture->acquire(1));
  }

  #[Test, Values(from: 'rates')]
  public function try_one_explicitely($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    Assert::true($fixture->tryAcquiring(1));
  }

  #[Test, Values(from: 'rates')]
  public function exact_number_of_permits($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $this->assertDouble(0.0, $fixture->acquire($rate));
  }

  #[Test, Values(from: 'rates')]
  public function try_exact_number_of_permits($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    Assert::true($fixture->tryAcquiring($rate));
  }

  #[Test, Values(from: 'rates')]
  public function exact_number_of_permits_after_sleeping_until_next($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire($rate);
    $this->clock->forward($fixture->rate()->unit()->seconds());
    $this->assertDouble(0.0, $fixture->acquire($rate));
  }

  #[Test, Values(from: 'rates')]
  public function try_exact_number_of_permits_after_sleeping_until_next($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire($rate);
    $this->clock->forward($fixture->rate()->unit()->seconds());
    Assert::true($fixture->tryAcquiring($rate));
  }

  #[Test, Values(from: 'rates')]
  public function one_more_than_permitted_causes_sleeping($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire($rate);
    Assert::equals((double)$fixture->rate()->unit()->seconds(), $fixture->acquire());
  }

  #[Test, Values(from: 'rates')]
  public function fails_trying_to_acquire_one_more_than_permitted_without_timeout($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire($rate);
    Assert::false($fixture->tryAcquiring(1));
  }

  #[Test, Values(from: 'rates')]
  public function fails_trying_to_acquire_one_more_than_permitted_with_zero_timeout($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire($rate);
    Assert::false($fixture->tryAcquiring(1, 0.0));
  }

  #[Test, Values(from: 'rates')]
  public function succeeds_trying_to_acquire_one_more_than_permitted_after_exact_timeout($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire($rate);
    Assert::true($fixture->tryAcquiring(1, $fixture->rate()->unit()->seconds()));
  }

  #[Test, Values(from: 'rates')]
  public function succeeds_trying_to_acquire_one_more_than_permitted_after_longer_timeout($rate) {
    $fixture= new RateLimiting($rate, $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire($rate);
    Assert::true($fixture->tryAcquiring(1, $fixture->rate()->unit()->seconds() + 0.5));
  }
}