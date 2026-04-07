<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;

class OfficeController extends Controller
{
    /**
     * Display the Departments or Offices grid.
     */
    public function index(Request $request)
    {
        // Get the view type from the URL (default to departments)
        $view = $request->query('view', 'departments');

        if ($view == 'offices') {
            // Fetch all offices with pagination
            $offices = Office::orderBy('name', 'asc')->paginate(12);
        } else {
            // Fetch unique departments by grouping offices
            // Note: If you have a separate Department model, you would query that instead.
            $offices = Office::select('department')
                ->whereNotNull('department')
                ->groupBy('department')
                ->paginate(12);
        }

        return view('offices', compact('offices', 'view'));
    }

    /**
     * Store a real office in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:offices,name',
            'department' => 'required|string|max:255',
            'head' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        Office::create($validated);

        return redirect()->route('offices.index', ['view' => 'offices'])
            ->with('success', 'New office has been registered successfully.');
    }
}