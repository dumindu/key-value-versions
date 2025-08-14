<?php

use App\Models\KeyValueVersion;
use App\Repositories\KeyValueVersionRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(KeyValueVersionRepository::class);
});

it('can create a record', function () {
    $data = [
        'key' => 'foo',
        'value' => json_encode(['bar' => 'baz']),
        'value_hash' => hash('sha256', json_encode(['bar' => 'baz'])),
        'created_at' => now(),
    ];

    $record = $this->repository->create($data);

    expect($record)->toBeInstanceOf(KeyValueVersion::class);
    $this->assertDatabaseHas('key_value_versions', ['key' => 'foo']);
});

it('can get latest by key', function () {
    KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => now()->subDay(),
    ]);

    $latest = KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => now(),
    ]);

    $result = $this->repository->getLatestByKey('foo');

    expect($result->id)->toBe($latest->id);
});

it('can get all latest keys', function () {
    KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => now()->subDay(),
    ]);
    $latestFoo = KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => now(),
    ]);

    KeyValueVersion::factory()->create([
        'key' => 'bar',
        'created_at' => now(),
    ]);

    $results = $this->repository->getAllLatestKeys();

    expect($results)->toHaveCount(2)
        ->and($results->contains('id', $latestFoo->id))->toBeTrue();
});

it('can get all by key', function () {
    $records = KeyValueVersion::factory()->count(3)->create([
        'key' => 'foo',
    ]);

    $results = $this->repository->getAllByKey('foo');

    expect($results)->toHaveCount(3)
        ->and($results->first()->id)->toBe($records->last()->id);
});

it('can get latest by key and timestamp', function () {
    $oldRecord = KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => Carbon::parse('2023-01-01'),
    ]);

    KeyValueVersion::factory()->create([
        'key' => 'foo',
        'created_at' => Carbon::parse('2023-02-01'),
    ]);

    $timestamp = Carbon::parse('2023-01-15');

    $result = $this->repository->getLatestByKeyAndTimestamp('foo', $timestamp);

    expect($result->id)->toBe($oldRecord->id);
});
