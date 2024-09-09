<?php

namespace App\Http\Controllers;

use App\Repositories\OrderRepository;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;
use App\Repositories\AccountingRepository;

class OrderController extends Controller
{
  /**
   * El repositorio para las operaciones de pedidos.
   *
   * @var OrderRepository
  */
  protected $orderRepository;

  /**
   * El repositorio para las operaciones de contabilidad.
   *
   * @var AccountingRepository
  */
  protected $accountingRepository;

  /**
   * Inyecta el repositorio en el controlador y los middleware.
   *
   * @param  OrderRepository  $orderRepository
   * @param  AccountingRepository  $accountingRepository
  */
  public function __construct(OrderRepository $orderRepository, AccountingRepository $accountingRepository)
  {
      $this->middleware(['check_permission:access_orders', 'user_has_store'])->only(
          [
              'index',
              'create',
              'show',
              'destroy',
              'datatable',
              'orderProductsDatatable'
          ]
      );

      $this->orderRepository = $orderRepository;
      $this->accountingRepository = $accountingRepository;
  }

  /**
   * Muestra una lista de todos los pedidos.
   *
   * @return View
  */
  public function index(): View
  {
    $orders = $this->orderRepository->getAllOrders();
    return view('content.e-commerce.backoffice.orders.orders', $orders);
  }

  /**
   * Muestra el formulario para crear un nuevo pedido.
   *
   * @return View
  */
  public function create(): View
  {
      return view('content.e-commerce.backoffice.orders.add-order');
  }

  /**
   * Almacena un nuevo pedido en la base de datos.
   *
   * @param  StoreOrderRequest  $request
   * @return RedirectResponse
  */
  public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderRepository->store($request);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido realizado con éxito.',
                    'order_id' => $order->id
                ]);
            }

            return redirect()->route('pdv.index')->with('success', 'Pedido realizado con éxito. ID de orden: ' . $order->id);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el pedido. Por favor, intente nuevamente.',
                    'error' => $e->getMessage()
                ], 500);
            }

            // Manejo de errores para solicitudes normales
            return back()->withErrors('Error al procesar el pedido. Por favor, intente nuevamente.')->withInput();
        }
    }


  /**
   * Muestra un pedido específico.
   *
   * @param Order $order
   * @return View
  */
  public function show(Order $order): View
  {
      // Cargar las relaciones necesarias
      $order = $this->orderRepository->loadOrderRelations($order);
      $products = json_decode($order->products, true);
      $clientOrdersCount = $this->orderRepository->getClientOrdersCount($order->client_id);

      return view('content.e-commerce.backoffice.orders.show-order', compact('order', 'products', 'clientOrdersCount'));
  }


  /**
   * Eliminar un pedido específico.
   *
   * @param int $id
   * @return JsonResponse
  */
  public function destroy(int $id): JsonResponse
  {
      try {
          $this->orderRepository->destroyOrder($id);
          return response()->json(['success' => true, 'message' => 'Pedido eliminado correctamente.']);
      } catch (\Exception $e) {
          Log::info($e->getMessage());
          return response()->json(['success' => false, 'message' => 'Error al eliminar el pedido.'], 400);
      }
  }

  /**
   * Obtiene los pedidos para la DataTable.
   *
   * @return mixed
  */
  public function datatable(): mixed
  {
      return $this->orderRepository->getOrdersForDataTable();
  }

  /**
   * Obtiene los productos de un pedido para la DataTable.
   *
   * @param Order $order
   * @return mixed
  */
  public function orderProductsDatatable(Order $order)
  {
      return $this->orderRepository->getOrderProductsForDataTable($order);
  }


  /**
   * Actualiza el estado del pago y envío de un pedido.
   *
   * @param Request $request
   * @param int $orderId
   * @return RedirectResponse
   */
  public function updateStatus(Request $request, int $orderId): RedirectResponse
  {
      $request->validate([
          'payment_status' => 'required|string',
          'shipping_status' => 'required|string',
      ]);

      try {
          $this->orderRepository->updatePaymentStatus($orderId, $request->input('payment_status'));
          $this->orderRepository->updateShippingStatus($orderId, $request->input('shipping_status'));
          return redirect()->back()->with('success', 'Estado del pedido actualizado correctamente.');
      } catch (\Exception $e) {
          Log::error($e->getMessage());
          return redirect()->back()->with('error', 'No se pudo actualizar. Por favor, intente nuevamente');
      }
  }

    /**
     * Maneja la emisión de la factura (CFE).
     *
     * @param Request $request
     * @param int $orderId
     * @return RedirectResponse
    */
    public function emitCFE(Request $request, int $orderId): RedirectResponse
    {
      try {
          $this->orderRepository->emitCFE($orderId, $request);
          return redirect()->back()->with('success', 'Factura emitida correctamente.');
      } catch (\Exception $e) {
          Log::error("Error al emitir CFE para la orden {$orderId}: {$e->getMessage()}");
          return redirect()->back()->with('error', 'Error al emitir la factura. Por favor, intente nuevamente.');
      }
    }
}
