<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Service::class);
        $query = Service::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $services = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        $this->authorize('create', Service::class);
        return view('services.create');
    }

    public function edit(Service $service)
    {
        $this->authorize('update', $service);
        return view('services.edit', compact('service'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Service::class);
        $validated = $request->validate([
            'code' => 'required|string|unique:services,code|max:20',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category' => 'required|in:consultation,procedure,lab,imaging,other',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Service::create($validated);

        return redirect()->route('services.index')
            ->with('success', __('messages.serviceCreated'));
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:services,code,' . $service->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category' => 'required|in:consultation,procedure,lab,imaging,other',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', __('messages.serviceUpdated'));
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', __('messages.serviceDeleted'));
    }
}
