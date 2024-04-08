<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoPagoAccount extends Model
{
    protected $table = 'mercadopago_accounts'; // Especifica el nombre de la tabla si no sigue la convención de nombres de Laravel

    protected $fillable = [
        'store_id',
        'public_key',
        'access_token',
    ]; // Los atributos que se pueden asignar en masa

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
