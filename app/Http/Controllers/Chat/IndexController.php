<?php

namespace App\Http\Controllers\Chat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{

    public function index()
    {
        return view('chat.home', [
            'title' => 'Home',
            'user' => \request()->user()
        ]);
    }
}
