<?php

namespace Lageg\Checkpoint\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lageg\Checkpoint\Checkpoint;

interface Manager
{
    public function for(Model $model): static;

    public function context(string $context_type, string $context_identifier = 'default'): static;

    public function save(array $fields = []): Checkpoint;

    public function delete(): bool;

    public function clear(): void;

    /** @return Collection<key, Checkpoint> */
    public function history(): Collection;
}
