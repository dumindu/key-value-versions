<?php

namespace Database\Factories;

use App\Models\KeyValueVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class KeyValueVersionFactory extends Factory
{
    protected $model = KeyValueVersion::class;

    public function definition()
    {
        $value = json_encode(['data' => $this->faker->word]);

        return [
            'key' => $this->faker->word,
            'value' => $value,
            'value_hash' => hash('sha256', $value),
            'created_at' => now(),
        ];
    }
}
