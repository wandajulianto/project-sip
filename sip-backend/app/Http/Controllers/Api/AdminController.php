<?php

namespace App\Http\Controllers\Api;

use App\Models\VerificationRequest;
use App\Models\Posyandu;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // tambah data posyandu
    public function createPosyandu(Request $request)
    {
        // set validasi
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        // jika validator gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // buat data Posyandu ke database
        $posyandu = Posyandu::create($request->all());

        // return JSON Posyandu telah dibuat
        if ($posyandu) {
            return response()->json([
                'message'     => 'Data posyandu berhasil ditambahkan',
                'posyandu'    => $posyandu,
            ], 201);
        }

         // return JSON process insert failed 
         return response()->json([
            'success' => false,
        ], 409);
    }

    // get daftar posyandu
    public function getAllPosyandu()
    {
        $posyandu = Posyandu::all();

        return response()->json([
            'posyandu' => $posyandu,
        ], 200);
    }

    // get detail posyandu
    public function getPosyandu($id)
    {
        // Cari data posyandu berdasarkan ID
        $posyandu = Posyandu::find($id);

        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$posyandu) {
            return response()->json([
                'message' => 'Posyandu tidak ditemukan.',
            ], 404);
        }

        // Jika ditemukan, return JSON dengan data posyandu
        return response()->json([
            'posyandu' => $posyandu,
        ], 200);
    }

    // update data posyandu
    public function updatePosyandu(Request $request, $id)
    {
        // Cari data posyandu berdasarkan ID
        $posyandu = Posyandu::find($id);
    
        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$posyandu) {
            return response()->json([
                'message' => 'Posyandu tidak ditemukan.',
            ], 404);
        }
    
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
        ]);
    
        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Data tidak valid.',
            ], 422);
        }
    
        // Update data posyandu
        $posyandu->update($request->only(['name', 'address']));
    
        // Return JSON response setelah update berhasil
        return response()->json([
            'message'  => 'Data posyandu berhasil diubah',
            'posyandu' => $posyandu,
        ], 200);
    }

    // hapus posyandu
    public function deletePosyandu($id)
    {
        // Cari data posyandu berdasarkan ID
        $posyandu = Posyandu::find($id);
    
        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$posyandu) {
            return response()->json([
                'message' => 'Posyandu tidak ditemukan.',
            ], 404);
        }
    
        // Hapus data posyandu
        $posyandu->delete();
    
        // Return JSON response setelah berhasil dihapus
        return response()->json([
            'message' => 'Data posyandu berhasil dihapus',
        ], 200);
    }

    // verifikasi akun user
    public function verifyUser($id)
    {
        $user = User::where('role', '!=', 'admin')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }    

        $user->verified = true;

        $user->save();

        return response()->json([
            'message' => 'Pengguna berhasil diverifikasi'
        ], 200);
    }

    // melihat profil admin
    public function getAdminProfile()
    {
        $user = auth()->user();

        return response()->json([
            'user' => $user
        ], 200);
    }

    // kelola profil admin
    public function updateAdminProfile(Request $request)
    {
        $user = auth()->user();

        // Set validasi input
        $validator = Validator::make($request->all(), [
            'name'      => 'string|max:255',
            'email'     => 'string|email|unique:users,email,' . $user->id, // Tambahkan koma sebelum ID user
            'phone'     => 'nullable|string|max:15',
            'address'   => 'nullable|string',
            'photo'     => 'nullable|image|mime:jpg,jpeg,png|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        /// Update profil pengguna
        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->address = $request->address ?? $user->address;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        // Proses upload foto profile (opsional)
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diupdate',
            'user'    => $user
        ], 200);
    }

    // Tampilkan list permintaan verifikasi user
    public function listVerificationRequests()
    {
        $requests = VerificationRequest::whereNull('approved')->with('user')->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // Tolak permintaan verifikasi user
    public function rejectUser($id)
    {
        $request = VerificationRequest::where('user_id', $id)->whereNull('approved')->first();
        if ($request) {
            $request->approved = false;
            $request->save();
            return response()->json(['message' => 'Permintaan verifikasi ditolak'], 200);
        }
        return response()->json(['message' => 'Permintaan tidak ditemukan'], 404);
    }
}
