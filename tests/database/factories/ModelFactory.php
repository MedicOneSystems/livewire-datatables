<?php

use \Faker\Generator;
use Mediconesystems\Harbinger\Message;
use Mediconesystems\Harbinger\Tests\Models\User;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(DummyModel::class, function (Generator $faker) {
    return [
        'relation_id' => $faker->randomNumber(6),
        'subject' => $faker->sentence,
        'body' => $faker->paragraph,
        'flag' => $faker->boolean(),
        'expires_at' => $faker->dateTimeBetween('now', '+ 4 weeks')
    ];
});
