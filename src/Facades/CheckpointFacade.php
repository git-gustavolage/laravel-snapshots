<?php

namespace Lageg\Checkpoint\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Lageg\Checkpoint\Contracts\Manager;

class CheckpointFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'checkpoint.manager';
    }

    public static function for(Model $model): Manager
    {
        return app('checkpoint.manager')->for($model);
    }
}
