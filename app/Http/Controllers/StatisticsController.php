<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index()
    {


        // Return the address with Inertia
        return Inertia::render('Stats/index');
    }
}
