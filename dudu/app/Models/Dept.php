<?php

namespace App\Models;

use App\Models\Common\Model;

class Dept extends Model
{
    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::Class)
            ->withTimestamps()
            ->using(DeptUser::Class);
    }

    public function dept_user(){
        return $this->hasMany(DeptUser::Class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::Class)
            // ->withPivot([])
            ->withTimestamps()
            ->using(TaskUser::Class);
    }

    public function sub_org(){
        return $this->belongsToMany('App\Models\Org', 'merge_dept_org', 'dept_id', 'org_id')
        ->withPivot(['deleted_at'])->where('is_active', 1)->whereNull('merge_dept_org.deleted_at');
    }
}
