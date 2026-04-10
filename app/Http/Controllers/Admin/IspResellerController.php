<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class IspResellerController extends Controller
{
    public function index()
    {
        $resellers = Reseller::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.isp.resellers.index', compact('resellers'));
    }

    public function create()
    {
        $users = User::whereDoesntHave('reseller')->orderBy('name')->get();
        return view('admin.isp.resellers.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'            => 'required|exists:users,id|unique:resellers,user_id',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'is_active'          => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $reseller = Reseller::create($data);
        AuditLog::record('reseller.created', Reseller::class, $reseller->id, [], $reseller->toArray());
        return redirect()->route('admin.isp.resellers.index')->with('success', 'Reseller created.');
    }

    public function edit(Reseller $reseller)
    {
        $reseller->load('user');
        return view('admin.isp.resellers.edit', compact('reseller'));
    }

    public function update(Request $request, Reseller $reseller)
    {
        $data = $request->validate([
            'commission_percent' => 'required|numeric|min:0|max:100',
            'is_active'          => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', false);
        $reseller->update($data);
        return redirect()->route('admin.isp.resellers.index')->with('success', 'Reseller updated.');
    }

    public function destroy(Reseller $reseller)
    {
        $reseller->delete();
        return redirect()->route('admin.isp.resellers.index')->with('success', 'Reseller removed.');
    }
}
