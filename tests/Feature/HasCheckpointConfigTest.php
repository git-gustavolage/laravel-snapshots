<?php

use Tests\Fixtures\Models\AnotherFakeModel;
use Tests\Fixtures\Models\FakeModel;

it('ignores global fields when saving checkpoint', function () {
    config()->set('checkpoint.ignored_fields', [
        '*' => ['created_at', 'updated_at'],
    ]);

    $model = FakeModel::create(['value' => 'x']);

    $model->checkpoint('checkpoint')->save();

    $checkpoint = $model->latestCheckpoint();

    expect($checkpoint->data)->not->toHaveKey('created_at')->and($checkpoint->data)->not->toHaveKey('updated_at');
});

it('ignores model specific fields', function () {
    config()->set('checkpoint.ignored_fields', [
        '*' => [],
        FakeModel::class => ['value'],
    ]);

    $model = FakeModel::create([
        'value' => 'x',
        'other_value' => 'y',
    ]);

    $model->checkpoint('checkpoint')->save();

    $checkpoint = $model->latestCheckpoint();

    expect($checkpoint->data)->not->toHaveKey('value')->and($checkpoint->data)->toHaveKey('other_value');
});

it('merges global and model ignored fields', function () {
    config()->set('checkpoint.ignored_fields', [
        '*' => ['created_at'],
        FakeModel::class => ['value'],
    ]);

    $model = FakeModel::create([
        'value' => 'x',
        'other_value' => 'y',
    ]);

    $model->checkpoint('checkpoint')->save();

    $checkpoint = $model->latestCheckpoint();

    expect($checkpoint->data)->not->toHaveKey('created_at')->not->toHaveKey('value')->toHaveKey('other_value');
});

it('ignored fields take precedence over requested fields', function () {
    config()->set('checkpoint.ignored_fields', [
        '*' => ['value'],
    ]);

    $model = FakeModel::create([
        'value' => 'x',
        'other_value' => 'y',
    ]);

    $model->checkpoint('checkpoint')->save(['value', 'other_value']);

    $checkpoint = $model->latestCheckpoint();

    expect($checkpoint->data)->not->toHaveKey('value')->toHaveKey('other_value');
});

it('applies ignored fields when saving without explicit fields', function () {
    config()->set('checkpoint.ignored_fields', [
        '*' => ['value'],
    ]);

    $model = FakeModel::create([
        'value' => 'x',
        'other_value' => 'y',
    ]);

    $model->checkpoint('checkpoint')->save();

    $checkpoint = $model->latestCheckpoint();

    expect($checkpoint->data)->not->toHaveKey('value')->toHaveKey('other_value');
});

it('applies ignored fields per model independently', function () {
    config()->set('checkpoint.ignored_fields', [
        '*' => [],
        FakeModel::class => ['value'],
        AnotherFakeModel::class => ['other_value'],
    ]);

    $a = FakeModel::create([
        'value' => 'x',
        'other_value' => 'y',
    ]);

    $b = AnotherFakeModel::create([
        'value' => 'x',
        'other_value' => 'y',
    ]);

    $a->checkpoint('checkpoint')->save();
    $b->checkpoint('checkpoint')->save();

    $dataA = $a->latestCheckpoint()->data;
    $dataB = $b->latestCheckpoint()->data;

    expect($dataA)->not->toHaveKey('value')->toHaveKey('other_value');

    expect($dataB)->toHaveKey('value')->not->toHaveKey('other_value');
});
