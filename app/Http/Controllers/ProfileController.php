<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('settings.services', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('settings.services')->with('status', 'profile-updated');
    }

    /**
     * Update required profile information for onboarding.
     */
    public function updateRequired(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255|min:2',
            'last_name' => 'required|string|max:255|min:2',
            'middle_name' => 'nullable|string|max:255',
            'phone_no' => 'required|string|max:15|min:10|regex:/^[0-9+\-\s()]+$/',
            'lga' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'bvn' => 'required|digits:11|unique:users,bvn,' . $user->id,
            'nin' => 'required|digits:11|unique:users,nin,' . $user->id,
            'pin' => 'required|digits:5',
            'termsCheck' => 'required|string|max:500', 
        ], [
            // ... existing validation messages ...
            'pin.required' => 'Transaction PIN is required.',
            'pin.digits' => 'PIN must be exactly 5 digits.',
        ]);

        try {
            $user->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'],
                'phone_no' => $validated['phone_no'],
                'lga' => $validated['lga'],
                'state' => $validated['state'],
                'address' => $validated['address'],
                'bvn' => $validated['bvn'],
                'nin' => $validated['nin'],
                'pin' => bcrypt($validated['pin']), 
                'profile_completed' => true, 
            ]);

            return redirect()->route('dashboard')->with('success', 'Account successfully! Welcome aboard! 🎉');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Upload or update profile photo.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB max
        ]);

        $user = Auth::user();

        try {
            // ✅ Delete old photo if exists (and not external link)
            if ($user->photo && !Str::startsWith($user->photo, 'http')) {
                $oldPath = str_replace(url('/') . '/storage/', '', $user->photo);
                Storage::disk('public')->delete($oldPath);
            }

            // ✅ Store new image
            $path = $request->file('photo')->store('profile_photos', 'public');

            // ✅ Build full HTTP link
            $fullUrl = url('storage/' . $path);

            // ✅ Save to database
            $user->update([
                'photo' => $fullUrl,
            ]);

            return back()->with('status', '✅ Profile photo updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Failed to update profile photo: ' . $e->getMessage());
        }
    }

    /**
     * Check if user needs to complete profile (for modal trigger)
     */
    public function checkProfileCompletion(): bool
    {
        $user = Auth::user();
        
        // Define required fields that must be filled
        $requiredFields = [
            'first_name', 'last_name', 'phone_no', 'lga', 
            'state', 'address', 'bvn', 'nin'
        ];

        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                return false;
            }
        }

        return true;
    }
    /**
     * Update additional profile information (only if not already set).
     */
    public function updateAdditionalInfo(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'nin' => 'nullable|digits:11|unique:users,nin,' . $user->id,
            'bvn' => 'nullable|digits:11|unique:users,bvn,' . $user->id,
            'state' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $updates = [];

        // Only allow updating if the field is currently empty in the database
        if (empty($user->business_name) && !empty($validated['business_name'])) {
            $updates['business_name'] = $validated['business_name'];
        }
        if (empty($user->nin) && !empty($validated['nin'])) {
            $updates['nin'] = $validated['nin'];
        }
        if (empty($user->bvn) && !empty($validated['bvn'])) {
            $updates['bvn'] = $validated['bvn'];
        }
        if (empty($user->state) && !empty($validated['state'])) {
            $updates['state'] = $validated['state'];
        }
        if (empty($user->lga) && !empty($validated['lga'])) {
            $updates['lga'] = $validated['lga'];
        }
        if (empty($user->address) && !empty($validated['address'])) {
            $updates['address'] = $validated['address'];
        }

        if (!empty($updates)) {
            $user->update($updates);
            return back()->with('status', 'Additional information updated successfully.');
        }

        return back()->with('info', 'No changes made or fields are already set.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => bcrypt($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Update the user's transaction PIN.
     */
    public function updatePin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'pin' => ['required', 'digits:5', 'confirmed'],
        ]);

        $request->user()->update([
            'pin' => bcrypt($validated['pin']),
        ]);

        return back()->with('status', 'pin-updated');
    }
}