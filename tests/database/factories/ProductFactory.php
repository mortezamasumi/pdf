<?php

use Tests\Services\Product;

$factory->define(Product::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name(),
        'type' => $faker->mimeType(),
    ];
});
