<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\ProductionLog;
use App\Models\ProductionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionOrderController extends Controller
{
    /**
     * List all production orders
     */
    public function index(Request $request)
    {
        $query = ProductionOrder::with(['plan', 'product', 'assignedTo', 'logs.changedBy']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter plan_id
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        // 🔍 Hanya filter kalau search tidak kosong
        if ($request->has('search') && trim($request->search) !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('assignedTo', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
            });
        }

        // Sorting dan pagination
        $sortField = $request->get('sort_field', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $perPage = $request->get('per_page', 10);
        $orders = $query->paginate($perPage);

        return response()->json([
            'message' => 'Production order data retrieved successfully',
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ]);
    }



    /**
     * Store new production order
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_code' => 'required|unique:production_orders',
            'plan_id' => 'nullable|exists:production_plans,id',
            'quantity_target' => 'nullable|integer',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        if (!empty($validated['plan_id'])) {
            $plan = \App\Models\ProductionPlan::findOrFail($validated['plan_id']);
            $validated['product_id'] = $plan->product_id;

            if (!isset($validated['quantity_target'])) {
                $validated['quantity_target'] = $plan->quantity;
            } else {
                if ($validated['quantity_target'] > $plan->quantity) {
                    return response()->json([
                        'message' => 'Quantity target exceeds plan quantity'
                    ], 400);
                }
            }
        } else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity_target' => 'required|integer|min:1'
            ]);
            $validated['product_id'] = $request->product_id;
        }
        $validated['status'] = 'waiting';
       
        $order = ProductionOrder::create($validated);

        ProductionLog::create([
            'order_id' => $order->id,
            'log_type' => 'order',
            'old_status' => null,
            'new_status' => 'waiting',
            'note' => 'Order created',
            'changed_by' => Auth::id(),
            'changed_at' => now()
        ]);

        return response()->json([
            'message' => 'Production order created successfully',
            'data' => $order
        ], 201);
    }

    /**
     * Show single production order
     */

    public function show($id)
    {

        $data =  ProductionOrder::with('product', 'plan', 'logs', 'assignedTo')->findOrFail($id);
        return response()->json([
            'message' => 'Production order retrieved successfully',
            'data' => $data
        ], 200
           
        );
    }

    /**
     * Update production order
     */
    public function update(Request $request, $id)
    {
        $order = ProductionOrder::findOrFail($id);


        $validated = $request->validate([
            'order_code' => 'required|unique:production_orders,order_code,' . $id,
            'plan_id' => 'nullable|exists:production_plans,id',
            'quantity_target' => 'nullable|integer|min:1',
            'assigned_to' => 'nullable|exists:users,id'
        ]);


        if (!empty($validated['plan_id'])) {
            $plan = \App\Models\ProductionPlan::findOrFail($validated['plan_id']);
            $validated['product_id'] = $plan->product_id;


            if (!isset($validated['quantity_target'])) {
                $validated['quantity_target'] = $plan->quantity;
            } else if ($validated['quantity_target'] > $plan->quantity) {
                return response()->json([
                    'message' => 'Quantity target exceeds plan quantity'
                ], 400);
            }
        } else {

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity_target' => 'required|integer|min:1'
            ]);
            $validated['product_id'] = $request->product_id;
        }

        $order->update($validated);

        return response()->json([
            'message' => 'Production order updated successfully',
            'data' => $order
        ], 200);
    }


    /**
     * Destroy production order
     */

    public function destroy($id){
        $order = ProductionOrder::findOrFail($id);
        $order->delete();

        return response()->json([
            'message' => 'Production order deleted successfully'
        ], 200);
    }


    /**
     * Log order status change
     */

    private function logOrderChange($order, $oldStatus, $newStatus, $note = null, $changes = [])
    {
        ProductionLog::create([
            'log_type' => 'order',
            'plan_id' => $order->plan_id,
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
            'changes' => json_encode($changes), // 🔹 fix array to string
            'changed_by' => Auth::id(),
            'changed_at' => now()
        ]);
    }

    /**
     * Update production order status
     */

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'new_status' => 'required|string|in:waiting,in_process,finished',
            'quantity_done' => 'nullable|integer|min:0',
            'note' => 'nullable|string'
        ]);

        $order = ProductionOrder::findOrFail($id);
        $oldStatus = $order->status;

        $updateData = ['status' => $validated['new_status']];

        if ($validated['new_status'] === 'in_process' && !$order->started_at) {
            $updateData['started_at'] = now();
        }

        // ====== HANDLE STATUS IN PROCESS ======
        if ($validated['new_status'] === 'in_process') {
            $quantityDone = $validated['quantity_done'] ?? $order->quantity_done ?? 0;

            $order->quantity_done = $quantityDone;
            $order->quantity_remaining = max($order->quantity_target - $quantityDone, 0);

            if ($order->quantity_remaining === 0) {
                $updateData['status'] = 'finished';
                $updateData['finished_at'] = now();
            }

            $updateData['quantity_done'] = $order->quantity_done;
            $updateData['quantity_remaining'] = $order->quantity_remaining;
        }

        // ====== HANDLE STATUS FINISHED ======
        if ($validated['new_status'] === 'finished') {
            $updateData['finished_at'] = now();
            $updateData['quantity_done'] = $order->quantity_target;
            $updateData['quantity_remaining'] = 0;
        }

        $order->update($updateData);

        // Catat log perubahan
        $this->logOrderChange(
            $order,
            $oldStatus,
            $updateData['status'],
            $validated['note'] ?? 'Status updated to ' . $updateData['status'],
            [
                'quantity_done' => $order->quantity_done,
                'quantity_remaining' => $order->quantity_remaining
            ]
        );

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $order
        ]);
    }



    /**
     * Get available production orders (those without reports)
     */
    public function availableOrders()
    {
        $reportedOrderIds = ProductionReport::pluck('order_id')->toArray();

        $orders = ProductionOrder::with('plan.product')
            ->whereNotIn('id', $reportedOrderIds)
            ->get();

        return response()->json($orders);
    }
    
    /**
     * Get logs for a production order
     */

    public function getLogs($id)
    {
        $order = ProductionOrder::findOrFail($id);
        return response()->json($order->logs);
    }
}
