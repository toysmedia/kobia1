<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\CmsService;
use Illuminate\Http\Request;

class SuperAdminCmsController extends Controller
{
    public function __construct(private CmsService $cms) {}

    public function index()
    {
        $sections = [
            'hero'     => $this->cms->getSection('hero'),
            'features' => $this->cms->getSection('features'),
            'pricing'  => $this->cms->getSection('pricing'),
            'contact'  => $this->cms->getSection('contact'),
            'footer'   => $this->cms->getSection('footer'),
        ];

        return view('super-admin.cms.index', compact('sections'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'sections'     => 'required|array',
            'sections.*'   => 'array',
            'sections.*.*' => 'nullable|string',
        ]);

        foreach ($data['sections'] as $section => $keys) {
            $this->cms->setSection($section, $keys);
        }

        return back()->with('success', 'CMS content updated.');
    }
}
