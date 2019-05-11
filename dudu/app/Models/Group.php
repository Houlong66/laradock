<?php

namespace App\Models;

use App\Models\Common\Model;

class Group extends Model
{
    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function tasks(){
        return $this->hasMany(Task::class,"group_id");
    }

    public function users()
    {
        return $this->belongsToMany(User::Class)
             ->using(GroupUser::Class)
             ->withPivot([
                 'role_id'
             ])
             ->withTimestamps();
    }

}
