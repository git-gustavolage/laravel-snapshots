<?php

namespace Lageg\Checkpoint\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Lageg\Checkpoint\Checkpoint;
use Lageg\Checkpoint\Contracts\Manager;
use Lageg\Checkpoint\Facades\CheckpointFacade;
use Lageg\Checkpoint\Models\CheckpointModel;

trait HasCheckpoints
{
    public function checkpoint(string $context, ?string $identifier = null): Manager
    {
        if (! $this->exists) {
            throw new \RuntimeException('Model must be persisted');
        }

        return CheckpointFacade::for($this)->context($context, $identifier);
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(CheckpointModel::class, 'model_id')
            ->where('model_type', static::class);
    }

    public function latestCheckpoint(): ?Checkpoint
    {
        return CheckpointFacade::for($this)->history()->sortByDesc('id')->last();
    }

    public function clearCheckpoints(): void
    {
        CheckpointFacade::for($this)->clear();
    }
}
