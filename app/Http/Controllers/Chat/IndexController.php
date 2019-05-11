<?php

namespace App\Http\Controllers\Chat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function home()
    {
        return view('chat.home');
    }
}
