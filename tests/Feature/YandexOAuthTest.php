<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class YandexOAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_route_sends_user_to_yandex_authorize(): void
    {
        config()->set('services.yandex.client_id', 'client-123');
        config()->set('services.yandex.redirect_uri', 'https://finance.hrach.ru/auth/yandex/callback');

        $response = $this->get('/auth/yandex/redirect?mobile_redirect=familyfinance://oauth-callback');

        $response->assertRedirect();

        $location = $response->headers->get('Location');

        $this->assertStringStartsWith('https://oauth.yandex.ru/authorize?', $location);
        $this->assertStringContainsString('client_id=client-123', $location);
        $this->assertStringContainsString('response_type=code', $location);
    }

    public function test_callback_creates_user_and_redirects_back_to_mobile_with_token(): void
    {
        config()->set('services.yandex.client_id', 'client-123');
        config()->set('services.yandex.client_secret', 'secret-123');
        config()->set('services.yandex.redirect_uri', 'https://finance.hrach.ru/auth/yandex/callback');

        Http::fake([
            'https://oauth.yandex.ru/token' => Http::response([
                'access_token' => 'yandex-access-token',
                'token_type' => 'bearer',
            ]),
            'https://login.yandex.ru/info*' => Http::response([
                'id' => 'ya-777',
                'default_email' => 'demo@example.com',
                'real_name' => 'Demo User',
            ]),
        ]);

        $redirectResponse = $this->get('/auth/yandex/redirect?mobile_redirect=familyfinance://oauth-callback');
        parse_str(parse_url((string) $redirectResponse->headers->get('Location'), PHP_URL_QUERY), $query);
        $state = $query['state'] ?? null;

        $response = $this->get('/auth/yandex/callback?code=code-123&state='.urlencode((string) $state));

        $response->assertRedirect();
        $location = $response->headers->get('Location');

        $this->assertStringStartsWith('familyfinance://oauth-callback?', (string) $location);
        $this->assertStringContainsString('token=', (string) $location);
        $this->assertStringContainsString('email=demo%40example.com', (string) $location);

        $user = User::query()->first();
        $this->assertNotNull($user);
        $this->assertSame('ya-777', $user->yandex_id);
        $this->assertSame('demo@example.com', $user->email);
        $this->assertSame('Demo User', $user->name);

        $this->assertSame(1, PersonalAccessToken::query()->count());
    }
}
