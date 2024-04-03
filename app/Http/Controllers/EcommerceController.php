<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;

class EcommerceController extends Controller
{
    public function index()
    {
      $stores = Store::all();
      return view('content.e-commerce.front.index', compact('stores'));
    }

    public function store()
    {
      $categories = ProductCategory::whereHas('products', function ($query) {
        $query->where('status', '=', 1); // Asumiendo que deseas también filtrar por el estado del producto si es necesario
    })->with(['products' => function ($query) {
        $query->where('status', '=', 1);
    }])->get();
      return view('content.e-commerce.front.store', compact('categories'));
    }

    public function marketing()
    {
        return view('content.e-commerce.backoffice.marketing');
    }

    public function settings()
    {
        return view('content.e-commerce.backoffice.settings');
    }

}
