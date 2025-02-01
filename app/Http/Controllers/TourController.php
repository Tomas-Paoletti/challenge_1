<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'min_price' => 'sometimes|numeric|min:0',
                'max_price' => 'sometimes|numeric|min:0',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);


            $query = Tour::query();

            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->has('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }
            $per_page = $request->input('per_page', 15);
            $tours = $query->paginate($per_page);
            return response()->json([
                'data' => $tours->items(),
                'meta' => [
                    'current_page' => $tours->currentPage(),
                    'total_pages' => $tours->lastPage(),
                    'total_items' => $tours->total(),
                    'per_page' => $tours->perPage(),
                ]
            ], 200);
        }catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $tour = Tour::create($validatedData);
        return response()->json($tour, 201);
    }

    public function show(Tour $tour)
    {
        return response()->json($tour, 200);
    }

    public function update(Request $request, Tour $tour)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ]);

        $tour->update($validatedData);
        return response()->json($tour, 200);
    }

    public function destroy(Tour $tour)
    {
        $tour->delete();
        return response()->json(null, 204);
    }
}
