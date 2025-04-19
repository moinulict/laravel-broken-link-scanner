<?php
namespace Moinulict\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Moinulict\LinkScanner\Models\BrokenLink;

class BrokenLinkFactory extends Factory
{
    protected $model = BrokenLink::class;

    public function definition(): array
    {
        return [
            'url'         => $this->faker->url(),
            'status_code' => 404,
            'reason'      => 'Not Found',
            'checked_at'  => now(),
        ];
    }
}