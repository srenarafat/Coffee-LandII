<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('shop_id', auth()->user()->shop_id)
                      ->where('role', 'cashier')
                      ->latest()
                      ->paginate(10);
        return view('admin.users.index', compact('users'));
    }

        public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'role'           => 'required|in:cashier',
            'password'       => 'required|string|min:6|confirmed',
            'profile_image'  => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'gender'         => 'nullable|in:male,female',
            'dob'            => 'nullable|date',
        ]);

        $user = new User();
        $user->name     = $request->name;
        $user->email = $request->email;
        $user->role     = $request->role;
        $user->shop_id  = auth()->user()->shop_id;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->gender = $request->gender;
        $user->dob = $request->dob;

        $user->shop_id = auth()->user()->shop_id;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->password = bcrypt($request->password);
        
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/users'), $filename);
            $user->profile_image = 'images/users/' . $filename;
        }

        $user->save();

        
        SystemLog::create([
            'user_id' => auth()->id(),
            'action'  => 'user_created'
        ]);

        return redirect()->route('admin.users.index')->with('success', __('messages.user_created_successfully'));
    }

    public function edit(User $user)
    {
        if ($user->shop_id !== auth()->user()->shop_id || $user->role !== 'cashier') {
            abort(403);
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->shop_id !== auth()->user()->shop_id || $user->role !== 'cashier') {
            abort(403);
        }

        $request->validate([
            'name'           => 'required|string|max:255',
            'role'           => 'required|in:cashier',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'password'       => 'nullable|string|min:6|confirmed',
            'profile_image'  => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'gender'         => 'nullable|in:male,female',
            'dob'            => 'nullable|date',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->gender = $request->gender;
        $user->dob = $request->dob;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && File::exists(public_path($user->profile_image))) {
                File::delete(public_path($user->profile_image));
            }

            $file = $request->file('profile_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/users'), $filename);
            $user->profile_image = 'images/users/' . $filename;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        SystemLog::create([
            'user_id' => auth()->id(),
            'action'  => 'user_updated'
        ]);

        return redirect()->route('admin.users.index')->with('success', __('messages.user_updated_successfully'));
    }

    public function destroy(User $user)
    {
        if ($user->shop_id !== auth()->user()->shop_id || $user->role !== 'cashier') {
            abort(403);
        }

        if ($user->profile_image && File::exists(public_path($user->profile_image))) {
            File::delete(public_path($user->profile_image));
        }

        $user->delete();

        SystemLog::create([
            'user_id' => auth()->id(),
            'action'  => 'user_deleted'
        ]);
        return back()->with('success', __('messages.user_deleted_successfully'));
    }
}
