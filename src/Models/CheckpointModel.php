<?php

namespace Lageg\Checkpoint\Models;

use DateTimeImmutable;
use Illuminate\Database\Eloquent\Model;
use Lageg\Checkpoint\Checkpoint;

class CheckpointModel extends Model
{
    protected $table = 'checkpoints';

    protected $fillable = [
        'context_type',
        'context_identifier',
        'model_type',
        'model_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    public function toCheckpoint(): Checkpoint
    {
        return new Checkpoint(
            context_type: $this->context_type,
            context_identifier: $this->context_identifier,
            model_type: $this->model_type,
            model_id: $this->model_id,
            data: $this->data,
            created_at: new DateTimeImmutable($this->created_at),
        );
    }

    public function scopeForModel($query, string $model, int $id)
    {
        return $query->where('model_type', $model)
            ->where('model_id', $id);
    }

    public function scopeForContext($query, string $context, ?string $identifier = null)
    {
        return $query->where('context_type', $context)
            ->when(isset($identifier), fn ($q) => $q->where('context_identifier', $identifier));
    }
}
