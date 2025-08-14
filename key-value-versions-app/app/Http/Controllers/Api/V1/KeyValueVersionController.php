<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\KeyValueVersionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KeyValueVersionController extends Controller
{
    protected KeyValueVersionRepository $repository;

    public function __construct(KeyValueVersionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a newly created resource in storage.
     * This method handles POST /api/v1/keys
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
        ]);

        $hash = hash('sha256', $request->value);

        $current = $this->repository->getLatestByKey($request->key);

        if ($current && $current->value_hash === $hash) {
            return response()->json(
                [
                    'key' => $current->key,
                    'value' => json_decode($current->value, true) ?? $current->value,
                    'createdAt' => $current->created_at->timestamp,
                ]
            );
        }

        $new = $this->repository->create(
            [
                'key' => $request->key,
                'value' => $request->value,
                'value_hash' => $hash,
                'created_at' => Carbon::now('UTC'),
            ]
        );

        return response()->json(
            [
                'key' => $new->key,
                'value' => json_decode($new->value, true) ?? $new->value,
                'createdAt' => $new->created_at->timestamp,
            ],
            201,
        );
    }

    /**
     * Display a listing of all currently stored keys with their latest values.
     * This method handles GET /api/v1/keys
     */
    public function index()
    {
        $all = $this->repository->getAllLatestKeys();

        $formatted = $all->map(function ($latest) {
            return [
                'key' => $latest->key,
                'value' => json_decode($latest->value, true) ?? $latest->value,
                'createdAt' => $latest->created_at->timestamp,
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Display the specified resource (latest value or value at timestamp).
     * This method handles GET /v1/keys/{key} and /v1/keys/{key}?timestamp={unix_timestamp}
     */
    public function show(Request $request, string $key)
    {
        if (! $request->has('timestamp') || ! is_numeric($request->query('timestamp'))) {
            $latest = $this->repository->getLatestByKey($key);
        } else {
            $timestamp = Carbon::createFromTimestamp((int) $request->query('timestamp'), 'UTC');
            $latest = $this->repository->getLatestByKeyAndTimestamp($key, $timestamp);
        }

        if ($latest === null) {
            return response()->json(['error' => 'Key not found or no version at specified timestamp'], 404);
        }

        return response()->json([
            'key' => $latest->key,
            'value' => json_decode($latest->value, true) ?? $latest->value,
            'createdAt' => $latest->created_at->timestamp,
        ]);
    }

    /**
     * Display all values (versions) for a key.
     * This method handles GET /v1/keys/{key}/history
     */
    public function history(string $key)
    {
        $all = $this->repository->getAllByKey($key);

        if ($all->isEmpty()) {
            return response()->json(['error' => 'Key not found'], 404);
        }

        $formatted = $all->map(function ($version) {
            return [
                'key' => $version->key,
                'value' => json_decode($version->value, true) ?? $version->value,
                'createdAt' => $version->created_at->timestamp,
            ];
        });

        return response()->json($formatted);
    }
}
