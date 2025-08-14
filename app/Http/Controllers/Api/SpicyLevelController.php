<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpicyLevel;
use Illuminate\Http\Request;

class SpicyLevelController extends Controller
{
    public function index()
    {
        $spicyLevels = SpicyLevel::all();
        if ($spicyLevels->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No spicy levels found'], 404);
        }

        $spicyLevels = $spicyLevels->map(function ($spicyLevel) {
            return [
                'id' => $spicyLevel->id,
                'name' => $spicyLevel->name
            ];
        });
        return response()->json(['success' => true, 'data' => $spicyLevels], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:spicy_levels']);
        $spicyLevel = SpicyLevel::create($validated);
        return response()->json(['success' => true, 'message' => 'Spicy level created successfully', 'data' => $spicyLevel], 201);
    }

    public function show($id)
    {
        $spicyLevel = SpicyLevel::find($id);
        if (!$spicyLevel) {
            return response()->json(['success' => false, 'message' => 'Spicy level not found'], 404);
        }
        $spicyLevel = [
            'id' => $spicyLevel->id,
            'name' => $spicyLevel->name
        ];
        return response()->json(['success' => true, 'data' => $spicyLevel], 200);
    }

    public function update(Request $request, $id)
    {
        $spicyLevel = SpicyLevel::find($id);
        if (!$spicyLevel) {
            return response()->json(['success' => false, 'message' => 'Spicy level not found'], 404);
        }
        $validated = $request->validate(['name' => 'required|string|unique:spicy_levels,name,' . $id]);
        $spicyLevel->update($validated);
        return response()->json(['success' => true, 'message' => 'Spicy level updated successfully', 'data' => $spicyLevel], 200);
    }

    public function destroy($id)
    {
        $spicyLevel = SpicyLevel::find($id);
        if (!$spicyLevel) {
            return response()->json(['success' => false, 'message' => 'Spicy level not found'], 404);
        }
        $spicyLevel->delete();
        return response()->json(['success' => true, 'message' => 'Spicy level deleted successfully'],   200);
    }
}
