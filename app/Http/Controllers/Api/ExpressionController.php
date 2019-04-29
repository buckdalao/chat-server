<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Tool\ExpressionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExpressionController extends Controller
{
    protected $expressionRepository;

    public function __construct(ExpressionRepository $expressionRepository)
    {
        $this->expressionRepository = $expressionRepository;
    }

    public function expression()
    {
        return response()->json($this->expressionRepository->getAll([
            'id', 'phrase', 'type', 'common', 'path'
        ]));
    }
}
