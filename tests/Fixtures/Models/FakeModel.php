<?php

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Lageg\Checkpoint\Traits\HasCheckpoints;

class FakeModel extends Model
{
    use HasCheckpoints;

    protected $table = 'fake_models';

    protected $fillable = [
        'value',
        'other_value',
    ];
}
