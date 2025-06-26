<?php

namespace App\Http\Controllers; 
 
use Illuminate\Http\Request; 
use App\Models\Customer; 
use App\Models\User; 
use Illuminate\Support\Facades\Auth; 
use Laravel\Socialite\Facades\Socialite; 
use Illuminate\Support\Facades\Hash; 
use App\Helpers\ImageHelper;


class CustomerController extends Controller 
{ 
    public function akun($id) 
{ 
    $loggedInCustomerId = Auth::user()->id; 
    // Cek apakah ID yang diberikan sama dengan ID customer yang sedang login 
    if ($id != $loggedInCustomerId) { 
        // Redirect atau tampilkan pesan error 
        return redirect()->route('customer.akun', ['id' => $loggedInCustomerId])
>with('msgError', 'Anda tidak berhak mengakses akun ini.'); 
    } 
    $customer = Customer::where('user_id', $id)->firstOrFail(); 
    return view('v_customer.edit', [ 
        'judul' => 'Customer', 
        'subJudul' => 'Akun Customer', 
        'edit' => $customer 
    ]); 
} 
public function updateakun(Request $request, $id)
{
    $customer = Customer::where('user_id', $id)->firstOrFail();
    $rulers=[
        'nama'=>'required|string|max:255',
        'hp'=>'required|min:10|max:13',
        'foto'=>'image|mimes:jpeg,png,jpg,gif|max:2048',
    ];
   $messages=['foto.image'=>'Format gambar gunakan file dengan ekstensi jpg, jpeg,png atau gif.',
   'foto.max'=>'Ukuran gambar maksimal 2MB.'];

   if ($request-> email != $customer-> user->email) {
      $rulers['email']='required|max:255|email| unique:customer';
   }
   if ($request-> alamat != $customer->alamat) {
      $rulers['alamat']='required';
   }
   if ($request-> pos != $customer->ps) {
      $rulers['pos']='required';
   }

$validatedData=$request->validate($rulers,$messages);
   // menggunakan image helper 
   if( $request-> file ('foto')){
    // Hapus gambar lama 
    if ($customer->user->foto) { 
        // Hapus gambar lama
    $oldImagePath=public_path('storage/img-customer/'). $customer->user->foto;
    if (file_exists($oldImagePath)) { 
        unlink($oldImagePath); 
    } 
    }
   $file = $request->file('foto'); 
   $extension = $file->getClientOriginalExtension(); 
   $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension; 
   $directory = 'storage/img-customer/'; 
   // Simpan gambar dengan ukuran yang ditentukan 
   ImageHelper::uploadAndResize($file, $directory, $originalFileName, 385, 400); // null (jika tinggi otomatis) 
   // Simpan nama file asli di database 
   $validatedData['foto'] = $originalFileName; 

$customer->user->update($validatedData); 
   
$customer->update([ 
   'alamat' => $request->input('alamat'), 
   'pos' => $request->input('pos'), 
]); 
return redirect()->route('customer.akun', $id)->with('success', 'Data berhasil 
diperbarui'); 
} 
}

    // Redirect ke Google 
    public function redirect() 
    { 
        // dd(Socialite::driver('google')->redirect());
        return Socialite::driver('google')->redirect(); 
    } 
 
    // Callback dari Google 
    public function callback() 
    { 
        try { 
 

 
            $socialUser = Socialite::driver('google')->user(); 
            // dd($socialUser);

            // Cek apakah email sudah terdaftar 
            $registeredUser = User::where('email', $socialUser->email)->first(); 
 
            if (!$registeredUser) { 
                // Buat user baru 
                $user = User::create([ 
                    'nama' => $socialUser->name, 
                    'email' => $socialUser->email, 
                    'role' => '2', // Role customer 
                    'status' => 1, // Status aktif 
                    'password' => Hash::make('default_password'), // Password default (opsional) 
                ]); 
 
                // Buat data customer 
                Customer::create([ 
                    'user_id' => $user->id, 
                    'google_id' => $socialUser->id, 
                    'google_token' => $socialUser->token 
                ]); 
 
                // Login pengguna baru 
                Auth::login($user); 
            } else { 
                // Jika email sudah terdaftar, langsung login 
                Auth::login($registeredUser); 
            } 
 
            // Redirect ke halaman utama 
            return redirect()->intended('beranda'); 
        } catch (\Exception $e) { 
            // Redirect ke halaman utama jika terjadi kesalahan 
            return redirect('/')->with('error', 'Terjadi kesalahan saat login dengan 
Google.'); 
        } 
    } 
 
    public function logout(Request $request) 
    { 
        Auth::logout(); // Logout pengguna 
        $request->session()->invalidate(); // Hapus session 
        $request->session()->regenerateToken(); // Regenerate token CSRF 
 
        return redirect('/')->with('success', 'Anda telah berhasil logout.'); 
    } 
    
    public function index() 
    { 
        $customer = Customer::orderBy('id', 'desc')->get(); 
        return view('backend.v_customer.index', [ 
            'judul' => 'Customer', 
            'sub' => 'Halaman Customer', 
            'index' => $customer 
        ]); 
    } 
    
    public function destroy($id)
    {
        // Hapus relasi user dan customer
        $customer = Customer::findOrFail($id);
        $customer->user->delete(); // jika ingin hapus user juga
        $customer->delete();
    
        return redirect()->route('backend.customer.index')->with('success', 'Data berhasil dihapus');
    }
}
