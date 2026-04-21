<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class YandexOAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = (string) config('services.yandex.client_id');
        $redirectUri = (string) config('services.yandex.redirect_uri');

        if ($clientId === '' || $redirectUri === '') {
            throw new HttpException(500, 'Yandex OAuth не настроен.');
        }

        $statePayload = [
            'mobile_redirect' => $request->query('mobile_redirect'),
            'ts' => now()->timestamp,
            'nonce' => Str::random(20),
        ];

        $state = Crypt::encryptString(json_encode($statePayload, JSON_THROW_ON_ERROR));

        $authUrl = 'https://oauth.yandex.ru/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'force_confirm' => 'yes',
        ]);

        return redirect()->away($authUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['nullable', 'string'],
        ]);

        $clientId = (string) config('services.yandex.client_id');
        $clientSecret = (string) config('services.yandex.client_secret');
        $redirectUri = (string) config('services.yandex.redirect_uri');

        if ($clientId === '' || $clientSecret === '' || $redirectUri === '') {
            throw new HttpException(500, 'Yandex OAuth не настроен.');
        }

        $tokenResponse = Http::asForm()->post('https://oauth.yandex.ru/token', [
            'grant_type' => 'authorization_code',
            'code' => $request->string('code')->toString(),
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ]);

        if ($tokenResponse->failed()) {
            throw new HttpException(401, 'Не удалось получить токен Yandex.');
        }

        $accessToken = (string) $tokenResponse->json('access_token');

        $user = $this->resolveUserByYandexToken($accessToken);

        $user->tokens()->delete();
        $mobileToken = $user->createToken('mobile')->plainTextToken;

        $mobileRedirect = $this->resolveMobileRedirect($request);

        if ($mobileRedirect === null) {
            throw new HttpException(422, 'Не передан mobile_redirect для возврата в приложение.');
        }

        $redirectToApp = $this->buildRedirectUrl($mobileRedirect, [
            'token' => $mobileToken,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);

        return redirect()->away($redirectToApp);
    }

    public function mobile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'oauthToken' => ['required', 'string'],
        ]);

        $user = $this->resolveUserByYandexToken((string) $validated['oauthToken']);

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    private function resolveUserByYandexToken(string $oauthToken): User
    {
        $profileResponse = Http::withHeaders([
            'Authorization' => 'OAuth '.$oauthToken,
        ])->get('https://login.yandex.ru/info', [
            'format' => 'json',
        ]);

        if ($profileResponse->failed()) {
            throw new HttpException(401, 'Не удалось получить профиль Yandex.');
        }

        $profile = $profileResponse->json();
        $yandexId = (string) ($profile['id'] ?? '');

        if ($yandexId === '') {
            throw new HttpException(422, 'Yandex не вернул идентификатор пользователя.');
        }

        $email = mb_strtolower((string) ($profile['default_email'] ?? ''));
        if ($email === '') {
            $email = 'yandex_'.$yandexId.'@oauth.local';
        }

        $displayName = trim((string) ($profile['real_name'] ?? $profile['display_name'] ?? $profile['login'] ?? ''));
        if ($displayName === '') {
            $displayName = 'Yandex user '.$yandexId;
        }

        $user = User::query()->where('yandex_id', $yandexId)->first();

        if (!$user) {
            $user = User::query()->where('email', $email)->first();
        }

        if ($user) {
            $user->fill([
                'name' => $displayName,
                'email' => $email,
                'yandex_id' => $yandexId,
            ])->save();
        } else {
            $user = User::query()->create([
                'name' => $displayName,
                'email' => $email,
                'yandex_id' => $yandexId,
                'password' => Str::password(32),
            ]);
        }

        return $user;
    }

    private function resolveMobileRedirect(Request $request): ?string
    {
        $fallback = config('services.yandex.mobile_redirect');

        $state = $request->query('state');
        if (!is_string($state) || $state === '') {
            return is_string($fallback) && $fallback !== '' ? $fallback : null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($state), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return is_string($fallback) && $fallback !== '' ? $fallback : null;
        }

        $mobileRedirect = $payload['mobile_redirect'] ?? null;

        if (is_string($mobileRedirect) && $mobileRedirect !== '') {
            return $mobileRedirect;
        }

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    private function buildRedirectUrl(string $baseUrl, array $params): string
    {
        $parts = parse_url($baseUrl);
        $existingQuery = [];

        if (isset($parts['query']) && $parts['query'] !== '') {
            parse_str($parts['query'], $existingQuery);
        }

        $query = http_build_query(array_merge($existingQuery, $params));

        $scheme = $parts['scheme'] ?? '';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        if ($host === '') {
            return $baseUrl.(str_contains($baseUrl, '?') ? '&' : '?').$query;
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port.$path.($query !== '' ? '?'.$query : '').$fragment;
    }
}
