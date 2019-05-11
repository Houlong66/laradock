<?php

namespace App\Models;

use App\Models\Common\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskUser extends Pivot
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function user(){
        return $this->belongsTo(User::Class);
    }

    public function org(){
        return $this->belongsTo(Org::Class);
    }

    public function dept(){
        return $this->belongsTo(Dept::Class);
    }

    public function group(){
        return $this->belongsTo(Group::Class);
    }

    public function send_user(){
        return $this->belongsTo(User::Class,"work_send_id");
    }

    public function worktransfres(){
        return $this->hasMany(WorkTransfer::Class,'work_item_id');
    }

    public function task() {
        return $this->belongsTo(Task::Class);
    }
}
