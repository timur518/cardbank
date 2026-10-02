<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class _TmpAgeValidationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $dateOfBirth): array
    {
        return [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'phone' => '+79990000001',
            'email' => 'test-age@example.com',
            'date_of_birth' => $dateOfBirth,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'personal_data_consent' => true,
        ];
    }

    public function test_register_rejects_under_18(): void
    {
        $tooYoung = now()->subYears(17)->format('d.m.Y');

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/v1/auth/register', $this->payload($tooYoung));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date_of_birth');
    }

    public function test_register_rejects_exactly_17_years_364_days(): void
    {
        $almost18 = now()->subYears(18)->addDay()->format('d.m.Y');

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/v1/auth/register', $this->payload($almost18));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date_of_birth');
    }

    public function test_register_accepts_exactly_18_years_old(): void
    {
        $exactly18 = now()->subYears(18)->format('d.m.Y');

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/v1/auth/register', $this->payload($exactly18));

        $response->assertStatus(201);
    }

    public function test_register_accepts_over_18(): void
    {
        $adult = now()->subYears(30)->format('d.m.Y');

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/v1/auth/register', $this->payload($adult));

        $response->assertStatus(201);
    }

    public function test_register_landing_rejects_under_18(): void
    {
        $tooYoung = now()->subYears(15)->format('d.m.Y');

        $payload = $this->payload($tooYoung);
        unset($payload['password'], $payload['password_confirmation']);

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/v1/auth/register-landing', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date_of_birth');
    }
}
