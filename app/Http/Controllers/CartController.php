<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;
use App\Models\Flavor;
use Illuminate\Support\Facades\Redirect;

class CartController extends Controller
{
    public function index()
    {
        return view('content.e-commerce.front.cart');
    }

    public function selectStore(Request $request)
    {
        $store = Store::find($request->input('storeId'));
        if (!$store) {
            return Redirect::back()->with('error', 'La tienda no existe.');
        }
        session(['store' => ['id' => $store->id, 'name' => $store->name, 'address' => $store->address]]);
        return Redirect::route('store', ['storeId' => $store->id]);
    }

    public function addToCart(Request $request, $productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return Redirect::back()->with('error', 'El producto no existe.');
        }

        $cart = session('cart', []);
        $flavorIds = $request->input('flavors', []);
        $flavors = Flavor::findMany($flavorIds)->pluck('name', 'id');

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += 1;
        } else {
            $cart[$productId] = [
                "id" => $product->id,
                "name" => $product->name,
                "sku" => $product->sku,
                "description" => $product->description,
                "type" => $product->type,
                "quantity" => 1,
                "old_price" => $product->old_price,
                "price" => $product->price,
                "image" => $product->image,
                "flavors" => $flavors->toArray()
            ];
        }

        session(['cart' => $cart]);
        $this->updateSubtotal();
        return Redirect::back()->with('success', 'Producto añadido al carrito con éxito!');
    }

    public function updateCart(Request $request)
    {
        $productId = $request->id;
        $quantity = $request->quantity;

        $cart = session('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            session(['cart' => $cart]);
            $this->updateSubtotal();
            return Redirect::back()->with('success', 'Carrito actualizado con éxito!');
        }

        return Redirect::back()->with('error', 'Producto no encontrado en el carrito.');
    }

    public function removeFromCart(Request $request)
    {
        $productId = $request->id;
        $cart = session('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session(['cart' => $cart]);
            $this->updateSubtotal();
            return Redirect::back()->with('success', 'Producto eliminado del carrito con éxito!');
        }

        return Redirect::back()->with('error', 'Producto no encontrado en el carrito.');
    }

    public function clearCart()
    {
        session()->forget('cart');
        session()->forget('subtotal');
        return Redirect::back()->with('success', 'Carrito vaciado con éxito.');
    }

    public function clearSession()
    {
        session()->flush();
        return Redirect::to('/')->with('success', 'Sesión limpiada correctamente.');
    }

    private function updateSubtotal()
    {
        $cart = session('cart', []);
        $subtotal = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));

        session(['subtotal' => $subtotal]);
    }
}
