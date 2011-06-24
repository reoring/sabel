<?php

class SabelTestCase extends PHPUnit_Framework_TestCase
{
  protected static function createSuite($name)
  {
    return new PHPUnit_Framework_TestSuite($name);
  }
}
