<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTransfer extends Model
{

    public function from_user() {
        return $this->belongsTo(User::Class, 'from_user_id');
    }

    public function to_user() {
        return $this->belongsTo(User::Class, 'to_user_id');
    }

    public function taskuser(){
        return $this->belongsTo(TaskUser::Class);
    }
}
