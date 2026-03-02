<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6',
            ]
        );

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $path = Storage::disk('public')->put('users', $image);
            $request->avatar = $path;
        }
        User::create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'cityOrProvince' => $request->cityOrProvince,
                'country' => $request->country,
                'sex' => $request->sex,
                'password' => Hash::make($request->password),
                'avatar' => $request->avatar,
            ]
        );

        return response()->json([
            'message' => 'user created successful',
        ], 200);

    }

    public function login(Request $request)
    {
        $request->validate(
            [

                'email' => 'required|string|email',
                'password' => 'required|string',
            ]
        );

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    'message' => 'invalid credential',
                ]
            );
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->avatar = $user->avatar ? asset('storage/'.$user->avatar) : null;

        return response()->json(
            [
                'token' => $token,
                'user' => $user,
            ]
        );

    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'logout successfully',
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user(); // get the authenticated user
        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $path = Storage::disk('public')->put('users', $image);

            if ($user->avatar && Storage::disk('public')->fileExists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $path;
        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $data = array_filter([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'country' => $request->input('country'),
            'sex' => $request->input('sex'),
        ], fn ($value) => $value !== null); // ← skip null values

        $user->update($data);

        return response()->json([
            'message' => 'user updated successfully',
            'user' => $user,
        ]);

    }

    public function getUser(Request $request)
    {
        $user = $request->user();
        $user->avatar = $user->avatar ? asset('storage/'.$user->avatar) : null;

        return response()->json([
            'user' => $user,
        ]);
    }
}
