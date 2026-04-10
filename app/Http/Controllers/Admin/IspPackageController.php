<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IspPackage;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class IspPackageController extends Controller
{
    public function index()
    {
        $packages = IspPackage::orderBy('price')->paginate(20);
        return view('admin.isp.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.isp.packages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'speed_upload'   => 'required|integer|min:1',
            'speed_download' => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'validity_days'  => 'required|integer|min:0',
            'validity_hours' => 'required|integer|min:0',
            'type'           => 'required|in:pppoe,hotspot,both',
            'data_limit_mb'  => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'description'    => 'nullable|string',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $package = IspPackage::create($data);
        AuditLog::record('package.created', IspPackage::class, $package->id, [], $package->toArray());
        return redirect()->route('admin.isp.packages.index')->with('success', "Package '{$package->name}' created.");
    }

    public function edit(IspPackage $ispPackage)
    {
        return view('admin.isp.packages.edit', ['package' => $ispPackage]);
    }

    public function update(Request $request, IspPackage $ispPackage)
    {
        $old = $ispPackage->toArray();
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'speed_upload'   => 'required|integer|min:1',
            'speed_download' => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'validity_days'  => 'required|integer|min:0',
            'validity_hours' => 'required|integer|min:0',
            'type'           => 'required|in:pppoe,hotspot,both',
            'data_limit_mb'  => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'description'    => 'nullable|string',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $ispPackage->update($data);
        AuditLog::record('package.updated', IspPackage::class, $ispPackage->id, $old, $ispPackage->fresh()->toArray());
        return redirect()->route('admin.isp.packages.index')->with('success', "Package updated.");
    }

    public function destroy(IspPackage $ispPackage)
    {
        AuditLog::record('package.deleted', IspPackage::class, $ispPackage->id, $ispPackage->toArray(), []);
        $ispPackage->delete();
        return redirect()->route('admin.isp.packages.index')->with('success', 'Package deleted.');
    }
}
