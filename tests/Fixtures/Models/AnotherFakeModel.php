<?php

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Lageg\Checkpoint\Traits\HasCheckpoints;

class AnotherFakeModel extends Model
{
    use HasCheckpoints;

    protected $table = 'another_fake_models';

    protected $fillable = [
        'value',
        'other_value',
    ];
}
