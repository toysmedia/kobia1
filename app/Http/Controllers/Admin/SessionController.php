<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Radacct;
use App\Models\Router;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = Radacct::whereNull('acctstoptime')
            ->orderBy('acctstarttime', 'desc')
            ->paginate(50);

        $routers = Router::select('id', 'name')->get();

        return view('admin.isp.sessions.index', compact('sessions', 'routers'));
    }

    public function disconnect(Request $request, int $id)
    {
        $session = Radacct::findOrFail($id);
        // Mark session as stopped (CoA/disconnect would be done via RADIUS)
        $session->update([
            'acctstoptime'        => now(),
            'acctterminatecause'  => 'Admin-Reset',
        ]);

        return response()->json(['success' => true, 'message' => 'Session disconnected.']);
    }
}
