<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;

class TenantController extends Controller
{
    public function index(Request $request){
        $tenants = Tenant::all();
        return response()->json(
            [
                'data' => $tenants
            ]
        );
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
            'status' => 'string',
        ]);

        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => str()->slug($validated['name']) . '-' . uniqid(),
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
            'logo' => $request->logo,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => $tenant
        ], 201);
    }

    public function show(Tenant $tenant){
        return response()->json([
            'success' => true,
            'message' => 'Tenant retrieved successfully',
            'data' => $tenant
        ]);
    }

    public function update(Request $request, Tenant $tenant){
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:tenants,email,' . $tenant->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
            'logo' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
        ]);

        $tenant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant
        ], 200);
    }

    public function destroy(Tenant $tenant){

        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ], 200);
    }
}
