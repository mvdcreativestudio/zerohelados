<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Client;
use App\Models\Store;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
  // Crear una orden con los datos del cliente, la orden y el carrito
  public function createOrder(array $clientData, array $orderData, array $cart)
  {
      DB::beginTransaction();
      try {
          // Buscar el cliente por email o crear uno nuevo si no existe
          $client = Client::firstOrCreate(
              ['email' => $clientData['email']],
              $clientData
          );

          // Crear la orden y asociarla con el cliente encontrado o creado
          $order = new Order($orderData);
          $order->client()->associate($client);
          $order->save();

          // Asociar los productos a la orden
          foreach ($cart as $item) {
              $order->products()->attach($item['id'], [
                  'quantity' => $item['quantity'],
                  'price' => $item['price'] ?? $item['old_price'],
              ]);
          }

          DB::commit();
          return $order;
      } catch (\Exception $e) {
          DB::rollBack();
          throw $e;
      }
  }

  // Obtener datos para Datatable del dashboard
  public function getOrdersForDataTable()
  {
      $query = Order::select([
                  'orders.id',
                  'orders.date',
                  'orders.client_id',
                  'orders.store_id',
                  'orders.subtotal',
                  'orders.tax',
                  'orders.shipping',
                  'orders.coupon_id',
                  'orders.coupon_amount',
                  'orders.discount',
                  'orders.total',
                  'orders.payment_status',
                  'orders.shipping_status',
                  'orders.payment_method',
                  'orders.shipping_method',
                  'orders.shipping_tracking',
                  'clients.email as client_email',
                  'stores.name as store_name',
                  DB::raw("CONCAT(clients.name, ' ', clients.lastname) as client_name")
                ])
              ->join('clients', 'orders.client_id', '=', 'clients.id')
              ->join('stores', 'orders.store_id', '=', 'stores.id');


      return DataTables::of($query)->make(true);
  }



}
