<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductionLog;
use Illuminate\Http\Request;

class ProductionLogController extends Controller
{
    public function index()
    {
        return response()->json(ProductionLog::with('order', 'changer')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:production_orders,id',
            'old_status' => 'nullable|string',
            'new_status' => 'required|string',
            'note' => 'nullable|string',
            'changed_by' => 'required|exists:users,id',
            'changed_at' => 'required|date'
        ]);

        $log = ProductionLog::create($validated);
        return response()->json($log, 201);
    }

    public function show($id)
    {
        return response()->json(
            ProductionLog::with('order', 'changer')->findOrFail($id)
        );
    }
}
