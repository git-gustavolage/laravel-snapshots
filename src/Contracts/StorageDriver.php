<?php

namespace Lageg\Checkpoint\Contracts;

use Illuminate\Support\Collection;
use Lageg\Checkpoint\Models\CheckpointModel;

interface StorageDriver
{
    public function save(array $data): CheckpointModel;

    public function clear(string $model_type, int $model_id): void;

    /** @return Collectiom<int, CheckpointModel> */
    public function load(string $model_type, int $model_id, ?string $context_type, ?string $context_identifier): Collection;

    public function delete(string $model_type, int $model_id, ?string $context_type, ?string $context_identifier): bool;
}
