<?php

namespace Lageg\Checkpoint\Storages;

use Illuminate\Support\Collection;
use Lageg\Checkpoint\Contracts\StorageDriver;
use Lageg\Checkpoint\Models\CheckpointModel;

final class DatabaseStorage implements StorageDriver
{
    public function save(array $data): CheckpointModel
    {
        return CheckpointModel::create($data);
    }

    public function clear(string $model_type, int $model_id): void
    {
        CheckpointModel::query()
            ->where('model_type', $model_type)
            ->where('model_id', $model_id)
            ->delete();
    }

    public function delete(string $model_type, int $model_id, ?string $context_type, ?string $context_identifier): bool
    {
        $query = CheckpointModel::forModel($model_type, $model_id);

        if ($context_type !== null) {
            $query->where('context_type', $context_type);

            if ($context_identifier !== null) {
                $query->where('context_identifier', $context_identifier);
            }
        }

        return $query->delete() > 0;
    }

    /** @return Collection<int, CheckpointModel> */
    public function load(string $model_type, int $model_id, ?string $context_type, ?string $context_identifier): Collection
    {
        return CheckpointModel::forModel($model_type, $model_id)
            ->when($context_type !== null, function ($query) use ($context_type) {
                $query->where('context_type', $context_type);
            })
            ->when($context_identifier !== null, function ($query) use ($context_identifier) {
                $query->where('context_identifier', $context_identifier);
            })
            ->get();
    }
}
