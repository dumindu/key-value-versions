<?php

namespace App\Repositories;

use App\Models\KeyValueVersion;
use Illuminate\Support\Facades\DB;

interface KeyValueVersionRepositoryInterface
{
    public function create(array $data);

    public function getLatestByKey(string $key);

    public function getAllLatestKeys();

    public function getAllByKey(string $key);
}

class KeyValueVersionRepository implements KeyValueVersionRepositoryInterface
{
    protected KeyValueVersion $model;

    public function __construct(KeyValueVersion $model)
    {
        $this->model = $model;
    }

    public function getAllLatestKeys()
    {
        return $this->model
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('key_value_versions')
                    ->groupBy('key');
            })->get();
    }

    public function create(array $data): KeyValueVersion
    {
        return $this->model->create($data);
    }

    public function getLatestByKey(string $key): ?KeyValueVersion
    {
        return $this->model
            ->where('key', $key)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public function getAllByKey(string $key)
    {
        return $this->model
            ->where('key', $key)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function getLatestByKeyAndTimestamp(string $key, $timestamp): ?KeyValueVersion
    {
        return $this->model
            ->where('key', $key)
            ->where('created_at', '<=', $timestamp)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }
}
