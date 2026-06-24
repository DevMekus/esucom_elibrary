<?php

namespace App\Middleware;

use App\Utils\Response;

class GuestOnlyMiddleware
{
    public function handle()
    {
        // Only check the origin / referer
        $allowedDomains = [
            'http://localhost/pizzasquare_v3/',
            'http://localhost/pizzasquare_v3/pos/',
            ''       // Production
        ];

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $origin  = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (
            (empty($referer) && empty($origin)) ||
            (!$this->startsWithAny($referer, $allowedDomains) &&
                !$this->startsWithAny($origin, $allowedDomains))
        ) {
            Response::error(403, 'Access denied: Request not from allowed domain');
            return false;
        }

        return true;
    }

    private function startsWithAny(string $value, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return true;
            }
        }
        return false;
    }
}
