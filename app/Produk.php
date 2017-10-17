<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    /*RELATION SECTION*/

    public function lapak(){
    	return $this->belongsTo('App\Lapak');
    }

    public function kategori_produk(){
    	return $this->belongsTo('App\KategoriProduk');
    }

    public function review(){
    	return $this->hasMany('App\Review');
    }

    public function keranjang(){
        return $this->hasMany('App\Keranjang');
    }

    public function orderDetail(){
        return $this->hasMany('App\OrderDetail');
    }

    public function satuan(){
        return $this->belongsTo('App\Satuan');
    }

    /*MUTATOR SECTION*/

    public function getUnitAttribute()
    {
        return $this->satuan()->first()->name;
    }

    public function getTerjualAttribute()
    {
        return $this->orderDetail()->count();
    }

    public function getTotalReviewAttribute()
    {
        return $this->review()->count();
    }

    /*CUSTOM METHOD SECTION*/

    public function addReview(){
        $review = $this->review()
            ->create(request([
                'nasabah_id',
                'rating',
                'review',
        ]));

        $this->update([
            'rating' => $this->review->avg('rating'),
        ]);

        return $review;
    }

    public function keyword($sentence, $coloumn = 'name')
    {
        $query = '';
                $i = 0;
                $keywords = explode(' ', $sentence);
                foreach ($keywords as $keyword) {
                    $query .= "$coloumn LIKE '%$keyword%'";

                    $i++;

                    if ($i != count($keywords)) {
                        $query .= " OR ";
                    }
                }

        return $query;
    }

    public static function search($keyword = '*')
    {
        $key = \Helper::keyword($keyword);

        $produks = Produk::where(function($query) use ($key, $keyword)
        {
            $query->whereRaw($key)
                  ->orWhereRaw(\Helper::keyword($keyword, 'deskripsi'))
                  ->orWhereHas('kategori_produk', function($q) use ($key)
                  {
                      $q->whereRaw($key);
                  })
                  ->orWhereHas('lapak', function($q) use ($key)
                  {
                      $q->whereRaw($key);
                  });  
        })->get();

        return $produks;
    }

    public static function hotItems()
    {
        return static::where('aktif', 1)
                    ->orderBy('view', 'desc')
                    ->take(5)
                    ->with(['kategori_produk', 'lapak','review' => function($query){
                        $query->take(5)->with('nasabah');
                    }])->get();
    }

    // protected $hidden=['nasabah_id'];
    protected $guarded=['id'];
    protected $appends = ['total_review', 'terjual', 'unit'];
}
