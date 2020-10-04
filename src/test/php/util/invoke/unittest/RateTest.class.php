<?php namespace util\invoke\unittest;

use lang\IllegalArgumentException;
use unittest\{Expect, Test, Values};
use util\invoke\{Per, Rate};

class RateTest extends \unittest\TestCase {

  #[Test]
  public function can_create() {
    new Rate(2);
  }

  #[Test]
  public function can_create_with_unit() {
    new Rate(2, Per::$SECOND);
  }

  #[Test, Values([0, -1]), Expect(IllegalArgumentException::class)]
  public function value_cannot_be_zero_or_negative($value) {
    new Rate($value);
  }

  #[Test]
  public function value() {
    $this->assertEquals(2, (new Rate(2))->value());
  }

  #[Test]
  public function unit_defaults_to_per_second() {
    $this->assertEquals(Per::$SECOND, (new Rate(2))->unit());
  }

  #[Test]
  public function unit() {
    $this->assertEquals(Per::$HOUR, (new Rate(5000, Per::$HOUR))->unit());
  }

  #[Test]
  public function equals_itself() {
    $rate= new Rate(100, Per::$MINUTE);
    $this->assertEquals($rate, $rate);
  }

  #[Test]
  public function does_not_equal_other_rate_with_different_unit() {
    $this->assertNotEquals(new Rate(100, Per::$MINUTE), new Rate(100, Per::$SECOND));
  }

  #[Test]
  public function does_not_equal_other_rate_with_different_value() {
    $this->assertNotEquals(new Rate(100, Per::$MINUTE), new Rate(200, Per::$MINUTE));
  }

  #[Test]
  public function string_representation() {
    $this->assertEquals('util.invoke.Rate(100 / hour)', (new Rate(100, Per::$HOUR))->toString());
  }
}