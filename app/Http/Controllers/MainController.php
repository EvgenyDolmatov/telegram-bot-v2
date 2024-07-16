<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MainController extends Controller
{
    public function webhook(Request $request): void
    {
        Log::debug('test');
    }
}
