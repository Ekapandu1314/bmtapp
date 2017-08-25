<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function nasabah(){
    	return $this->belongsTo('App\Nasabah');
    }

    public function produk(){
    	return $this->belongsTo('App\Produk');
    }

    protected $guarded = ['id'];
}
