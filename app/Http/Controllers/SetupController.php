<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupRequest;
use App\Models\Setup;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function index(): View
    {
        return view('setups.index', ['setups' => Setup::orderBy('name')->paginate(20)]);
    }

    public function store(SetupRequest $request): RedirectResponse
    {
        Setup::create($request->validated());

        return back()->with('status', 'Setup created.');
    }

    public function update(SetupRequest $request, Setup $setup): RedirectResponse
    {
        $setup->update($request->validated());

        return back()->with('status', 'Setup updated.');
    }

    public function destroy(Setup $setup): RedirectResponse
    {
        $setup->delete();

        return back()->with('status', 'Setup deleted.');
    }
}
