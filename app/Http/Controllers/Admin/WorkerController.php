<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Admin::with('roles')->whereNull('deleted_at')->get();
        return view('admin.isp.access.users.index', compact('workers'));
    }

    public function create()
    {
        $roles = Role::orderBy('display_name')->get();
        return view('admin.isp.access.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:admins,email',
            'mobile'   => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
        ]);

        $worker = Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'mobile'   => $request->mobile,
            'password' => $request->password, // hashed via Admin model mutator
        ]);

        if ($request->filled('roles')) {
            \DB::table('role_user')->insert(
                collect($request->roles)->map(fn ($rid) => [
                    'role_id'   => $rid,
                    'user_id'   => $worker->id,
                    'user_type' => 'admin',
                ])->toArray()
            );
        }

        return redirect()->route('admin.isp.access.users.index')->with('success', 'Worker created.');
    }

    public function edit(Admin $worker)
    {
        $roles          = Role::orderBy('display_name')->get();
        $workerRoleIds  = $worker->roles->pluck('id')->toArray();
        return view('admin.isp.access.users.edit', compact('worker', 'roles', 'workerRoleIds'));
    }

    public function update(Request $request, Admin $worker)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:admins,email,' . $worker->id,
            'mobile'   => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
        ]);

        $data = [
            'name'   => $request->name,
            'email'  => $request->email,
            'mobile' => $request->mobile,
        ];
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $worker->update($data);

        // Sync roles
        \DB::table('role_user')->where('user_id', $worker->id)->where('user_type', 'admin')->delete();
        if ($request->filled('roles')) {
            \DB::table('role_user')->insert(
                collect($request->roles)->map(fn ($rid) => [
                    'role_id'   => $rid,
                    'user_id'   => $worker->id,
                    'user_type' => 'admin',
                ])->toArray()
            );
        }

        return redirect()->route('admin.isp.access.users.index')->with('success', 'Worker updated.');
    }

    public function destroy(Admin $worker)
    {
        if ($worker->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        \DB::table('role_user')->where('user_id', $worker->id)->where('user_type', 'admin')->delete();
        $worker->delete();

        return back()->with('success', 'Worker deleted.');
    }
}
