<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
   use CreatesApplication;
   
   protected function cartKey($productId, $size = '', $sugar = null, $ice = '', $note = ''): string
   {
       return md5($productId . '|' . $size . '|' . ($sugar ?? '') . '|' . $ice . '|' . $note);
   }
}
