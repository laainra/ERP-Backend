<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductionLog;
use Illuminate\Http\Request;

class ProductionLogController extends Controller
{
public function index(Request $request)
{
    $perPage = (int) $request->get('perPage', 10);
    $search = $request->get('search', '');
    $sortField = $request->get('sortField', 'changed_at');
    $sortOrder = strtolower($request->get('sortOrder', 'desc')) === 'asc' ? 'asc' : 'desc';

    $query = ProductionLog::with(['order', 'plan', 'changedBy']);

    // Pencarian (global)
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('log_type', 'like', "%{$search}%")
              ->orWhere('old_status', 'like', "%{$search}%")
              ->orWhere('new_status', 'like', "%{$search}%")
              ->orWhere('note', 'like', "%{$search}%")
              ->orWhere('changes', 'like', "%{$search}%");
        });
    }

    // Sorting - hanya izinkan field tertentu agar aman
    $allowedSorts = ['id', 'log_type', 'old_status', 'new_status', 'changed_at'];
    if (in_array($sortField, $allowedSorts)) {
        $query->orderBy($sortField, $sortOrder);
    } else {
        $query->orderBy('changed_at', $sortOrder);
    }

    // Pagination
    $logsPaginator = $query->paginate($perPage);

    // Ambil items (array of models) dan transform ke struktur frontend
    $items = $logsPaginator->items();

    $mapped = array_map(function ($log) {
        return [
            'id' => $log->id,
            'log_type' => $log->log_type,
            'plan' => $log->plan,
            'order' => $log->order,
            'old_status' => $log->old_status,
            'new_status' => $log->new_status,
            'note' => $log->note,
            'changes' => $log->changes,
            'changedBy' => $log->changedBy, // nama user bisa diakses: log.changedBy.name
            'changed_at' => $log->changed_at,
            'full_data' => $log, // untuk modal detail
        ];
    }, $items);

    // Kembalikan struktur paginator yang konsisten
    return response()->json([
        'data' => $mapped,
        'current_page' => $logsPaginator->currentPage(),
        'last_page' => $logsPaginator->lastPage(),
        'per_page' => $logsPaginator->perPage(),
        'total' => $logsPaginator->total(),
    ]);
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
