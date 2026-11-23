<?php

namespace Tests\Feature;

use Tests\TestCase;

class TunnelHttpsAssetTest extends TestCase
{
    public function test_cloudflare_tunnel_request_generates_https_assets_in_layout(): void
    {
        $response = $this->withServerVariables([
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'SERVER_PORT' => 443,
            'HTTPS' => 'on',
        ])->get('https://random-subdomain.trycloudflare.com/');

        $response->assertStatus(200);

        // Content should contain https asset URLs
        $content = $response->getContent();
        $this->assertStringContainsString('https://random-subdomain.trycloudflare.com/assets/css/style.css', $content);
        $this->assertStringContainsString('https://random-subdomain.trycloudflare.com/assets/js/main.js', $content);
        $this->assertStringNotContainsString('http://localhost:8000/assets/css/style.css', $content);
    }

    public function test_cloudflare_tunnel_proxy_headers_generates_correct_https_domain_assets(): void
    {
        $response = $this->withServerVariables([
            'HTTP_X_FORWARDED_HOST' => 'my-subdomain.trycloudflare.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('https://my-subdomain.trycloudflare.com/assets/css/style.css', $content);
        $this->assertStringContainsString('https://my-subdomain.trycloudflare.com/assets/js/main.js', $content);
    }
}
