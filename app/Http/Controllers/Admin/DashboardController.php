<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'orders' => Order::count(),
            'categories' => Category::count(),
            'users' => User::count(),
            'revenue' => Order::where(function ($q) {
                // Chargily: only paid orders
                $q->where('payment_method', 'chargily')->where('payment_status', 'paid');
            })->orWhere(function ($q) {
                // COD: only delivered orders
                $q->where('payment_method', 'cod')->where('status', 'delivered');
            })->sum('total'),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
