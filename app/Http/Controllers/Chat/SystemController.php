<?php

namespace App\Http\Controllers\Chat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    public function index()
    {
        return response()->view('chat.system', [
            'title' => 'System',
            'pageName' => 'system',
            'user' => \request()->user(),
            'isRoot' => \request()->user()->hasRole('root')
        ]);
    }
}
