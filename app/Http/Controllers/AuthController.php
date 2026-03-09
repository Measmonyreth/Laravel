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
                'role' => 'sometimes|string',
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
                'role' => $request->role ?? 'user',
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
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $path = Storage::disk('public')->put('users', $image);

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $data = array_filter([
            'name' => $request->input('name') ?? $user->name,
            'email' => $request->input('email') ?? $user->email,
            'phone' => $request->input('phone') ?? $user->phone,
            'address' => $request->input('address') ?? $user->address,
            'country' => $request->input('country') ?? $user->country,
            'cityOrProvince' => $request->input('cityOrProvince') ?? $user->cityOrProvince,
            'role' => $request->input('role') ?? $user->role,
            'sex' => $request->input('sex') ?? $user->sex,
            'avatar' => $path ?? $user->avatar,
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

    public function getAllUsers(Request $request)
    {
        $main_user = auth('sanctum')->user();
        if (! $main_user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        } else {
            $users = User::all();
            foreach ($users as $user) {
                $user->avatar = $user->avatar ? asset('storage/'.$user->avatar) : null;
            }

            return response()->json([
                'users' => $users,
            ]);
        }
    }
}
