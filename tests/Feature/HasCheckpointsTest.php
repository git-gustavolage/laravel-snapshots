<?php

use Illuminate\Database\Eloquent\Relations\HasMany;
use Lageg\Checkpoint\Models\CheckpointModel;
use Tests\Fixtures\Models\AnotherFakeModel;
use Tests\Fixtures\Models\FakeModel;

it('creates a checkpoint via trait', function () {
    $model = FakeModel::create(['value' => 'some value']);

    $model->checkpoint('checkpoint', 'default')->save();

    expect(CheckpointModel::count())->toBe(1);

    $checkpoint = CheckpointModel::first();

    expect($checkpoint->context_type)->toBe('checkpoint')
        ->and($checkpoint->context_identifier)->toBe('default')
        ->and($checkpoint->model_id)->toBe($model->id);
});

it('returns a hasMany relationship ordered correctly', function () {
    $model = FakeModel::create(['value' => 'some value']);

    expect($model->checkpoints())->toBeInstanceOf(HasMany::class);
});

it('stores the correct fields of the model', function () {
    $model = FakeModel::create([
        'value' => 'some value',
        'other_value' => 'other value',
    ]);

    $model->checkpoint('checkpoint')->save(['value']);
    $model->checkpoint('checkpoint')->save(['other_value']);

    $first = $model->checkpoint('checkpoint')->history()->first();
    $latest = $model->latestCheckpoint();

    expect($first->data)->toMatchArray(['value' => 'some value']);
    expect($latest->data)->toMatchArray(['other_value' => 'other value']);
});

it('returns latest checkpoint when exists', function () {
    $model = FakeModel::create([
        'value' => 'some value',
        'other_value' => 'other value',
    ]);

    $model->checkpoint('checkpoint')->save(['value']);
    $model->checkpoint('checkpoint')->save(['other_value']);

    $latest = $model->latestCheckpoint();

    expect($latest->data)->toMatchArray(['other_value' => 'other value']);
});

it('returns null when no checkpoints exist', function () {
    $model = FakeModel::create(['value' => 'some value']);

    $latest = $model->latestCheckpoint();

    expect($latest)->toBeNull();
});

it('clears all checkpoints for model', function () {
    $a = FakeModel::create(['value' => 'some value']);
    $b = FakeModel::create(['value' => 'some value']);

    $a->checkpoint('checkpoint')->save(['value']);
    $a->checkpoint('checkpoint2')->save(['other_value']);

    $b->checkpoint('checkpoint')->save(['value']);

    expect(CheckpointModel::count())->toBe(3);

    $a->clearCheckpoints();

    expect(CheckpointModel::count())->toBe(1);
});

it('deletes checkpoints by context', function () {
    $model = FakeModel::create(['value' => 'some value']);

    $model->checkpoint('checkpoint', 'a')->save(['value']);
    $model->checkpoint('checkpoint', 'b')->save(['other_value']);

    expect(CheckpointModel::count())->toBe(2);

    $deleted = $model->checkpoint('checkpoint', 'a')->delete();

    expect($deleted)->toBeTrue();
    expect(CheckpointModel::count())->toBe(1);
});

it('returns false when deleting non-existing checkpoint', function () {
    $model = FakeModel::create(['value' => 'some value']);

    $result = $model->checkpoint('checkpoint')->delete();

    expect($result)->toBeFalse();
});

it('returns checkpoint history collection', function () {
    $model = FakeModel::create(['value' => 'some value']);

    $model->checkpoint('checkpoint')->save(['value']);
    $model->checkpoint('checkpoint')->save(['other_value']);

    $history = $model->checkpoint('checkpoint')->history();

    expect($history)->toHaveCount(2);
});

it('does not mix checkpoints between models', function () {
    $a = FakeModel::create(['value' => 'some value']);
    $b = FakeModel::create(['value' => 'some value']);

    $a->checkpoint('checkpoint')->save(['value']);
    $b->checkpoint('checkpoint')->save(['value']);

    $historyA = $a->checkpoint('checkpoint')->history();
    $historyB = $b->checkpoint('checkpoint')->history();

    expect($historyA)->toHaveCount(1);
    expect($historyB)->toHaveCount(1);
});

it('does not mix checkpoints between different model types with same id', function () {
    $a = FakeModel::create(['value' => 'A']);
    $b = AnotherFakeModel::create(['value' => 'B']); // id = 1 também

    $a->checkpoint('checkpoint')->save(['value']);
    $b->checkpoint('checkpoint')->save(['value']);

    expect($a->checkpoints)->toHaveCount(1);
    expect($b->checkpoints)->toHaveCount(1);
});

it('fails when model is not persisted', function () {
    $model = new FakeModel(['value' => 'x']);

    expect(fn () => $model->checkpoint('checkpoint')->save(['value'])
    )->toThrow(RuntimeException::class);
});

it('isolates by context identifier', function () {
    $model = FakeModel::create(['value' => 'x']);

    $model->checkpoint('checkpoint', 'a')->save(['value']);
    $model->checkpoint('checkpoint', 'b')->save(['value']);

    $a = $model->checkpoint('checkpoint', 'a')->history();
    $b = $model->checkpoint('checkpoint', 'b')->history();

    expect($a)->toHaveCount(1);
    expect($b)->toHaveCount(1);
});
