<?php

namespace App\Http\Controllers\Api;

use App\Models\Konsultasi;
use App\Models\Imunisasi;
use App\Models\User;
use App\Models\Balita;
use App\Models\Pertumbuhan;
use App\Models\Agenda;
use App\Models\VerificationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KaderController extends Controller
{
    // meminta verifikasi ke admin
    public function requestVerification()
    {
        $user = auth()->user();

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

    // membuat agenda pelayanan
    public function addAgenda(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'required|date',
            'time'        => 'nullable|date_format:H:i',
            'location'    => 'nullable|string|max:255',
        ]);

        // simpan agenda baru
        $agenda = Agenda::create([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'created_by' => auth()->id(), // Mengambil ID kader yang login
        ]);

        return response()->json([
            'message' => 'Agenda pelayanan berhasil ditambahkan',
            'agenda' => $agenda
        ], 201);
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

    // update data agenda pelayanan
    public function updateAgenda(Request $request, $id)
    {
        // Cari data agenda berdasarkan ID
        $agenda = Agenda::find($id);
    
        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$agenda) {
            return response()->json([
                'message' => 'Agenda pelayanan tidak ditemukan.',
            ], 404);
        }
    
        // Validasi input
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'date'        => 'required|date',
            'time'        => 'nullable|date_format:H:i',
            'location'    => 'nullable|string|max:255',
        ]);
    
        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Data tidak valid.',
            ], 422);
        }
    
        // Update data posyandu
        $agenda->update($request->only(['title', 'description', 'date', 'time', 'location']));
    
        // Return JSON response setelah update berhasil
        return response()->json([
            'message'  => 'Data agenda pelayanan berhasil diubah',
            'agenda' => $agenda,
        ], 200);
    }

    // hapus agenda pelayanan
    public function deleteAgenda($id)
    {
        // Cari data agenda berdasarkan ID
        $agenda = Agenda::find($id);
    
        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$agenda) {
            return response()->json([
                'message' => 'Agenda pelayanan tidak ditemukan.',
            ], 404);
        }
    
        // Hapus data posyandu
        $agenda->delete();
    
        // Return JSON response setelah berhasil dihapus
        return response()->json([
            'message' => 'Data agenda pelayanan berhasil dihapus',
        ], 200);
    }

    // menambah data pertumbuhan balita
    public function addGrowthRecord(Request $request)
    {
        // Validasi data input
        $request->validate([
            'balita_id' => 'required|exists:balitas,id',
            'date' => 'required|date',
            'weight' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'head_circumference' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Simpan data pertumbuhan balita
        $growthRecord = Pertumbuhan::create([
            'balita_id' => $request->balita_id,
            'date' => $request->date,
            'weight' => $request->weight,
            'height' => $request->height,
            'head_circumference' => $request->head_circumference,
            'notes' => $request->notes,
            'recorded_by' => auth()->id(), // ID kader yang login
        ]);

        return response()->json([
            'message' => 'Data pertumbuhan balita berhasil ditambahkan',
            'growth_record' => $growthRecord
        ], 201);
    }

    // update data pertumbuhan balita
    public function updateGrowthRecord(Request $request, $id)
    {
        // Cari data pertumbuhan balita berdasarkan ID
        $growthRecord = Pertumbuhan::find($id);

        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$growthRecord) {
            return response()->json([
                'message' => 'Data pertumbuhan balita tidak ditemukan.',
            ], 404);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'balita_id' => 'required|exists:balitas,id',
            'date' => 'required|date',
            'weight' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'head_circumference' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Data tidak valid.',
            ], 422);
        }

        // Update data pertumbuhan balita
        $growthRecord->update($request->only([
            'balita_id', 
            'date',
            'weight',
            'height',
            'head_circumference',
            'notes'
        ]));

        return response()->json([
            'message' => 'Data pertumbuhan balita berhasil diupdate',
            'growth_record' => $growthRecord
        ], 201);
    }

    // Menampilkan list data ibu
    public function listParent()
    {
       // Menampilkan daftar user dengan role 'orang_tua' yang terdaftar di posyandu yang sama
       $parents = User::where('role', 'orang_tua')->where('posyandu_id', auth()->user()->posyandu_id)
        ->get(['id', 'name', 'email', 'address']);

        return response()->jsosn([
            'parents'   => $parents
        ]);
    }

    // Menampilkan detail data ibu
    public function getParentDetail()
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

    // tambah data balita
    public function storeBalita(Request $request)
    {
        // set validasi
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'birth-date'  => 'required|date',
            'gender'      => 'required|string|in:L,P',
            'posyandu_id' => 'required|exists:posyandus,id',
        ]);

        // jika validator gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // buat data Posyandu ke database
        $balita = Balita::create($request->all());

        // return JSON Posyandu telah dibuat
        if ($balita) {
            return response()->json([
                'message'     => 'Data balita berhasil ditambahkan',
                'balita'    => $balita,
            ], 201);
        }

         // return JSON process insert failed 
         return response()->json([
            'success' => false,
        ], 409);
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

    // Update data balita
    public function updateBalita(Request $request, $id)
    {
        // Cari data posyandu berdasarkan ID
        $balita = Balita::find($id);

        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$balita) {
            return response()->json([
                'message' => 'Balita tidak ditemukan.',
            ], 404);
        }

        // Set validasi
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'birth-date'  => 'required|date',
            'gender'      => 'required|string|in:L,P',
            'posyandu_id' => 'required|exists:posyandus,id',
        ]);

        // Cek jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        // Update data posyandu
        $balita->update($request->only(['name', 'birth-date', 'gender', 'posyandu_id']));

         // Return JSON response setelah update berhasil
         return response()->json([
            'message'  => 'Data balita berhasil diubah',
            'balita' => $balita,
        ], 200);
    }

    // Hapus data balita
    public function deletebalita($id)
    {
        // Cari data posyandu berdasarkan ID
        $balita = Balita::find($id);
    
        // Jika tidak ditemukan, return JSON dengan pesan error
        if (!$balita) {
            return response()->json([
                'message' => 'Balita tidak ditemukan.',
            ], 404);
        }
    
        // Hapus data posyandu
        $balita->delete();
    
        // Return JSON response setelah berhasil dihapus
        return response()->json([
            'message' => 'Data balita berhasil dihapus',
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

    // Rekap data balita
    public function rekapDataBalita()
    {
        $balita = Balita::where('posyandu_id', auth()->user()->posyandu_id)
            ->with(['pertumbuhan', 'imunisasi', 'konsultasi'])
            ->gets();

            return response()->json([
                'balita' => $balita,
            ], 200);
    }

    // Rekap data bulanan
    public function monthlyReport(Request $request)
    {
        // Set Validasi
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:' . date('Y')
        ]);

        // Jika validator gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $posyanduId = auth()->user()->posyandu_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // Hitung jumlah imunisasi pada bulan dan tahun tertentu
        $jumlahImunisasi = Imunisasi::where('posyandu_id', $posyanduId)
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->count();

        // Hitung jumlah konsultasi kesehatan pada bulan dan tahun tertentu
        $jumlahKonsultasi = Konsultasi::where('posyandu_id', $posyanduId)
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->count();

        return response()->json([
            'bulan'             => $bulan,
            'tahun'             => $tahun,
            'jumlahImunisasi'   => $jumlahImunisasi,
            'jumlahKonsultasi'  => $jumlahKonsultasi,
        ], 200);
    }

    // Rekap data hasil posyandu
    public function rekapHasilKegiatanPosyandu()
    {
        $posyanduId = auth()->user()->posyandu_id;

        // Total imunisasi yang dilakukan di posyandu
        $totalImunisasi = Imunisasi::where('posyandu_id', $posyanduId)->count();

        // Total konsultasi yang dilakukan di posyandu
        $totalKonsultasi = Konsultasi::where('posyandu_id', $posyanduId)->count();

        // Total jumlah balita yang terdaftar
        $totalBalita = Balita::where('posyandu_id', $posyanduId)->count();

        return response()->json([
            'totalImunisasi'    => $totalImunisasi,
            'totalKonsultasi'   => $totalKonsultasi,
            'totalBalita'       => $totalBalita,
        ], 200);
    }

    // Menampilkan data profil kader
    public function getKaderProfile()
    {
        $user = auth()->user();

        return response()->json([
            'user' => $user
        ], 200);
    }

    // Mengelola profil kader
    public function updateKaderProfile(Request $request
    )
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
