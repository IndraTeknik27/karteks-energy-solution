<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->first();

        if (! $page) {
            abort(404, 'Halaman tidak ditemukan.');
        }

        return view('pages.page', compact('page'));
    }
}