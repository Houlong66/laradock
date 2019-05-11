<?php

namespace App\Models;

use App\Models\Common\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class MergeDeptOrg extends Pivot
{
    use SoftDeletes;

    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function dept()
    {
        return $this->belongsTo(Dept::class);
    }

    protected $dates = ['deleted_at'];
}
