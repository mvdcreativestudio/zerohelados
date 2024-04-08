<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Repositories\OrderRepository;
use App\Services\MercadoPagoService;
use MercadoPago\SDK;


use Log;

class CheckoutController extends Controller
{
    protected $orderRepository;
    protected $mercadoPagoService; // Declara una propiedad para el servicio de MercadoPago

    public function __construct(OrderRepository $orderRepository, MercadoPagoService $mercadoPagoService)
    {
        $this->orderRepository = $orderRepository;
        $this->mercadoPagoService = $mercadoPagoService; // Inyecta el servicio de MercadoPago

    }


    public function index()
    {
        $order = null; // Inicializamos $order como nulo, ya que aún no se ha creado una orden
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

        $preferenceId = null;

        // Pasar el ID de la preferencia y otros datos a la vista
        return view('content.e-commerce.front.checkout', compact('order', 'cart', 'subtotal', 'costoEnvio', 'totalPedido', 'envioGratis', 'preferenceId'));
    }




  public function success($orderId)
  {
      // Cargar la orden con productos y sabores relacionados
      $order = Order::with(['products.flavors'])->findOrFail($orderId);
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
        'city' => 'Montevideo',
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
    $costoEnvio = session('costoEnvio', 60); // Costo de envío predeterminado si no se ha establecido en la sesión
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

    try {
        DB::beginTransaction();
        $order = $this->orderRepository->createOrder($clientData, $orderData, session('cart', []));

        // Crear preferencia de pago en MercadoPago
        $items = array_map(function ($item) {
            return [
                'title' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'] ?? $item['old_price'],
            ];
        }, session('cart', []));

        $preferenceData = [
            'items' => $items,
            'payer' => ['email' => $clientData['email']],
            // Puedes agregar más configuraciones a la preferencia según necesites
        ];

        // Crear la preferencia de pago utilizando el servicio de MercadoPago
        $preference = $this->mercadoPagoService->createPreference($preferenceData, $order);

        // Obtener el ID de la preferencia creada
        $preferenceId = $preference->id;

        // Asociar el ID de la preferencia a la orden
        $order->preference_id = $preferenceId;
        $order->save();

        DB::commit();
        session()->forget('cart'); // Limpiar el carrito de compras

        // Redireccionar a la página de pago con el ID de la orden
        return redirect()->route('checkout.payment', $order->id);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error al procesar el pedido: {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
        return back()->withErrors('Error al procesar el pedido. Por favor, intente nuevamente.')->withInput();
    }
}

public function payment($orderId)
{
    // Obtener la orden para obtener la información necesaria
    $order = Order::findOrFail($orderId);

    // Verificar si la orden tiene un estado válido para procesar el pago
    if ($order->payment_status !== 'pending') {
        Log::error("La orden (ID: {$order->id}) no está pendiente de pago.");
        return redirect()->route('checkout.index')->with('error', 'La orden no está pendiente de pago.');
    }

    // Obtener la preferencia de pago asociada a la orden
    $preferenceId = $order->preference_id;

    // Verificar si la preferencia de pago existe
    if (!$preferenceId) {
        Log::error("No se encontró una preferencia de pago asociada a la orden (ID: {$order->id}).");
        return redirect()->route('checkout.index')->with('error', 'No se encontró una preferencia de pago asociada a la orden.');
    }

    // Pasar la información necesaria a la vista de pago
    Log::info("Redirigiendo al usuario al pago de la orden (ID: {$order->id}). Preferencia de pago: {$preferenceId}");
    return view('content.e-commerce.front.checkout-payment', compact('order', 'preferenceId'));
}




}
