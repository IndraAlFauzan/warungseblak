<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use Illuminate\Http\Request;

class FlavorController extends Controller
{
    public function index()
    {
        $flavors = Flavor::all();
        if ($flavors->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No flavors found'], 404);
        }
        return response()->json(['success' => true, 
        'message' => 'Flavors retrieved successfully'
        ,'data' => $flavors], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:flavors']);
        $flavor = Flavor::create($validated);
        return response()->json(['success' => true,
         'message' => 'Flavor created successfully', 'data' => $flavor], 201);
    }

    public function show($id)
    {
        $flavor = Flavor::find($id);
        if (!$flavor) {
            return response()->json(['success' => false, 'message' => 'Flavor not found'], 404);
        }
        return response()->json(['success' => true,
        'message' => 'Flavor retrieved successfully'
        ,'data' => $flavor], 200);
    }

    public function update(Request $request, $id)
    {
        $flavor = Flavor::find($id);
        if (!$flavor) {
            return response()->json(['success' => false, 'message' => 'Flavor not found'], 404);
        }
        $validated = $request->validate(['name' => 'required|string|unique:flavors,name,' . $id]);
        $flavor->update($validated);
        return response()->json(['success' => true, 'message' => 'Flavor updated successfully', 'data' => $flavor], 200);
    }

    public function destroy($id)
    {
        $flavor = Flavor::find($id);
        if (!$flavor) {
            return response()->json(['success' => false, 'message' => 'Flavor not found'], 404);
        }
        $flavor->delete();
        return response()->json(['success' => true, 'message' => 'Flavor deleted successfully'], 200 );
    
    }
}
