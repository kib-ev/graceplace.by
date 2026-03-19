<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::orderBy('sort')->withCount('masters')->get();

        return view('admin.service-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.service-categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:0'],
            'keywords' => ['nullable', 'string'],
        ]);

        $keywords = $this->parseKeywords($data['keywords'] ?? '');

        ServiceCategory::create([
            'name' => $data['name'],
            'sort' => (int) $data['sort'],
            'keywords' => $keywords,
        ]);

        return redirect()->route('admin.service-categories.index')->with('success', 'Категория создана');
    }

    public function edit(ServiceCategory $service_category)
    {
        return view('admin.service-categories.edit', ['category' => $service_category]);
    }

    public function update(Request $request, ServiceCategory $service_category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:0'],
            'keywords' => ['nullable', 'string'],
        ]);

        $keywords = $this->parseKeywords($data['keywords'] ?? '');

        $service_category->update([
            'name' => $data['name'],
            'sort' => (int) $data['sort'],
            'keywords' => $keywords,
        ]);

        return redirect()->route('admin.service-categories.index')->with('success', 'Категория обновлена');
    }

    public function destroy(ServiceCategory $service_category)
    {
        $service_category->masters()->detach();
        $service_category->delete();

        return redirect()->route('admin.service-categories.index')->with('success', 'Категория удалена');
    }

    private function parseKeywords(string $input): array
    {
        return collect(preg_split('/[\r\n,;]+/', $input))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->toArray();
    }
}
