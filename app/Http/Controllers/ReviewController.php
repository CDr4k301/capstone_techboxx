<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'product_type' => ['required', 'string'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'title'      => ['nullable', 'string', 'max:100'],
            'content'    => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['product_type'] === 'cases') {
            $data['product_type'] = 'pc_cases';
        }

        // attach current user if logged in
        $data['user_id'] = Auth::id();

        // always use logged-in user's name, otherwise fallback to "Anonymous"
        $data['name'] = Auth::check() ? Auth::user()->first_name : 'Anonymous';

        Review::create($data);

        return back()->with('success', 'Thanks for your review!');
    }
}