<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        return view('admin.isp.dashboard', compact('user'));
    }
}
 