<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->get();
        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        $shops = Shop::all();
        return view('superadmin.users.create', compact('shops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'role'           => 'required|in:superadmin,admin,cashier',
            'password'       => 'required|string|min:6|confirmed',
            'profile_image'  => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'gender'         => 'nullable|in:male,female',
            'dob'            => 'nullable|date',
        ]);

        $user               = new User();
        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->role         = $request->role;
        $user->password     = bcrypt($request->password);
        $user->shop_id      = $request->shop_id;
        $user->phone        = $request->phone;
        $user->address      = $request->address;
        $user->gender       = $request->gender;
        $user->dob          = $request->dob;

        if ($request->hasFile('profile_image')) {
            $file     = $request->file('profile_image');
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
            'action'  => 'user_created'
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', __('messages.user_created_successfully'));
    }

    public function edit(User $user)
    {
        $shops = Shop::all();
        return view('superadmin.users.edit', compact('user','shops'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'role'           => 'required|in:superadmin,admin,cashier',
            'password'       => 'nullable|string|min:6|confirmed',
            'profile_image'  => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'gender'         => 'nullable|in:male,female',
            'dob'            => 'nullable|date',
        ]);

        $user->name    = $request->name;
        $user->email   = $request->email;
        $user->role    = $request->role;
        $user->shop_id = $request->shop_id;
        $user->phone   = $request->phone;
        $user->address = $request->address;
        $user->gender  = $request->gender;
        $user->dob     = $request->dob;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && File::exists(public_path($user->profile_image))) {
                File::delete(public_path($user->profile_image));
            }

            $file     = $request->file('profile_image');
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

        return redirect()->route('superadmin.users.index')
            ->with('success', __('messages.user_updated_successfully'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        SystemLog::create([
            'user_id' => auth()->id(),
            'action'  => 'user_deleted'
        ]);
        return back()->with('success', __('messages.user_deleted_successfully'));
    }
}