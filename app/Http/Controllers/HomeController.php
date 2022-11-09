<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Encompasses;
use App\Inventries;
use App\Orders;
use Illuminate\Support\Facades\Auth;




class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $current_date = date('Y-m-d');
        $orders= Orders::where('created_at', now())->count();
        $users = User::whereDate('created_at', now())->count();
        $trucks = Encompasses::whereDate('created_at', now())->count();
        $inventries = Inventries::whereDate('created_at', now())->count();
        $sales     = Orders::whereDate('created_at', now())->sum('total');
        return view('home', compact( 'orders', 'users', 'trucks', 'inventries', 'sales', 'current_date'));
    }
}
