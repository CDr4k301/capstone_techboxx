<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderedBuild;

class OrderDetailsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get orders for this user through userBuild relationship
        $orders = OrderedBuild::with([
            'user', 
            'userBuild.case',
            'userBuild.cpu',
            'userBuild.motherboard',
            'userBuild.gpu',
            'userBuild.ram',
            'userBuild.storage',
            'userBuild.psu',
            'userBuild.cooler',  
            'userBuild.user'])
            ->whereHas('userBuild.user', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereNull('pickup_date')
            ->orderBy('created_at', 'desc')
            ->paginate(2);

        return view('customer.orderdetails', compact('orders'));
    }
}