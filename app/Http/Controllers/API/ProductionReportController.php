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
    $query = ProductionReport::with([
        'order.plan.product',
        'reporter'
    ]);

    // Filter by period
    if ($request->type === 'weekly') {
        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    } elseif ($request->type === 'monthly') {
        $query->whereMonth('created_at', now()->month);
    } elseif ($request->has(['from', 'to'])) {
        $query->whereBetween('created_at', [$request->from, $request->to]);
    }

    $reports = $query->orderByDesc('created_at')->get();

    $data = $reports->map(function ($report) {
        $order = $report->order;
        $plan = $order?->plan;
        $product = $plan?->product;

        $started_at = $order?->started_at ? Carbon::parse($order->started_at) : null;
        $finished_at = $order?->finished_at ? Carbon::parse($order->finished_at) : null;

        return [
            'report_id' => $report->id,
            'order_code' => $order->order_code ?? null,
            'plan_code' => $plan->plan_code ?? null,
            'product_name' => $product->name ?? null,
            'quantity_target' => $order->quantity_target ?? $plan->quantity ?? 0,
            'quantity_actual' => $report->quantity_actual ?? 0,
            'quantity_reject' => $report->quantity_reject ?? 0,
            'status_final' => $report->status_final ?? null,
            'started_at' => $started_at?->format('Y-m-d H:i:s'),
            'finished_at' => $finished_at?->format('Y-m-d H:i:s'),
            'duration_days' => ($started_at && $finished_at) ? $finished_at->diffInDays($started_at) : null,
            'reported_by' => $report->reporter->name ?? null,
            'notes' => $report->notes ?? null,
            'storage_location' => $report->storage_location ?? null,
        ];
    });

    return response()->json([
        'period' => $request->type ?? 'custom',
        'count' => $data->count(),
        'data' => $data
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
