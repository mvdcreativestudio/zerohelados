<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Repositories\OrderRepository;
use Log;

class CheckoutController extends Controller
{

  protected $orderRepository;

  public function __construct(OrderRepository $orderRepository)
  {
      $this->orderRepository = $orderRepository;
  }


  public function index() {
    $cart = session('cart', []);
    $subtotal = 0;
    $costoEnvio = 60;

    foreach ($cart as $item) {
        $price = !empty($item['price']) ? $item['price'] : $item['old_price'];
        $subtotal += $price * $item['quantity'];
    }

    $envioGratis = $subtotal > 100; // Envío gratis para pedidos mayores a $100
    if ($envioGratis) {
        $costoEnvio = 0;
    }

    $totalPedido = $subtotal + $costoEnvio;

    return view('content.e-commerce.front.checkout', compact('cart', 'subtotal', 'costoEnvio', 'totalPedido', 'envioGratis'));
  }

  public function success($orderId)
  {
      // Buscar la orden por su ID y cargar los productos relacionados
      $order = Order::with(['products'])->findOrFail($orderId);
      
      // Pasar los datos de la orden a la vista, incluidos los productos
      return view('content.e-commerce.front.checkout-success', compact('order'));
  }




  public function store(Request $request)
  {
    // Validación de los datos recibidos
    $validatedData = $request->validate([
        'name' => 'required|max:255',
        'lastname' => 'required|max:255',
        'address' => 'required',
        'phone' => 'required',
        'email' => 'required|email',
        'payment_method' => 'required',
    ]);

    // Preparar datos del cliente
    $clientData = [
        'name' => $validatedData['name'],
        'lastname' => $validatedData['lastname'],
        'type' => 'individual',
        'state' => 'Montevideo',
        'country' => 'Uruguay',
        'address' => $validatedData['address'],
        'phone' => $validatedData['phone'],
        'email' => $validatedData['email'],
    ];

    // Preparar datos de la orden, excluyendo los productos que se manejan en el repositorio
    $subtotal = 0;
    foreach (session('cart') as $item) {
        $price = $item['price'] ?? $item['old_price'];
        $subtotal += $price * $item['quantity'];
    }
    $costoEnvio = session('costoEnvio', 0);
    $total = $subtotal + $costoEnvio;

    $orderData = [
        'date' => now(),
        'origin' => 'ecommerce',
        'store_id' => 1, // Asegúrate de que este ID es correcto para tu lógica de negocio
        'subtotal' => $subtotal,
        'tax' => 0, // Ajusta según sea necesario
        'shipping' => $costoEnvio,
        'total' => $total,
        'payment_status' => 'pending',
        'shipping_status' => 'pending',
        'payment_method' => $validatedData['payment_method'],
        'shipping_method' => 'peya', // Asegúrate de ajustar según tu lógica de negocio
    ];

    // Procesar la orden utilizando el repositorio
    try {
        $order = $this->orderRepository->createOrder($clientData, $orderData, session('cart', []));
        session()->forget('cart'); // Limpiar el carrito de compras
        return redirect()->route('checkout.success', ['order' => $order->id]);
      } catch (\Exception $e) {
        Log::error("Error al procesar el pedido: {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
        return back()->withErrors('Error al procesar el pedido. Por favor, intente nuevamente.')->withInput();
    }
  }


}
