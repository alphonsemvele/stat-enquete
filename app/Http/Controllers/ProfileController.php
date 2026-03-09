<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function logout()
    {
        Auth::logout();
        return Redirect::route('vitrine');
     }
}
