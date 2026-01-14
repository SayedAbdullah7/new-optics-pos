<?php

namespace App\Http\Controllers;

use App\DataTables\VendorDataTable;
use App\Http\Requests\VendorRequest;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(VendorDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.vendor.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    public function create(): View
    {
        return view('pages.vendor.form');
    }

    public function store(VendorRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            if (isset($data['phone']) && is_array($data['phone'])) {
                $data['phone'] = array_filter($data['phone']);
            }

            $vendor = Vendor::create($data);

            return response()->json([
                'status' => true,
                'msg' => 'Vendor created successfully.',
                'data' => $vendor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Vendor $vendor): View
    {
        // Load relationships with bills and transactions
        $vendor->load(['bills.transactions']);

        // Calculate financial summary
        $totalAmount = $vendor->bills->sum('amount');
        $totalPaid = $vendor->bills->sum(function ($bill) {
            return $bill->transactions ? $bill->transactions->sum('amount') : 0;
        });
        $totalRemaining = $totalAmount - $totalPaid;

        // Count bills by status
        $billStats = [
            'total' => $vendor->bills->count(),
            'paid' => $vendor->bills->where('status', 'paid')->count(),
            'unpaid' => $vendor->bills->whereIn('status', ['unpaid', 'partial'])->count(),
        ];

        return view('pages.vendor.show', compact(
            'vendor',
            'totalAmount',
            'totalPaid',
            'totalRemaining',
            'billStats'
        ));
    }

    public function edit(Vendor $vendor): View
    {
        return view('pages.vendor.form', ['model' => $vendor]);
    }

    public function update(VendorRequest $request, Vendor $vendor): JsonResponse
    {
        try {
            $data = $request->validated();
            if (isset($data['phone']) && is_array($data['phone'])) {
                $data['phone'] = array_filter($data['phone']);
            }

            $vendor->update($data);

            return response()->json([
                'status' => true,
                'msg' => 'Vendor updated successfully.',
                'data' => $vendor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        try {
            if ($vendor->bills()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Cannot delete vendor with existing bills.'
                ], 422);
            }

            $vendor->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Vendor deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete vendor: ' . $e->getMessage()
            ], 500);
        }
    }
}





