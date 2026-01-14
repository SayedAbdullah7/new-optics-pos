<?php

namespace App\Http\Controllers;

use App\DataTables\ClientDataTable;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Models\Paper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ClientDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.client.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.client.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get client data
            $clientData = $request->clientData();

            // Handle phone array
            if (isset($clientData['phone']) && is_array($clientData['phone'])) {
                $clientData['phone'] = array_filter($clientData['phone']);
            }

            // Create client
            $client = Client::create($clientData);

            // Create paper (prescription) if data is provided
            if ($request->hasPaperData()) {
                $paperData = $request->paperData();
                $paperData['client_id'] = $client->id;
                Paper::create($paperData);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Client created successfully.',
                'data' => $client
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create client: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): View
    {
        // Load relationships with invoices and papers
        $client->load(['invoices.transactions', 'papers']);

        // Get the latest paper (prescription)
        $paper = $client->papers()->latest()->first();

        // Calculate financial summary
        $totalAmount = $client->invoices->sum('amount');
        $totalPaid = $client->invoices->sum(function ($invoice) {
            return $invoice->transactions ? $invoice->transactions->sum('amount') : 0;
        });
        $totalRemaining = $totalAmount - $totalPaid;

        // Count invoices by status
        $invoiceStats = [
            'total' => $client->invoices->count(),
            'paid' => $client->invoices->where('status', 'paid')->count(),
            'unpaid' => $client->invoices->whereIn('status', ['unpaid', 'partial'])->count(),
        ];

        return view('pages.client.show', compact(
            'client',
            'paper',
            'totalAmount',
            'totalPaid',
            'totalRemaining',
            'invoiceStats'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        $client->load('papers');
        return view('pages.client.form', ['model' => $client]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, Client $client): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get client data
            $clientData = $request->clientData();

            // Handle phone array
            if (isset($clientData['phone']) && is_array($clientData['phone'])) {
                $clientData['phone'] = array_filter($clientData['phone']);
            }

            // Update client
            $client->update($clientData);

            // Create new paper (prescription) if data is provided
            // Each edit creates a new prescription record (history tracking)
            if ($request->hasPaperData()) {
                $paperData = $request->paperData();
                $paperData['client_id'] = $client->id;
                Paper::create($paperData);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Client updated successfully.',
                'data' => $client
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update client: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): JsonResponse
    {
        try {
            // Check if client has invoices
            if ($client->invoices()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Cannot delete client with existing invoices.'
                ], 422);
            }

            DB::beginTransaction();

            // Delete associated papers first
            $client->papers()->delete();

            // Delete client
            $client->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Client deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete client: ' . $e->getMessage()
            ], 500);
        }
    }
}
