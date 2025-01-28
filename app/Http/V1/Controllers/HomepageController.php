<?php

namespace App\Http\V1\Controllers;

class HomepageController extends Controller
{
    public function index()
    {
        return view('homepage');
    }
}
