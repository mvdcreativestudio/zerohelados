<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function index()
    {
      $coupon = Coupon::all();

      return view('content.e-commerce.backoffice.marketing.coupons.index');
    }

    public function datatable()
    {
      $coupons = Coupon::all();

      return datatables()->of($coupons)
        ->addColumn('action', function($coupon){
          return '<a href="'.route('coupons.edit', $coupon->id).'" class="btn btn-primary btn-sm">Editar</a>';
        })
        ->rawColumns(['action'])
        ->make(true);
    }
}
