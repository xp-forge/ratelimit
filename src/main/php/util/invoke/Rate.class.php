<?php namespace util\invoke;

use lang\IllegalArgumentException;

/**
 * Encapsulates rates with their units
 *
 * @test  xp://util.invoke.unittest.RateTest
 */
class Rate {
  private $value, $unit;

  /**
   * Creates a new rate
   *
   * @param  int $value Queries per unit to permit. Must be greater than 0.
   * @param  util.invoke.Per $unit Unit to use, defaulting to Per::$SECOND
   * @throws lang.IllegalArgumentException
   */
  public function __construct($value, Per $unit= null) {
    if ($value <= 0) {
      throw new IllegalArgumentException('Value cannot be zero or negative');
    }

    $this->value= $value;
    $this->unit= $unit ?: Per::$SECOND;
  }

  /** @return int */
  public function value() { return $this->value; }

  /** @return util.invoke.Per */
  public function unit() { return $this->unit; }

  /** @return string */
  public function toString() {
    return nameof($this).'('.$this->value.' / '.strtolower($this->unit->name()).')';
  }

  /**
   * Checks whether another value is equal to this rate instance
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $this->value === $cmp->value && $this->unit->equals($cmp->unit);
  }
}