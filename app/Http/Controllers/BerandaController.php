<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\produk;
class BerandaController extends Controller
{
    public function berandaBackend() {
        return view('backend.v_beranda.index',[
            'judul' => 'Halaman Beranda',
        ]);
    }
    public function index() {
        $produk= Produk::where('status',1)->orderby('updated_at','desc')->paginate(8);
        return view('v_beranda.index',[
            'judul' => 'Halaman Beranda',
            'produk'=> $produk,
        ]);
    }
}
