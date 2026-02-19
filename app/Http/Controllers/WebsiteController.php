<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    /**
     * Show the Buy Website landing page.
     */
    public function index()
    {
        return view('pages.website.index');
    }
}
