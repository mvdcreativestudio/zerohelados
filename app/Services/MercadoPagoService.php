<?php

namespace App\Services;

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use stdClass;
use App\Models\Order;

class MercadoPagoService
{
    /**
     * La clave secreta de la tienda en MercadoPago.
     *
     * @var string
    */
    private string $secretKey;

    /**
     * Instancia del cliente.
     *
     * @var Client
    */
    private Client $client;

    /**
     * Constructor para configurar el acceso a la API de MercadoPago.
    */
    public function __construct()
    {
      // Cargar la secret key desde la configuración
      $this->secretKey = '4bb8a460c15a6bc54919c4195bca8a746bcea2a5c21eac424a999c981ae7795d';
      $this->client = new Client();

      // Configurar el acceso a la API de MercadoPago
      SDK::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Crea una preferencia de pago en MercadoPago.
     *
     * @param array $preferenceData
     * @param Order $order
     * @return Preference
    */
    public function createPreference(array $preferenceData, Order $order): Preference
    {
      // Crear la preferencia de MercadoPago
      $preference = new Preference();

      // Configurar el pagador
      $payer = new stdClass();
      $payer->email = $preferenceData['payer']['email'];
      $preference->payer = $payer;
      $preference->currency_id = 'UYU';

      // Configurar el campo metadata
      $preference->metadata = [
          'order_id' => $order->id
      ];

      // Configurar los ítems
      $items = [];
      foreach ($preferenceData['items'] as $itemData) {
          $item = new Item();
          $item->title = $itemData['title'];
          $item->description = 'descripcion';
          $item->quantity = $itemData['quantity'];
          $item->unit_price = $itemData['unit_price'];
          $items[] = $item;
      }

      // Aplicar descuento
      $discount = $preferenceData['discount']['amount'] ?? 0;
      if ($discount > 0) {
        $discountItem = new Item();
        $discountItem->title = $preferenceData['discount']['description'];
        $discountItem->quantity = 1;
        $discountItem->unit_price = -$discount;
        $items[] = $discountItem;
      }

      $preference->items = $items;

      // Configurar las URLs de retorno
      $preference->back_urls = [
        "success" => config('services.checkout.return_url') . "/success/{$order->uuid}",
        "failure" => config('services.checkout.return_url') . "/failure/{$order->uuid}",
        "pending" => config('services.checkout.return_url') . "/pending/{$order->uuid}"
      ];
      $preference->auto_return = "all";

      // URL para las notificaciones webhooks
      $preference->notification_url = 'https://zerohelados.com.uy/api/mpagohook';

      $preference->external_reference = $order->id;

      // Configurar los envíos
      $preference->shipments = (object) [
          'mode' => 'not_specified',
          'cost' => (float) $order->shipping,
      ];

      // Guardar la preferencia y generar el log
      $preference->save();
      Log::info('Preference created:', $preference->toArray());

      return $preference;
    }

    /**
     * Verifica la firma HMAC de una solicitud.
     *
     * @param string $id
     * @param string $requestId
     * @param string $timestamp
     * @param string $receivedHash
     * @return bool
    */
    public function verifyHMAC(string $id, string $requestId, string $timestamp, string $receivedHash): bool
    {
      $message = "id:$id;request-id:$requestId;ts:$timestamp;";
      $generatedHash = hash_hmac('sha256', $message, $this->secretKey);

      return hash_equals($generatedHash, $receivedHash);
    }

    /**
     * Obtiene la información de un pago desde la API de MercadoPago.
     *
     * @param string $id
     * @return array|null
    */
    public function getPaymentInfo(string $id): ?array
    {
      try {
          $response = $this->client->request('GET', "https://api.mercadopago.com/v1/payments/{$id}", [
              'headers' => [
                  'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
              ],
          ]);

          return json_decode($response->getBody(), true);
      } catch (\Exception $e) {
          Log::error("Error al obtener la información del pago: " . $e->getMessage());
          return null;
      }
    }

    /**
     * Configura las credenciales de la tienda para acceder a la API de MercadoPago.
     *
     * @param string $publicKey
     * @param string $accessToken
     * @return void
    */
    public function setCredentials(string $publicKey, string $accessToken): void
    {
        SDK::setPublicKey($publicKey);
        SDK::setAccessToken($accessToken);

        Log::info('Credenciales de MercadoPago configuradas:', [
            'public_key' => $publicKey,
            'access_token' => $accessToken
        ]);
    }

}
