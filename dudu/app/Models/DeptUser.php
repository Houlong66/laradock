<?php

namespace App\Models;

use App\Models\Common\Pivot;

class DeptUser extends Pivot
{
    public function dept()
    {
        return $this->belongsTo(Dept::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
