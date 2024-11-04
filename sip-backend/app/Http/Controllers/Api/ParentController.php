<?php

namespace App\Http\Controllers\Api;

use App\Models\Balita;
use App\Models\Agenda;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    // Menampilkan list agenda pelayanan
    public function getAllAgenda()
    {
        $agenda = Agenda::where('posyandu_id', auth()->user()->posyandu_id)->get();

        return response()->json([
            'agenda' => $agenda,
        ], 200);
    }

    // Melihat detail agenda pelayanan
    public function getAgenda($id)
    {
        // Cari data agenda berdasarkan ID
        $agenda = Agenda::find($id);
 
        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$agenda) {
             return response()->json([
                 'message' => 'Agenda tidak ditemukan.',
             ], 404);
        }
 
        // Jika ditemukan, return JSON dengan data posyandu
        return response()->json([
            'agenda' => $agenda,
        ], 200);
    }

    // Menampilkan list data balita
    public function getAllBalita()
    {
        $balita = Balita::where('user_id', auth()->id())->get();

        return response()->json([
            'balita' => $balita,
        ], 200);
    }

    // Menampilkan detail data balita
    public function getBalitaDetail($id)
    {
        $balita = Balita::with(['pertumbuhan', 'imunisasi', 'konsultasi'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

            // Jika tidak ditemukan, return JSON dengan pesan error
            if (!$balita) {
                return response()->json([
                    'message' => 'Balita tidak ditemukan.',
                ], 404);
            }

        return response()->json([
            'balita' => $balita,
        ], 200);
    }

    // Menampilkan data profil orang tua
    public function getParentProfile()
    {
        $user = auth()->user();

        return response()->json([
            'user' => $user
        ], 200);
    }

    // Mengelola profil orang tua
    public function updateParentProfile(Request $request)
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

        // Update profil pengguna
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
}
