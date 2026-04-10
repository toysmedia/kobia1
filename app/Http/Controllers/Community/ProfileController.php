<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(int $id)
    {
        $user = CommunityUser::findOrFail($id);
        $posts = $user->posts()->approved()->latest()->paginate(10);
        $replies = $user->replies()->where('status', 'visible')->latest()->paginate(10);
        return view('community.profile.show', compact('user', 'posts', 'replies'));
    }

    public function edit()
    {
        $user = Auth::guard('community')->user();
        return view('community.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('community')->user();

        $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'bio'      => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:100'],
            'website'  => ['nullable', 'url', 'max:200'],
            'avatar'   => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only('name', 'bio', 'location', 'website');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('community/avatars', 'public');
        }

        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => ['required'],
                'new_password'     => ['required', 'confirmed', 'min:8'],
            ]);
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Incorrect current password.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }

        $user->update($data);
        return back()->with('success', 'Profile updated successfully.');
    }
}
