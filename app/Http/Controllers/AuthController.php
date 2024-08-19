<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponces;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponces;
    public function login() {
        return $this->ok('Hello, login!');
    }
}
