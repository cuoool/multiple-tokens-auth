<?php
namespace Livijn\MultipleTokensAuth\Traits;

use Illuminate\Support\Str;
use Livijn\MultipleTokensAuth\Models\ApiToken;

trait HasApiTokens
{
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    public function generateApiToken()
    {
        $useHash = config('auth.guards.api.hash', false);
        $unique = false;
        $token = null;
        $hashedToken = null;

        while (! $unique) {
            $token = Str::random(80);
            $hashedToken = $useHash
                ? hash('sha256', $token)
                : $token;

            $unique = ApiToken::where('token', $hashedToken)->exists() == false;
        }

        ApiToken::create([
            'user_id' => $this->getAuthIdentifier(),
            'token' => $hashedToken,
            'expired_at' => now()->addDays(config('multiple-tokens-auth.token.life_length')),
        ]);

        return $token;
    }

    public function purgeApiTokens()
    {
        $this->apiTokens()->delete();
    }
}
