<?php

namespace App\Http\Controllers\Api;

use App\Models\Imunisasi;
use App\Models\Balita;
use App\Models\User;
use App\Models\Konsultasi;
use App\Models\Agenda;
use App\Models\VerificationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class TenagaKesehatanController extends Controller
{
    // Meminta verifikasi akun ke admin
    public function requestVerification()
    {
        $user = auth()->user();

        // Periksa jika akun sudah terverifikasi
        if ($user->verified) {
            return response().json(['message' => 'Akun anda sudah terverifikasi'], 200);
        }

        // Periksa jika permintaan sudah ada
        $existingRequest = VerificationRequest::where('user_id', $user->id)->whereNull('approved')->first();
        if ($existingRequest) {
            return response()->json(['message' => 'Permintaan verifikasi sudah diajukan dan menunggu persetujuan'], 200);
        }

        // Buat permintaan baru
        VerificationRequest::create([
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Permintaan verifikasi telah dikirim ke admin'], 200);
    }

    // Melihat daftar agenda pelayanan
    public function getAllAgenda()
    {
        $agenda = Agenda::all();

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

    // Menambah data konsultasi kesehatan
    public function addConsultation(Request $request)
    {
        // Set validasi input
        $validator = Validator::make($request->all(), [
            'balita_id'         => 'required|exists:balita,id',
            'date'              => 'required|date',
            'notes'             => 'required|string',
            'recommendation'    => 'nullable|string',
        ]);

        $consultation  = Konsultasi::create([
            'balita_id'         => $request->balita_id,
            'date'              => $request->date,
            'notes'             => $request->notes,
            'recommendation'    => $request->recommendation,
        ]);

        return response()->json([
            'message'       => 'Data konsultasi berhasil ditambahkan',
            'consultation'  => $consultation,
        ], 201);
    }

    // Update data konsultasi kesehatan
    public function updateConsultation(Request $request, $id)
    {
        // Set validasi input
        $validator = Validator::make($request->all(), [
            'balita_id'         => 'required|exists:balita,id',
            'date'              => 'required|date',
            'notes'             => 'required|string',
            'recommendation'    => 'nullable|string',
        ]);

        // Cari data konsultasi berdasarkan ID dan pastikan berada di posyandu yang sesuai
        $consultation = Konsultasi::where('id', $id)
            ->whereHas('balita', function ($query) {
                $query->where('posyandu_id', auth()->user()->posyandu_id);
            })
            ->first();

        // Jika data konsultasi tidak ditemukan
        if (!$consultation) {
            return response()->json([
                'message' => 'Data konsultasi tidak ditemukan atau posyandu tidak sesuai',
            ], 404);
        }

        // Update data konsultasi
        $consultation->update($request->only(['date', 'notes', 'recommendation']));

        return response()->json([
            'message'       => 'Data konsultasi berhasil di ubah',
            'consultation'  => $consultation,
        ], 200);
    }

    // Hapus data konsultasi kesehatan
    public function deleteConsultation($id)
    {
        // Cari data konsultasi kesehatan berdasarkan ID
        $consultation = Konsultasi::find($id);

        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$consultation) {
            return response()->json([
                'message' => 'Data konsultasi kesehatan tidak ditemukan.',
            ], 404);
        }

        // Hapus data konsultasi kesehatan
        $consultation->delete();

        // Return JSON response setelah berhasil dihapus
        return response()->json([
            'message' => 'Data konsultasi kesehatan berhasil dihapus',
        ], 200);
    }

    // Menampilkan list data ibu
    public function listParent()
    {
       // Menampilkan daftar user dengan role 'orang_tua' yang terdaftar di posyandu yang sama
       $parents = User::where('role', 'orang_tua')->where('posyandu_id', auth()->user()->posyandu_id)
        ->get(['id', 'name', 'email']);

        return response()->json([
            'parents'   => $parents
        ]);
    }

    // Menampilkan detail data ibu
    public function getParentDetail($id)
    {
        // Menampilkan daftar user dengan role 'orang_tua' yang terdaftar di posyandu yang sama
       $parent = User::where('id', $id)->where('role', 'orang_tua')->where('posyandu_id', auth()->user()->posyandu_id)->first();

       // Cek apakah data ibu ditemukan
       if (!$parent) {
            return response()->json([
                'message'   => 'Orang tua tidak ditemukan',
            ], 404);
        }

       return response()->json([
            'parent'   => $parent
        ]);
    }

    // Menampilkan list data balita
    public function getAllBalita()
    {
        $balita = Balita::all();

        return response()->json([
            'balita' => $balita,
        ], 200);
    }

    // Lihat detail data balita
    public function getBalita($id)
    {
        // Cari data balita berdasarkan ID
        $balita = Balita::with(['imunisasi', 'konsultasi', 'pertumbuhan' => function ($query) {
            $query->orderBy('date', 'asc');
        }])
            ->where('id', $id)
            // ->where('posyandu_id', auth()->user()->posyandu_id)
            ->firstOrFail();

        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$balita) {
            return response()->json([
                'message' => 'Data balita tidak ditemukan.',
            ], 404);
        }

        // Jika ditemukan, return JSON dengan data posyandu
        return response()->json([
            'balita' => $balita,
        ], 200);
    }

    // Update data imunisasi
    public function updateImunisasi(Request $request, $id)
    {
        // Set validasi
        $validator = Validator::make($request->all(), [
            'type'       => 'string|max:255',
            'date'       => 'date',
            'status'     => 'in:pending,completed',
        ]);

        // Cari data imunisasi berdasarkan ID
        $imunisasi = Imunisasi::find($id)
            ->whereHas('balita', function ($query) {
                $query->where('posyandu_id', auth()->user()->posyandu_id);
            })
            ->firstOrFail();

        // Perbarui data imunisasi
        $imunisasi->update($request->only([
            'type',
            'date',
            'status'
        ]));

        // Return JSON response setelah update berhasil
        return response()->json([
            'message'  => 'Data imunisasi berhasil diubah',
            'imunisasi' => $imunisasi,
        ], 200);
    }

    // Menampilkan data profil tenaga kesehatan
    public function getTenagaKesehatanProfile()
    {
        $user = auth()->user();

        return response()->json([
            'user' => $user
        ], 200);
    }

    // Mengelola profil tenaga kesehatan
    public function updateTenagaKesehatanProfile(Request $request)
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

    // Mengganti data posyandu user
    public function changePosyandu(Request $request)
    {
        // Set validasi input
        $validator = Validator::make($request->all(), [
            'posyandu_id'   => 'required|exists:posyandu,id'
        ]);

        $user = auth()->user();
        $user->posyandu_id = $request->posyandu_id;
        $user->save();

        return response()->json([
            'message' => 'Posyandu berhasil diupdate'
        ], 200);
    }
}
