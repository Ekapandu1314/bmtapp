<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Lapak;
Use App\OrderDetail;

class PenjualanController extends Controller
{
    public function index(Lapak $lapak){
    	return $lapak->orderDetail()->latest()->with('produk', 'order.nasabah')->get();
    }

    public function update(OrderDetail $orderDetail, Request $request){
    	if ($request->sedia == 1) {
    		$orderDetail->ada();
    	}

    	if ($request->sedia == 0) {
    		$orderDetail->habis();
    	}

    	$orderDetail->order->cekSedia();

		return ['error' => false, 'status' => 'success'];
    }

    public function siap(OrderDetail $orderDetail){

        if ($orderDetail->order->status != 'paid') {
            return [
                'error' => true,
                'message' => 'Pesanan Belum dibayar',
            ];
        }

        $orderDetail->update(['dikirim_at' => \Carbon\Carbon::now()]);
        $orderDetail->reduceStock();

        return [
            'error' => false,
            'status' => 'success',
            'dikirim_at' => $orderDetail->dikirim_at,
        ];
    }
}
