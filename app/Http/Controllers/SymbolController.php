<?php

namespace App\Http\Controllers;

use App\Http\Requests\SymbolRequest;
use App\Models\Symbol;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SymbolController extends Controller
{
    public function index(): View
    {
        return view('symbols.index', ['symbols' => Symbol::orderBy('name')->paginate(20)]);
    }

    public function store(SymbolRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['name'] = strtoupper($data['name']);
        Symbol::create($data);

        return back()->with('status', 'Symbol created.');
    }

    public function update(SymbolRequest $request, Symbol $symbol): RedirectResponse
    {
        $data = $request->validated();
        $data['name'] = strtoupper($data['name']);
        $symbol->update($data);

        return back()->with('status', 'Symbol updated.');
    }

    public function destroy(Symbol $symbol): RedirectResponse
    {
        $symbol->delete();

        return back()->with('status', 'Symbol deleted.');
    }
}
