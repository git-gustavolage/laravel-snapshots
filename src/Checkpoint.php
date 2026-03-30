<?php

namespace Lageg\Checkpoint;

use DateTimeImmutable;

final class Checkpoint
{
    public function __construct(
        public string $context_type,
        public string $context_identifier,
        public string $model_type,
        public string $model_id,
        public array $data,
        public DateTimeImmutable $created_at,
    ) {}

    public function toArray(): array
    {
        return [
            'context_type' => $this->context_type,
            'context_identifier' => $this->context_identifier,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'data' => $this->data,
            'created_at' => $this->created_at,
        ];
    }
}
