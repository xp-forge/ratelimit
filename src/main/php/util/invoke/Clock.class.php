<?php namespace util\invoke;

interface Clock {

  /**
   * Returns the current time in seconds and microseconds, presented as fraction
   *
   * @return double
   */
  public function time();

  /**
   * Waits for a given time in seconds and microseconds
   *
   * @param  double $seconds
   * @return void
   */
  public function wait($seconds);
}