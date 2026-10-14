<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $store = $user->store ?? Store::first();

        return view('admin.dashboard', [
            'user' => $user,
            'store' => $store,
            // Basic metric counts for summary cards
            'productsCount' => Product::count(),
            'customersCount' => Customer::count(),
            'salesCount' => Sale::count(),
        ]);
    }
}
