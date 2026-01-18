<?php

namespace App\Http\Controllers\Owner;

use App\Models\User;
use App\Models\Paket;
use App\Models\Rating;
use App\Models\Certificate;
use Illuminate\Support\Str;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CertificateTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class OwnerController extends Controller
{
    public function getAllPaket()
    {
        $pakets = Paket::paginate(3);

        return response()->json([
            'status' => 'success',
            'message' => 'List Paket',
            'data' => $pakets,
        ], 200);
    }

    public function storePaket(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'durasi' => 'nullable|string|max:100',
            'benefits' => 'required|array',
            'benefits.*' => 'string|max:255',
            'is_rias' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // khusus rias
            'schedules' => 'required_if:is_rias,true|array',
            'schedules.*.tanggal' => 'required|date',
            'schedules.*.jam_mulai' => 'required',
            'schedules.*.jam_selesai' => 'required',
        ]);

        // upload image
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('pakets', 'public');
        }

        $pakets = Paket::create([
            'image' => $imagePath,
            'name' => $request->name,
            'price' => $request->price,
            'durasi' => $request->durasi,
            'benefits' => json_encode($request->benefits),
            'is_rias' => $request->is_rias
        ]);

        // simpan schedule kalau rias
        if ($request->is_rias) {
            foreach ($request->schedules as $schedule) {
                $pakets->schedules()->create($schedule);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Paket created successfully',
            'data' => $pakets->load('schedules'),
        ], 201);
    }

    public function detailPaket($id)
    {
        $paket = Paket::with('schedules')->find($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Paket details',
            'data' => $paket,
        ], 200);
    }

    // edit hanya nama dan harga aja ya
    public function updatePaket(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        $paket = Paket::findOrFail($id);

        $paket->update([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Paket updated successfully',
            'data' => $paket,
        ]);
    }

    public function deletePaket($id)
    {
        $paket = Paket::findOrFail($id);
        $paket->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Paket deleted successfully',
        ]);
    }

    // dari sini fungsi employee
    public function getAllEmployee()
    {
        $employees = User::role(['admin', 'owner'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of Employees',
            'data' => $employees,
        ], 200);
    }

    public function addEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,owner',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user,
            'role' => $user->getRoleNames(),
        ], 201);
    }

    public function removeEmployee($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);
    }

    // Rating
    public function rating()
    {
        $ratings = Rating::with('transaction.paket', 'transaction.user')
            ->get()
            ->map(function ($rating) {
                $paketId = $rating->transaction->paket_id;

                $peserta = Transactions::where('paket_id', $paketId)->count();

                return [
                    'nama_paket' => $rating->transaction->nama_paket,
                    'rating' => $rating->rating,
                    'peserta' => $peserta
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $ratings
        ]);
    }

    // sales report
    public function salesReport()
    {
        $transaction = Transactions::with('paket')->get();

        $grouped = $transaction->groupBy('paket.name');

        $totalSold = $transaction->count();

        $packageSales = $grouped->map(function ($items, $packageName) use ($totalSold) {
            $sold = $items->count();
            $revenue = $items->sum(fn ($t) => $t->paket->price);

            return [
                'name'       => $packageName,
                'sold'       => $sold,
                'revenue'    => $revenue,
                'percentage' => $totalSold > 0 ? round(($sold / $totalSold) * 100) : 0,
            ];
        })->values();

        // monthly 6 month
        $monthlySales = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd   = now()->subMonths($i)->endOfMonth();

            $revenue = Transactions::with('paket')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get()
                ->sum(fn ($t) => $t->paket->price);

            $monthlySales[] = [
                'month'   => $monthStart->format('M'),
                'revenue' => $revenue,
            ];
        }

        // total revenue
        $totalRevenue = $transaction->sum(fn ($t) => $t->paket->price);

        // monthly revenue
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();
        $monthlyRevenue = Transactions::with('paket')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->get()
            ->sum(fn ($t) => $t->paket->price);

        // total user
        $totalUsers = User::role('customer')->count();

        // conversion rate
        $conversionRate = $totalUsers > 0
            ? round(($totalSold / $totalUsers) * 100, 2)
            : 0;

        return response()->json([
            'packageSales' => $packageSales,
            'monthlySales' => $monthlySales,
            'totalRevenue'     => $totalRevenue,
            'monthlyRevenue'   => $monthlyRevenue,
            'totalUsers'       => $totalUsers,
            'conversionRate'   => $conversionRate,
        ]);
    }

    // Track Transaction
    public function trackTransactions(Request $request)
    {
        $query = Transactions::with(['paket', 'user', 'certificate']);

        // filter paket
        if ($request->filled('paket_id')) {
            $query->where('paket_id', $request->paket_id);
        }

        // search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                ->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('paket', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                });
            });
        }

        $transactions = $query
            ->latest()
            ->paginate($request->get('per_page', 5));

        // inject file_path dari certificate ke setiap transaksi
        $transactions->getCollection()->transform(function($trx) {
            $trx->file_path = $trx->certificate->file_path ?? null;
            return $trx;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'List of Transactions',
            'data' => $transactions,
        ], 200);
    }

    // certificate template
    public function getCertificate()
    {
        $certificates = CertificateTemplate::paginate(3);

        return response()->json([
            'status' => 'success',
            'message' => 'List Certificate',
            'data' => $certificates,
        ], 200);
    }

    public function storeTemplate(Request $request) 
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'background' => 'required|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'fields' => 'nullable|json',
        ]);

        $path = $request->file('background')->store('certificates/templates', 'public');

        $template = CertificateTemplate::create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'background_path' => $path,
            'fields' => $request->fields ? json_decode($request->fields, true) : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Template sertifikat berhasil diupload',
            'data' => $template,
        ], 201);
    }

    // assign certificate
    public function assignCertificate(Request $request)
    {
        $request->validate([
            'paket_id'        => 'required|exists:pakets,id',
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'exists:transactions,id',
            'template_id'     => 'required|exists:certificate_templates,id',
        ]);

        $created = [];

        foreach ($request->transaction_ids as $transactionId) {
            $trx = Transactions::find($transactionId);
            if (!$trx) continue;

            // Cegah assign dobel
            $exists = Certificate::where('user_id', $trx->user_id)
                ->where('paket_id', $request->paket_id)
                ->where('transaction_id', $transactionId)
                ->exists();

            if ($exists) continue;

            // Generate nomor sertifikat
            $certificateNumber = 'CERT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

            $certificate = Certificate::create([
                'user_id'            => $trx->user_id,
                'paket_id'           => $request->paket_id,
                'transaction_id'     => $transactionId,
                'template_id'        => $request->template_id,
                'certificate_number' => $certificateNumber,
                'file_path'          => 'pending',
            ]);

            // 🔹 Generate PDF langsung
            $template = $certificate->template;

            if ($template && $template->fields) {
                $values = [
                    'name'    => $certificate->user->name,
                    'paket'   => $certificate->paket->name,
                    'tanggal' => now()->format('d F Y'),
                ];

                $pdf = Pdf::loadView('certificates.template', [
                    'template' => $template,
                    'values'   => $values,
                ])->setPaper('a4', 'landscape');

                $filename = 'CERT-' . Str::upper(Str::random(10)) . '.pdf';
                $path = 'certificates/generated/' . $filename;

                Storage::disk('public')->put($path, $pdf->output());

                $certificate->update([
                    'file_path' => $path,
                ]);
            }

            $created[] = $certificate;
        }

        return response()->json([
            'message' => 'Certificates assigned & generated successfully',
            'data'    => $created,
        ], 201);
    }

    public function generateCertificate(Certificate $certificate)
    {
        $certificate->load(['user', 'paket', 'template']);

        $template = $certificate->template;

        if (!$template || !$template->fields) {
            return response()->json([
                'message' => 'Template tidak valid'
            ], 422);
        }

        // DATA YANG DI-RENDER KE SERTIFIKAT
        $values = [
            'name'    => $certificate->user->name,
            'paket'   => $certificate->paket->name,
            'tanggal' => now()->format('d F Y'),
        ];

        $pdf = Pdf::loadView('certificates.template', [
            'template' => $template,
            'values'   => $values,
        ])->setPaper('a4', 'landscape');

        // PATH FILE
        $filename = 'CERT-' . Str::upper(Str::random(10)) . '.pdf';
        $path = 'certificates/generated/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        // UPDATE DB
        $certificate->update([
            'file_path' => $path,
        ]);

        return response()->json([
            'message' => 'Certificate generated',
            'file_path' => $path,
        ]);
    }
}
