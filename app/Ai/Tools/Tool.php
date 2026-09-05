<?php

namespace App\Ai\Tools;

interface Tool
{
   public function definition();
   public function use(array $arguments = []);
}