<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Mail\LoginCodeMail;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => mb_strtolower($request->string('email')->toString()),
            'password' => $request->string('password')->toString(),
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', mb_strtolower($request->string('email')->toString()))
            ->first();

        if (!$user || !Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json(['message' => 'Неверный email или пароль.'], 422);
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Выход выполнен.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower($validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Если такой email существует, мы отправили код для входа.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        Mail::to($email)->send(new LoginCodeMail($code));

        return response()->json([
            'message' => 'Если такой email существует, мы отправили код для входа.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = mb_strtolower($validated['email']);
        $resetRow = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRow || !Hash::check($validated['code'], $resetRow->token)) {
            return response()->json([
                'message' => 'Неверный код или email.',
            ], 422);
        }

        if (now()->diffInMinutes($resetRow->created_at) > 15) {
            return response()->json([
                'message' => 'Срок действия кода истек.',
            ], 422);
        }

        $user = User::query()->where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Неверный код или email.',
            ], 422);
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
