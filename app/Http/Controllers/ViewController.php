<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function index_page_view ()
    {
      return view('admin-db.login'); 
    }
}
