<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')->orderBy('name')->get();
        return view('admin.isp.expense_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.isp.expense_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:expense_categories,name',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'sometimes|boolean',
        ]);

        ExpenseCategory::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.isp.expense_categories.index')->with('success', 'Category created.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('admin.isp.expense_categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string|max:255',
            'is_active'   => 'sometimes|boolean',
        ]);

        $expenseCategory->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.isp.expense_categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'Cannot delete category with existing expenses.');
        }
        $expenseCategory->delete();
        return back()->with('success', 'Category deleted.');
    }
}
