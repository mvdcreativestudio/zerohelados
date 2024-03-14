<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'address', 'city', 'state', 'country', 'zip_code', 'company_name', 'company_rut', 'company_address', 'company_city', 'company_state', 'company_country', 'company_zip_code', 'company_phone', 'company_email', 'company_website', 'company_logo'];

}
