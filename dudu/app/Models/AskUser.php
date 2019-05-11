<?php

namespace App\Models;

use App\Models\Common\Pivot;

class AskUser extends Pivot
{
    public function user(){
        return $this->belongsTo(User::Class);
    }

    public function work_send(){
        return $this->belongsTo(User::Class,'work_send_id');
    }

    public function org(){
        return $this->belongsTo(Org::Class);
    }
}
