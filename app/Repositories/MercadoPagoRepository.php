<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoPagoService;
use App\Repositories\EmailNotificationsRepository;
use App\Repositories\PedidosYaRepository;

class MercadoPagoRepository
{
  /**
   * El repositorio de PedidosYa para la gestión de envíos.
   *
   * @var PedidosYaRepository
  */
  protected $pedidosYaRepository;

  /**
   * El repositorio de Email para envío de correos
   *
   * @var EmailNotificationsRepository
   */
  protected $emailNotificationsRepository;

  /**
   * Inyecta los repositorios necesarios.
   *
   * @param PedidosYaRepository $pedidosYaRepository
   * @param EmailNotificationsRepository $emailNotificationsRepository
  */
  public function __construct(PedidosYaRepository $pedidosYaRepository, EmailNotificationsRepository $emailNotificationsRepository)
  {
    $this->pedidosYaRepository = $pedidosYaRepository;
    $this->emailNotificationsRepository = $emailNotificationsRepository;
  }


  /**
   * Maneja las notificaciones webhook de MercadoPago.
   *
   * @param Request $request
   * @param MercadoPagoService $mpService
   * @return array
  */
public function handleWebhook(Request $request, MercadoPagoService $mpService): array
{
    $payload = $request->all();

    $dataId = $payload['data']['id'] ?? null;
    $resourceUrl = $payload['resource'] ?? null;
    $id = $dataId ?: basename(parse_url($resourceUrl, PHP_URL_PATH));
    $topic = $payload['topic'] ?? $payload['type'] ?? null;

    // DEBUG LOG
    Log::info('Webhook recibido:', [
        'headers' => $request->headers->all(),
        'body' => $payload
    ]);

    // Si no hay ID, devolvemos error
    if (!$id) {
        return ['message' => ['error' => 'Invalid request data'], 'status' => 400];
    }

    // Si viene la cabecera x-signature, validamos HMAC
    if ($request->hasHeader('x-signature')) {
        $xSignatureParts = explode(',', $request->header('x-signature'));
        $xSignatureData = $this->parseXSignature($xSignatureParts);
        $ts = $xSignatureData['ts'] ?? '';
        $receivedHash = $xSignatureData['v1'] ?? '';

        if (!$mpService->verifyHMAC($id, $request->header('x-request-id'), $ts, $receivedHash)) {
            Log::warning('HMAC fallido para webhook');
            return ['message' => ['error' => 'HMAC verification failed'], 'status' => 400];
        }

        Log::info('HMAC verificado con éxito');
    } else {
        Log::info('Webhook recibido sin HMAC, asumiendo flujo de Checkout Pro');
    }

    // Procesamos la notificación
    return $this->processNotification($topic, $id, $mpService);
}
    


  /**
   * Procesa las notificaciones según el tipo de evento.
   *
   * @param string $topic
   * @param string $id
   * @param MercadoPagoService $mpService
   * @return array
  */
  private function processNotification(string $topic, string $id, MercadoPagoService $mpService): array
  {
    switch ($topic) {
        case 'payment':
            Log::info("Procesando 'payment' con ID: $id");
            $paymentInfo = $mpService->getPaymentInfo($id);

            if ($paymentInfo) {
                Log::info("Información del pago recibida:", $paymentInfo);

                $orderId = $paymentInfo['metadata']['order_id']; // Obtiene el ID de la orden desde el metadata
                $this->updatePaymentStatus($orderId, 'paid'); // Método para actualizar el estado del pago

                Log::info("Estado del pago actualizado a 'paid' para la orden con ID: $orderId");
                return ['message' => ['message' => 'Notification received'], 'status' => 200];
            } else {
                Log::error("No se pudo obtener información del pago con ID: $id");
                return ['message' => ['error' => 'Payment information not found'], 'status' => 400];
            }

        case 'merchant_order':
            // Implementar lógica similar para 'merchant_order'
            return ['message' => ['message' => 'Notification received'], 'status' => 200];

        default:
            Log::warning("Tipo de notificación no soportado: $topic");
            return ['message' => ['error' => 'Unsupported notification type'], 'status' => 400];
    }
  }

  /**
   * Actualiza el estado del pago de una orden.
   *
   * @param int $orderId
   * @param string $status
   * @return bool
  */
  public function updatePaymentStatus(int $orderId, string $status): bool
  {
      $order = Order::find($orderId);

      if ($order) {
          $order->payment_status = $status;
          $order->save();

          Log::info("Estado de la orden actualizado a '$status' para la orden con ID: $orderId");

          // if ($status === 'paid') {
          //     $this->sendOrderEmails($order);
          // }

          if ($status === 'paid' && $order->shipping_method === 'peya') {
              $this->createPeYaShipping($order);
          }

          return true;
      } else {
          Log::error("No se encontró la orden con ID: $orderId");
          return false;
      }
  }

  /**
   * Envía correos electrónicos de notificación de orden.
   *
   * @param Order $order
   * @return void
   */
  private function sendOrderEmails(Order $order)
  {
      $variables = [
          'order_id' => $order->id,
          'client_name' => $order->client->name,
          'client_lastname' => $order->client->lastname,
          'client_email' => $order->client->email,
          'client_phone' => $order->client->phone,
          'client_address' => $order->client->address,
          'client_city' => $order->client->city,
          'client_state' => $order->client->state,
          'client_country' => $order->client->country,
          'order_subtotal' => $order->subtotal,
          'order_shipping' => $order->shipping,
          'coupon_amount' => $order->coupon_amount,
          'order_total' => $order->total,
          'order_date' => $order->date,
          'order_items' => $order->products,
          'order_shipping_method' => $order->shipping_method,
          'order_payment_method' => $order->payment_method,
          'order_payment_status' => $order->payment_status,
          'store_name' => $order->store->name,
      ];
      // Enviar correo al administrador
      $this->emailNotificationsRepository->sendNewOrderEmail($variables);
      // Enviar correo al cliente
      $this->emailNotificationsRepository->sendNewOrderClientEmail($variables);
  }

  /**
   * Crea el envío en PedidosYa si el método de envío es 'peya'.
   *
   * @param Order $order
   * @return void
  */
  private function createPeYaShipping(Order $order): void
  {
      $request = new Request([
          'estimate_id' => $order->estimate_id,
          'store_id' => $order->store_id, // Asegúrate de incluir el store_id
          'delivery_offer_id' => $order->delivery_offer_id,
      ]);
      $response = $this->pedidosYaRepository->confirmOrderRequest($request);
      if (isset($response['shippingId'])) {
          $order->shipping_id = $response['shippingId'];
          $order->shipping_status = $response['status'];
          $order->save();
          Log::info("Envío creado con éxito en PedidosYa para la orden con ID: {$order->id}");
      } else {
          Log::error("Error al crear el envío en PedidosYa para la orden con ID: {$order->id}", ['response' => $response]);
      }
  }

  /**
   * Analiza los datos de la firma X-Signature.
   *
   * @param array $xSignatureParts
   * @return array
  */
    private function parseXSignature(array $xSignatureParts): array
    {
        $xSignatureData = [];

        foreach ($xSignatureParts as $part) {
            $keyValue = explode('=', $part, 2);

            if (count($keyValue) === 2) {
                $xSignatureData[trim($keyValue[0])] = trim($keyValue[1]);
            } else {
                Log::warning("🛑 Parte inválida en X-Signature: '$part'");
            }
        }

        return $xSignatureData;
    }

}
