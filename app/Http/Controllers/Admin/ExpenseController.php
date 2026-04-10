<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('expenseCategory');

        if ($request->filled('category'))   $query->where('category', $request->category);
        if ($request->filled('from'))       $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))         $query->whereDate('date', '<=', $request->to);

        $expenses   = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $categories = ExpenseCategory::where('is_active', true)->pluck('name', 'name');

        return view('admin.isp.expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'amount'              => 'required|numeric|min:0',
            'category'            => 'required|string|max:100',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'payment_method'      => 'required|in:cash,mpesa,bank',
            'reference'           => 'nullable|string|max:100',
            'date'                => 'required|date',
            'receipt_image'       => 'nullable|file|image|max:2048',
        ]);

        if ($request->hasFile('receipt_image')) {
            $data['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $data['created_by'] = auth('admin')->id();
        Expense::create($data);

        return redirect()->route('admin.isp.expenses.index')->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.isp.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'amount'              => 'required|numeric|min:0',
            'category'            => 'required|string|max:100',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'payment_method'      => 'required|in:cash,mpesa,bank',
            'reference'           => 'nullable|string|max:100',
            'date'                => 'required|date',
            'receipt_image'       => 'nullable|file|image|max:2048',
        ]);

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $data['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $expense->update($data);

        return redirect()->route('admin.isp.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }

    public function report(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $expenses = Expense::whereBetween('date', [$from, $to])->get();

        $totalExpenses  = $expenses->sum('amount');
        $byCategory     = $expenses->groupBy('category')->map->sum('amount');

        // Monthly trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyTrend[$month->format('M Y')] = Expense::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');
        }

        return view('admin.isp.expenses.report', compact('expenses', 'totalExpenses', 'byCategory', 'monthlyTrend', 'from', 'to'));
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $expenses = Expense::whereBetween('date', [$from, $to])->orderBy('date')->get();

        $csv = "Title,Amount,Category,Payment Method,Reference,Date\n";
        foreach ($expenses as $e) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $e->title) . '"',
                $e->amount,
                $e->category,
                $e->payment_method,
                '"' . str_replace('"', '""', $e->reference ?? '') . '"',
                $e->date->format('Y-m-d'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=expenses_{$from}_{$to}.csv",
        ]);
    }
}
