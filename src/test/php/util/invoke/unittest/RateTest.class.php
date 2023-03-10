<?php namespace util\invoke\unittest;

use lang\IllegalArgumentException;
use test\Assert;
use test\{Expect, Test, Values};
use util\invoke\{Per, Rate};

class RateTest {

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
    Assert::equals(2, (new Rate(2))->value());
  }

  #[Test]
  public function unit_defaults_to_per_second() {
    Assert::equals(Per::$SECOND, (new Rate(2))->unit());
  }

  #[Test]
  public function unit() {
    Assert::equals(Per::$HOUR, (new Rate(5000, Per::$HOUR))->unit());
  }

  #[Test]
  public function equals_itself() {
    $rate= new Rate(100, Per::$MINUTE);
    Assert::equals($rate, $rate);
  }

  #[Test]
  public function does_not_equal_other_rate_with_different_unit() {
    Assert::notEquals(new Rate(100, Per::$MINUTE), new Rate(100, Per::$SECOND));
  }

  #[Test]
  public function does_not_equal_other_rate_with_different_value() {
    Assert::notEquals(new Rate(100, Per::$MINUTE), new Rate(200, Per::$MINUTE));
  }

  #[Test]
  public function string_representation() {
    Assert::equals('util.invoke.Rate(100 / hour)', (new Rate(100, Per::$HOUR))->toString());
  }
}