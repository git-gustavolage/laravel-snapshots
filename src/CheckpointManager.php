<?php

namespace Lageg\Checkpoint;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lageg\Checkpoint\Contracts\Manager;
use Lageg\Checkpoint\Contracts\StorageDriver;
use Lageg\Checkpoint\Models\CheckpointModel;

final class CheckpointManager implements Manager
{
    private Model $model;

    private string $model_type;

    private int $model_id;

    private ?string $context_type = null;

    private ?string $context_identifier = null;

    public function __construct(private StorageDriver $storage) {}

    public function for(Model $model): static
    {
        if (! $model->exists) {
            throw new \RuntimeException('Model must be persisted');
        }

        $this->model_type = get_class($model);
        $this->model_id = $model->id;
        $this->model = $model;

        $this->context_type = null;
        $this->context_identifier = null;

        return $this;
    }

    public function context(string $context_type, string $context_identifier = 'default'): static
    {
        if (! isset($this->model)) {
            throw new \RuntimeException('Model not defined');
        }

        $this->context_type = $context_type;
        $this->context_identifier = $context_identifier;

        return $this;
    }

    public function save(array $fields = []): Checkpoint
    {
        if (! isset($this->model)) {
            throw new \RuntimeException('Model not defined');
        }

        $original = $this->model->getRawOriginal();

        $data = $this->filterData($original, $fields);

        $payload = [
            'context_type' => $this->context_type ?? 'checkpoint',
            'context_identifier' => $this->context_identifier ?? 'default',
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'data' => $data,
        ];

        return $this->storage->save($payload)->toCheckpoint();
    }

    /**
     * @return Collection<int, Checkpoint>
     */
    public function history(): Collection
    {
        if (! isset($this->model)) {
            throw new \RuntimeException('Model not defined');
        }

        return $this->storage
            ->load(
                model_type: $this->model_type,
                model_id: $this->model_id,
                context_type: $this->context_type,
                context_identifier: $this->context_identifier
            )
            ->map(fn (CheckpointModel $model): Checkpoint => $model->toCheckpoint());
    }

    public function delete(): bool
    {
        if (! isset($this->model)) {
            throw new \RuntimeException('Model not defined');
        }

        return $this->storage
            ->delete(
                model_type: $this->model_type,
                model_id: $this->model_id,
                context_type: $this->context_type,
                context_identifier: $this->context_identifier
            );
    }

    public function clear(): void
    {
        if (! isset($this->model)) {
            throw new \RuntimeException('Model not defined');
        }

        $this->storage->clear($this->model_type, $this->model_id);
    }

    private function filterData(array $original, array $fields): array
    {
        $ignored = $this->resolveIgnoredFields();

        $filtered = array_diff_key($original, array_flip($ignored));

        if (empty($fields)) {
            return $filtered;
        }

        return array_intersect_key($filtered, array_flip($fields));
    }

    private function resolveIgnoredFields(): array
    {
        $config = config('checkpoint.ignored_fields', []);

        $global = $config['*'] ?? [];

        $modelSpecific = $config[$this->model_type] ?? [];

        return array_values(array_unique(array_merge($global, $modelSpecific)));
    }
}
