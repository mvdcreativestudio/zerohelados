<?php

use MercadoPago\SDK;

class MercadoPagoController extends Controller
{
  public function __construct()
  {
    SDK::setAccessToken(config('services.mercadopago.access_token'));
  }

}
