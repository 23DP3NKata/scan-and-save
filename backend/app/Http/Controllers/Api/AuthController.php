<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function csrf(Request $request): JsonResponse
    {
        return response()->json(['token' => $request->session()->token()]);
    }

    public function register(AuthRequest $request): JsonResponse
    {
        abort_unless(SystemSetting::getValue('registration_enabled', true), 403, 'Reģistrācija pašlaik ir atspējota.');
        $user = DB::transaction(function () use ($request): User {
            $user = User::create($request->safe()->only(['name', 'email', 'password']));
            foreach (['Elektronika', 'Mājas preces', 'Citi'] as $name) {
                $user->categories()->create(['name' => $name]);
            }

            return $user->fresh();
        });
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => new UserResource($user)], 201);
    }
}
