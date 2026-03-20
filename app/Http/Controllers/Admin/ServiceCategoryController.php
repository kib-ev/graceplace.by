<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::with(['children' => fn ($q) => $q->withCount('masters')])
            ->withCount('masters')
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get();

        return view('admin.service-categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        $parentCategories = ServiceCategory::whereNull('parent_id')->orderBy('sort')->get();
        $preselectedParentId = $request->get('parent_id');

        return view('admin.service-categories.create', compact('parentCategories', 'preselectedParentId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:0'],
            'parent_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'keywords' => ['nullable', 'string'],
        ]);

        $keywords = $this->parseKeywords($data['keywords'] ?? '');

        ServiceCategory::create([
            'name' => $data['name'],
            'sort' => (int) $data['sort'],
            'parent_id' => $data['parent_id'] ?? null,
            'keywords' => $keywords,
        ]);

        return redirect()->route('admin.service-categories.index')->with('success', 'Категория создана');
    }

    public function edit(ServiceCategory $service_category)
    {
        $parentCategories = ServiceCategory::whereNull('parent_id')
            ->where('id', '!=', $service_category->id)
            ->orderBy('sort')
            ->get();

        return view('admin.service-categories.edit', ['category' => $service_category, 'parentCategories' => $parentCategories]);
    }

    public function update(Request $request, ServiceCategory $service_category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:0'],
            'parent_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'keywords' => ['nullable', 'string'],
        ]);

        $parentId = $data['parent_id'] ?? null;
        if ($parentId == $service_category->id) {
            $parentId = null;
        }

        $keywords = $this->parseKeywords($data['keywords'] ?? '');

        $service_category->update([
            'name' => $data['name'],
            'sort' => (int) $data['sort'],
            'parent_id' => $parentId,
            'keywords' => $keywords,
        ]);

        return redirect()->route('admin.service-categories.index')->with('success', 'Категория обновлена');
    }

    public function destroy(ServiceCategory $service_category)
    {
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
