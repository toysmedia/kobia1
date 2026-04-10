<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IspPackage;

class PackageController extends Controller
{
    public function index()
    {
        $packages = IspPackage::where('is_active', true)
            ->select(['id', 'name', 'speed_upload', 'speed_download', 'price', 'validity_days', 'validity_hours', 'type', 'description'])
            ->orderBy('price')
            ->get()
            ->map(function ($p) {
                return [
                    'id'             => $p->id,
                    'name'           => $p->name,
                    'speed_upload'   => $p->speed_upload,
                    'speed_download' => $p->speed_download,
                    'price'          => $p->price,
                    'validity_days'  => $p->validity_days,
                    'validity_hours' => $p->validity_hours,
                    'type'           => $p->type,
                    'description'    => $p->description,
                    'rate_limit'     => "{$p->speed_upload}M/{$p->speed_download}M",
                ];
            });

        return response()->json(['packages' => $packages]);
    }
}
