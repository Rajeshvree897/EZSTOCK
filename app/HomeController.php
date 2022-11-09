<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Encompasses;
use App\Inventries;


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

        $bins= Encompasses::with('users')->get();
        $inventries= Inventries::with('trucks','user')->get();
        $orders   = Order::with('order_user')->get();
        $users = User::get();
        return view('home', compact('bins', 'inventries','orders', 'users'));
    }
}
