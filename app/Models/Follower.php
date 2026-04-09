<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Follower extends Pivot
{
    public $incrementing = true;

    protected $table = 'followers';
}
