<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    // 将url模型关联到task模型，指明一条url属于一个任务
    public function task(){
        return $this->belongsTo(Task::class);
    }

    public function natice(){
        return $this->belongsTo(Notification::class);
    }
}
