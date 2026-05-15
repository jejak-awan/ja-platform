<?php

namespace Modules\Infra\Http\Controllers;

use Modules\System\Http\Controllers\BaseApiController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('infra::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('infra::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('infra::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('infra::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
