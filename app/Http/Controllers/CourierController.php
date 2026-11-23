<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourierRequest;
use App\Http\Requests\IndexCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCourierRequest $request)
    {
        $filters = $request->validated();

        $couriers = Courier::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
            ->when($filters['level'] ?? null, fn ($q, $levels) => $q->ofLevels($levels))
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->orderBy('id') // tie-breaker agar pagination stabil
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return CourierResource::collection($couriers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourierRequest $request)
    {
        return new CourierResource(Courier::create($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Courier $courier)
    {
        //
        return new CourierResource($courier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourierRequest $request, Courier $courier)
    {
        $courier->update($request->validated());

        return new CourierResource($courier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Courier $courier)
    {
        //
        $courier->delete();

        return response()->noContent(); // 204
    }
}
