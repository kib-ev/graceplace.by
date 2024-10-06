<?php

namespace App\Http\Controllers;

use App\Models\StorageCell;
use Illuminate\Http\Request;

class StorageCellController extends Controller
{
    public function index()
    {
        $storageCells = StorageCell::all();

        return view('admin.storage-cells.index', compact('storageCells'));
    }

    public function create()
    {
        return view('admin.storage-cells.create');
    }

    public function store(Request $request)
    {
        $storageCell = StorageCell::make();
        $storageCell->fill($request->all());
        $storageCell->save();
    }

    public function edit(StorageCell $storageCell)
    {
        return view('admin.storage-cells.edit', compact('storageCell'));
    }

    public function update(Request $request, StorageCell $storageCell)
    {
        $storageCell->fill($request->all());
        $storageCell->update();

        return back();
    }
}
