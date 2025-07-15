<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoPagoService;
use App\Repositories\MercadoPagoRepository;
use Illuminate\Http\JsonResponse;

class MercadoPagoController extends Controller
{
    /**
     * El servicio de MercadoPago.
     *
     * @var MercadoPagoService
    */
    protected $mpService;

    /**
     * El repositorio de MercadoPago.
     *
     * @var MercadoPagoRepository
    */
    protected $mercadopagorepo;

    /**
     * Inyecta el repositorio y el servicio en el controlador.
     *
     * @param MercadoPagoRepository $mercadopagorepo
     * @param MercadoPagoService $mpService
    */
    public function __construct(MercadoPagoRepository $mercadopagorepo, MercadoPagoService $mpService)
    {
      $this->mercadopagorepo = $mercadopagorepo;
      $this->mpService = $mpService;
    }

    
    public function webhooks(Request $request): JsonResponse
    {
        try {
            // 🔍 Logueamos todo el request (headers y body)
            Log::info('🧩 Webhook recibido de MercadoPago', [
                'headers' => $request->headers->all(),
                'body'    => $request->all(),
                'raw'     => $request->getContent(),
            ]);

            // 🔧 Ejecutamos la lógica del repo
            $result = $this->mercadopagorepo->handleWebhook($request, $this->mpService);

            Log::info('✅ Webhook procesado correctamente', $result);

            return response()->json($result['message'], $result['status']);
        } catch (\Throwable $e) {
            // 🚨 Si falla algo, lo registramos completo
            Log::error('💥 Error en webhook de MercadoPago: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'body'  => $request->all()
            ]);

            return response()->json(['error' => 'internal_error'], 500);
        }
    }
}
