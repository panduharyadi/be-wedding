<?php

namespace App\Http\Controllers\Owner;

use App\Models\User;
use App\Models\Paket;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transactions;

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
        $query = Transactions::with(['paket', 'user']);

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

        return response()->json([
            'status' => 'success',
            'message' => 'List of Transactions',
            'data' => $transactions,
        ], 200);
    }
}
