<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\ProductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionOrderController extends Controller
{
    /**
     * List all production orders
     */
    public function index(Request $request)
    {
        $query = ProductionOrder::with('plan', 'product', 'assignedTo', 'logs');

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }


        if ($request->has('plan_id') && $request->plan_id !== null) {
            $query->where('plan_id', $request->plan_id);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'message' => 'Production order data retrieved successfully',
            'data' => $orders
        ], 200);
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
     * Update production order status
     */

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'new_status' => 'required|string|in:waiting,in_process,finished',
            'quantity_done' => 'nullable|integer|min:0', // jumlah yang sudah selesai
            'note' => 'nullable|string'
        ]);

        $order = ProductionOrder::findOrFail($id);
        $oldStatus = $order->status;

        $updateData = ['status' => $validated['new_status']];

        // Set started_at jika pertama kali in_process
        if ($validated['new_status'] === 'in_process' && !$order->started_at) {
            $updateData['started_at'] = now();
        }

        // Tambahkan logika quantity_done dan quantity_remaining
        if ($validated['new_status'] === 'in_process') {
            $quantityDone = $validated['quantity_done'] ?? 0;

            // Hitung total quantity done sampai sekarang
            $order->quantity_done = ($order->quantity_done ?? 0) + $quantityDone;

            // Hitung sisa quantity
            $order->quantity_remaining = max($order->quantity_target - $order->quantity_done, 0);

            // Jika sudah selesai semua, otomatis ubah status ke finished
            if ($order->quantity_remaining === 0) {
                $updateData['status'] = 'finished';
                $updateData['finished_at'] = now();
            }

            $updateData['quantity_done'] = $order->quantity_done;
            $updateData['quantity_remaining'] = $order->quantity_remaining;
        }

        // Jika finished langsung
        if ($validated['new_status'] === 'finished') {
            $updateData['finished_at'] = now();
            $updateData['quantity_done'] = $order->quantity_target;
            $updateData['quantity_remaining'] = 0;
        }

        $order->update($updateData);

        ProductionLog::create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $updateData['status'],
            'note' => $validated['note'] ?? '',
            'changed_by' => Auth::id(),
            'changed_at' => now()
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $order
        ]);
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
