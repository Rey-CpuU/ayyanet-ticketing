<?php

namespace App\Http\Controllers;

use App\Models\StatusBanner;
use Illuminate\Http\Request;

class StatusBannerController extends Controller
{
    public function index()
    {
        $banners = StatusBanner::latest()->get();

        return view('status-banners.index', compact('banners'));
    }

    public function create()
    {
        return view('status-banners.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        StatusBanner::create($data);

        return redirect()->route('status-banners.index')
            ->with('success', 'Banner created.');
    }

    public function edit(StatusBanner $statusBanner)
    {
        return view('status-banners.edit', compact('statusBanner'));
    }

    public function update(Request $request, StatusBanner $statusBanner)
    {
        $data = $this->validated($request);

        $statusBanner->update($data);

        return redirect()->route('status-banners.index')
            ->with('success', 'Banner updated.');
    }

    public function destroy(StatusBanner $statusBanner)
    {
        $statusBanner->delete();

        return redirect()->route('status-banners.index')
            ->with('success', 'Banner deleted.');
    }

    public function active()
    {
        return response()->json(
            StatusBanner::active()->latest()->get(['id', 'title', 'message', 'type'])
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'message'   => ['required', 'string'],
            'type'      => ['required', 'in:info,maintenance,outage,warning'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
