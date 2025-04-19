<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Moinul\LinkScanner\Models\BrokenLink;

class BrokenLinkFactory extends Factory
{
    protected $model = BrokenLink::class;

    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'status_code' => $this->faker->randomElement([400, 401, 403, 404, 500, 502, 503]),
            'reason' => $this->faker->sentence,
            'checked_at' => now(),
        ];
    }
} 