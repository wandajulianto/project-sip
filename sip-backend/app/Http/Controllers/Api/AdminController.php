<?php

namespace App\Http\Controllers\Api;

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

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|unique:users,email,' . $user->id, // Tambahkan koma sebelum ID user
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Cek jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        // Update profil pengguna
        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diupdate'
        ], 200);
    }
}
