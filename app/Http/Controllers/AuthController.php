<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiLoginRequest;
use App\Traits\ApiResponces;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponces;
    public function login(ApiLoginRequest $request) {

        return $this->ok($request->get('email'));
    }
    public function register(ApiLoginRequest $request) {

        return $this->ok('Register endpoint');
    }
}
