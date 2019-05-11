<?php

namespace App\Models;

use App\Models\Common\Model;

class Remind extends Model
{
    public function schedule(){
        return $this->belongsTo('App\Models\Schedule', 'schedule_id');
    }
}
