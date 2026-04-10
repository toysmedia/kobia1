<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        return view('admin.isp.access.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get()->groupBy('group');
        return view('admin.isp.access.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'         => $request->name,
            'display_name' => $request->display_name,
            'description'  => $request->description,
        ]);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.isp.access.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        $permissions        = Permission::orderBy('group')->orderBy('display_name')->get()->groupBy('group');
        $rolePermissionIds  = $role->permissions->pluck('id')->toArray();
        return view('admin.isp.access.roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'permissions'  => 'nullable|array',
            'permissions.*'=> 'exists:permissions,id',
        ]);

        $role->update([
            'name'         => $request->name,
            'display_name' => $request->display_name,
            'description'  => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.isp.access.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super_admin') {
            return back()->with('error', 'Cannot delete the super_admin role.');
        }
        $role->permissions()->detach();
        $role->delete();
        return back()->with('success', 'Role deleted.');
    }
}
