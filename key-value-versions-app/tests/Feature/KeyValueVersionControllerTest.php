<?php

use App\Models\KeyValueVersion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->baseUrl = '/api/v1/keys';

    Passport::actingAs(
        User::factory()->create(),
        ['*'] // scopes
    );
});

it('stores a new key value version', function () {
    $payload = [
        'key' => 'foo',
        'value' => json_encode(['bar' => 'baz']),
    ];

    $response = $this->postJson($this->baseUrl, $payload);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'key' => 'foo',
            'value' => ['bar' => 'baz'],
        ]);

    $this->assertDatabaseHas('key_value_versions', [
        'key' => 'foo',
    ]);
});

it('returns existing version if hash matches', function () {
    $value = json_encode(['bar' => 'baz']);
    $existing = KeyValueVersion::factory()->create([
        'key' => 'foo',
        'value' => $value,
        'value_hash' => hash('sha256', $value),
    ]);

    $response = $this->postJson($this->baseUrl, [
        'key' => 'foo',
        'value' => $value,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'key' => 'foo',
            'value' => ['bar' => 'baz'],
            'createdAt' => $existing->created_at->timestamp,
        ]);

    $this->assertDatabaseCount('key_value_versions', 1);
});

it('lists all latest keys', function () {
    KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => now()->subDay(),
    ]);
    KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => now(),
    ]);
    KeyValueVersion::factory()->create([
        'key' => 'bar',
    ]);

    $response = $this->getJson($this->baseUrl);

    $response->assertOk();
    $data = $response->json();
    expect($data)->toHaveCount(2);
});

it('shows the latest value for a key', function () {
    $latest = KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => now(),
    ]);

    $response = $this->getJson("{$this->baseUrl}/foo");

    $response->assertOk()
        ->assertJsonFragment([
            'key' => 'foo',
            'createdAt' => $latest->created_at->timestamp,
        ]);
});

it('shows the value for a key at a specific timestamp', function () {
    $old = KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => Carbon::parse('2023-01-01'),
    ]);
    KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => Carbon::parse('2023-02-01'),
    ]);

    $timestamp = Carbon::parse('2023-01-15')->timestamp;

    $response = $this->getJson("{$this->baseUrl}/foo?timestamp={$timestamp}");

    $response->assertOk()
        ->assertJsonFragment([
            'key' => 'foo',
            'createdAt' => $old->created_at->timestamp,
        ]);
});

it('returns 404 when key not found', function () {
    $response = $this->getJson("{$this->baseUrl}/notfound");

    $response->assertStatus(404);
});

it('shows the history for a key', function () {
    KeyValueVersion::factory()->count(3)->create([
        'key' => 'foo',
    ]);

    $response = $this->getJson("{$this->baseUrl}/foo/history");

    $response->assertOk();
    $data = $response->json();
    expect($data)->toHaveCount(3);
});

it('returns 404 when requesting history for non-existent key', function () {
    $response = $this->getJson("{$this->baseUrl}/notfound/history");

    $response->assertStatus(404);
});
