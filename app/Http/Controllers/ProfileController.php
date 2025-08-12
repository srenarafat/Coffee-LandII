<?php


namespace App\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;


class ProfileController extends Controller
{
    

    /**
     * Show the user info page.
     */
    public function info(): View
    {
        $user = auth()->user();
        return view('profile.info', compact('user'));
    }


    /**
     * Update only the phone field from the profile info view.
     */
    public function updateUserCode(Request $request)
    {
        $request->validate([
            'user_code' => 'required|string|max:20',
        ]);


        $user = auth()->user();
        $user->user_code = $request->input('user_code');
        $user->save();


        return redirect()->back()->with('success', __('messages.user_id_updated_successfully'));
    }




    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);


        $user = $request->user();


        Auth::logout();


        $user->delete();


        $request->session()->invalidate();
        $request->session()->regenerateToken();


        return Redirect::to('/');
    }


    // Show Email Page
    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);


        $user = auth()->user();
        $user->email = $request->input('email');
        $user->email_verified_at = null; // Optional: force re-verification
        $user->save();


        return redirect()->route('profile.info')->with('status', __('messages.email_updated_successfully'));
    }


    // Show Gender Page
    public function updateGender(Request $request)
    {
        $request->validate([
            'gender' => 'required|in:male,female',
        ]);


        $user = auth()->user();
        $user->gender = $request->gender;
        $user->save();


        return redirect()->route('profile.info')->with('success', __('messages.gender_updated_successfully'));
    }


   
    // Show calendar page
    public function updateDob(Request $request)
    {
        $request->validate([
            'dob' => 'nullable|date',
        ]);


        $user = auth()->user();
        $user->dob = $request->dob;
        $user->save();


        return redirect()->route('profile.info')->with('success', __('messages.dob_updated_successfully'));
    }


    // Show Phone number
    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20'
        ]);
   
        $user = auth()->user();
        $user->phone = $request->input('phone');
        $user->save();
   
        return back()->with('success', __('messages.phone_number_updated_successfully'));
    }




    // Show Address page
    public function updateAddress(Request $request)
    {
        $request->validate([
            'address' => 'nullable|string|max:500',
        ]);


        $user = auth()->user();
        $user->address = $request->address;
        $user->save();


        return redirect()->route('profile.info')->with('success', __('messages.address_updated_successfully'));
    }


    // Switch Account Page
    public function showSwitchAccount()
    {
        $users = User::all();
        return view('profile.switch-account', compact('users'));
    }
   
    public function switchToAccount(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
   
        Auth::logout();
        Auth::loginUsingId($request->user_id); // ✅ switch directly
   
        return redirect()->route(
            Auth::user()->role === 'admin' ? 'admin.dashboard' : 'cashier.dashboard'
        );
    }
    /**
     * Show the personalization page.
     */
    public function personalization(): View
    {
        return view('profile.personalization');
    }


    /**
     * Show the integrations page.
     */
    public function integrations(): View
    {
        return view('profile.integrations');
    }


    /**
     * Show the change password page.
     */
    public function changePassword(): View
    {
        return view('profile.password');
    }
}






