<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductionLog;
use App\Models\ProductionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionPlanController extends Controller
{
    /**
     * List all production plans
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $sortField = $request->get('sort_field', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = ProductionPlan::with(['product', 'creator', 'approver', 'order']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('plan_code', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('creator', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
            });
        }

        $plans = $query->orderBy($sortField, $sortOrder)->paginate($perPage);

        // Transform data tanpa getCollection
        $plans->transform(function ($plan) {
            $plan->has_order = $plan->order ? true : false;
            $plan->order_count = $plan->order ? 1 : 0;
            return $plan;
        });

        return response()->json($plans);
    }


    /**
     * Log production plan changes
     */

        private function logPlanChange($plan, $oldStatus, $newStatus, $note = null, $changes = [])
        {
            ProductionLog::create([
                'log_type'   => 'plan',
                'plan_id'    => $plan->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note'       => $note,
                // ubah array menjadi string JSON agar tidak error
                'changes'    => json_encode($changes, JSON_UNESCAPED_UNICODE),
                'changed_by' => Auth::id(),
                'changed_at' => now()
            ]);
        }



    /**
     * Store new production plan
     */

    public function store(Request $request)
    {
        $user = Auth::user();
        if  ($user->role !== 'staff' ) {
        return response()->json(['message' => 'Unauthorized: Only PPIC staff can create plans'], 403);}


        $validated = $request->validate([
            'plan_code' => 'required|unique:production_plans',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'target_finish_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $validated['creator_id'] = $user->id;
        $validated['status'] = 'pending_approval';

        $plan = ProductionPlan::create($validated);

        $this->logPlanChange(
            $plan, 
            null, 
            'pending_approval', 
            'Production plan created',
            $validated
        );

        return response()->json([
            'message' => 'Production plan created successfully',
            'data' => $plan
        ], 201);
    }

    /**
     * Show single production plan
     */

    public function show($id)
    {
        return response()->json(
            ProductionPlan::with('product', 'creator', 'approver', 'order')->findOrFail($id)
        );
    }

    /**
     * Update production plan
     */

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'staff' ){
            return response()->json(['message' => 'Unauthorized: Only PPIC staff can update plans'], 403);
        }

        $plan = ProductionPlan::findOrFail($id);
        if ($plan->status == 'approved') {
            return response()->json(['message' => 'Plan already approved/rejected'], 403);
        }

        $validated = $request->validate([
            'plan_code' => 'required|unique:production_plans,plan_code,' . $id,
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'target_finish_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $plan->update($validated);

        return response()->json([
            'message' => 'Production plan updated successfully',
            'data' => $plan
        ]);
    }


    /**
     * Approve production plan
     */

    public function approve($id)
    {
        $user = Auth::user();
        if ($user->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized: Only manager can approve plans'], 403);
        }

        $plan = ProductionPlan::findOrFail($id);

        if ($plan->status !== 'pending_approval') {
            return response()->json(['message' => 'Plan already processed'], 403);
        }

        $oldStatus = $plan->status;

        $plan->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);

        $this->logPlanChange(
            $plan,
            $oldStatus,
            'approved',
            'Plan approved by manager'
        );

        return response()->json(['message' => 'Production plan approved successfully', 'data' => $plan->fresh()->toArray()]);
    }

    /**
     * Reject production plan
     */

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized: Only manager can reject plans'], 403);
        }

        $plan = ProductionPlan::findOrFail($id);

        if ($plan->status !== 'pending_approval') {
            return response()->json(['message' => 'Plan already processed'], 403);
        }

        $plan->update([
            'status' => 'rejected',
            'notes' => $request->input('reason', 'No reason provided'),
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);

        return response()->json(['message' => 'Production plan rejected successfully', 'data' => $plan]);
    }



    /**
     * Delete production plan
     */

    public function destroy($id){
        $user = Auth::user();
        if ($user->role !== 'staff') {
            return response()->json(['message' => 'Unauthorized: Only PPIC staff can delete plans'], 403);
        }
        $plan = ProductionPlan::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Production plan deleted successfully']);
    }

    /**
     * GET /api/production_plans/report
     * Get production plan report (weekly/monthly or custom period)
     */
    public function report(Request $request)
    {
        // Validasi input
        $request->validate([
            'type' => 'nullable|in:weekly,monthly,custom',
            'from' => 'required_if:type,custom|date',
            'to'   => 'required_if:type,custom|date|after_or_equal:from',
        ]);

        // Ambil semua plan, termasuk yang belum approved
        $query = ProductionPlan::with(['product', 'creator', 'approver', 'order.logs']);

        // Filter periode
        switch ($request->type) {
            case 'weekly':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;

            case 'monthly':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;

            case 'custom':
            default:
                if ($request->has(['from', 'to'])) {
                    $query->whereBetween('created_at', [$request->from, $request->to]);
                }
                break;
        }

        $plans = $query->orderByDesc('created_at')->get()->map(function ($plan) {
            $progress = 0;
            $order = $plan->order;

            if ($order && $plan->quantity > 0) {
                $progress = round(($order->quantity_done / $plan->quantity) * 100, 2);
            }

            // Tentukan status plan untuk report
            $status = $plan->status ?? 'pending'; // jika belum approved dianggap pending

            return [
                'plan_code'        => $plan->plan_code,
                'product_name'     => $plan->product->name ?? null,
                'quantity_target'  => $plan->quantity,
                'status'           => $status,
                'approved_by'      => $plan->approver->name ?? null,
                'approved_at'      => $plan->approved_at,
                'progress_percent' => $progress,
                'notes'            => $plan->notes,
                'additional_info'  => $plan->additional_info ?? null,
                'order'            => $order ? [
                    'id'               => $order->id,
                    'quantity_done'    => $order->quantity_done,
                    'quantity_remaining'=> $order->quantity_remaining,
                    'quantity_target'  => $order->quantity_target,
                    'status_final'     => $order->status_final,
                    'storage_location' => $order->storage_location,
                    'logs'             => $order->logs ?? [],
                ] : null
            ];
        });

        return response()->json([
            'type'  => $request->type ?? 'custom',
            'count' => $plans->count(),
            'data'  => $plans
        ]);
    }

    public function hasOrders($id)
    {
        $plan = ProductionPlan::with('order')->findOrFail($id);
        
        $hasOrders = !is_null($plan->order);
        
        return response()->json([
            'has_orders' => $hasOrders,
            'plan_code' => $plan->plan_code,
            'order_count' => $hasOrders ? 1 : 0
        ]);
    }
/**
 * Get orders for a production plan (only if plan approved)
 */
public function getOrders($id)
{
    $plan = ProductionPlan::with('order')
            ->where('status', 'approved') // filter hanya approved
            ->findOrFail($id);

    return response()->json($plan->order);
}

}
