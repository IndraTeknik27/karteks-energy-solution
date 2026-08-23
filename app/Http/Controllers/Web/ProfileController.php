<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'gender' => ['nullable', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'current_password' => ['nullable', 'required_with:password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        if (! empty($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.']);
            }
            $user->password = Hash::make($data['password']);
            unset($data['password']);
        }

        unset($data['current_password'], $data['password_confirmation']);
        $user->fill($data)->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}