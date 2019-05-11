<?php

namespace App\Models;

use App\Models\Common\Model;

class Schedule extends Model
{
    public function reminds(){
        return $this->hasMany(Remind::class);
    }

    public function user() {
        return $this->belongsTo(User::class, "creater_id");
    }
}
