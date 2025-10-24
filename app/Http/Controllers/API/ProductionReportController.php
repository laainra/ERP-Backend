<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductionReport;
use App\Models\ProductionOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductionReportController extends Controller
{
    /**
     * GET /api/reports
     * List reports (linked to orders and plans) by period
     */

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search', '');
        $sortField = $request->get('sort_field', 'created_at');
        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = ProductionReport::with(['order', 'reporter', 'order.plan.product']);

        // Pencarian (global)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('order.plan.product', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhere('status_final', 'like', "%{$search}%")
                ->orWhere('storage_location', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sorting - hanya izinkan field tertentu agar safe
        $allowedSorts = ['created_at', 'quantity_actual', 'quantity_reject', 'status_final'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            // Jika user coba sort by product_name (relasi) — fallback ke created_at
            $query->orderBy('created_at', $sortOrder);
        }

        // Pagination
        $reportsPaginator = $query->paginate($perPage);

        // Ambil items (array of models) dan transform menjadi struktur yang frontend butuhkan
        $items = $reportsPaginator->items();

        $mapped = array_map(function ($report) {
            // $report bisa berupa model atau array tergantung versi; pastikan akses dengan property
            $order = $report->order ?? null;
            $plan = $order ? ($order->plan ?? null) : null;
            $product = $plan ? ($plan->product ?? null) : null;

            return [
                'id' => $report->id,
                'order' => $order, 
                'product' => $product, 
                'product_name' => $product->name ?? '-',
                'order_code' => $order->order_code ?? null,
                'quantity_target' => $order->quantity_target ?? $plan->quantity ?? null,
                'quantity_actual' => $report->quantity_actual,
                'quantity_reject' => $report->quantity_reject,
                'status_final' => $report->status_final,
                'storage_location' => $report->storage_location,
                'report_date' => optional($report->created_at)->toDateTimeString(),
            ];
        }, $items);

        // Kembalikan struktur paginator yang konsisten untuk frontend
        return response()->json([
            'data' => $mapped,
            'current_page' => $reportsPaginator->currentPage(),
            'last_page' => $reportsPaginator->lastPage(),
            'per_page' => $reportsPaginator->perPage(),
            'total' => $reportsPaginator->total(),
        ]);
    }


    /**
     * GET /api/reports/{id}
     */
    public function show($id)
    {
        $report = ProductionReport::with([
            'order.plan.product',
            'reporter'
        ])->findOrFail($id);

        return response()->json($report);
    }

    /**
     * POST /api/reports
     * Create report after production completed
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:production_orders,id',
            'quantity_actual' => 'required|integer|min:0',
            'quantity_reject' => 'nullable|integer|min:0',
            'storage_location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['reported_by'] = Auth::id();

        $order = ProductionOrder::findOrFail($validated['order_id']);
        $validated['product_id'] = $order->product_id;

        // Auto-fill target quantity from order
        $validated['quantity_target'] = $order->quantity_target;
        $validated['status_final'] = $order->status;

        $report = ProductionReport::create($validated);

        return response()->json([
            'message' => 'Production report created successfully',
            'data' => $report
        ], 201);
    }

    /**
     * PUT /api/reports/{id}
     */
    public function update(Request $request, $id)
    {
        $report = ProductionReport::findOrFail($id);
        $report->update($request->all());

        return response()->json([
            'message' => 'Production report updated successfully',
            'data' => $report
        ]);
    }

    /**
     * DELETE /api/reports/{id}
     */
    public function destroy($id)
    {
        $report = ProductionReport::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Production report deleted successfully']);
    }
}
