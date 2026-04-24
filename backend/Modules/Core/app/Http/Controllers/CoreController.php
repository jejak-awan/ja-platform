<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;

class CoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\View\View
    {
        return view('core::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\View\View
    {
        return view('core::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string|int $id): \Illuminate\View\View
    {
        return view('core::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string|int $id): \Illuminate\View\View
    {
        return view('core::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string|int $id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string|int $id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->back();
    }
}
