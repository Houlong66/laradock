<?php

namespace App\Models;

use App\Models\Common\Model;
use Illuminate\Database\Eloquent\Builder;

class Org extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('status', function(Builder $builder) {
            $builder->where('status', '=', 1);
        });
    }

    protected $hidden = ['password'];

    public function depts()
    {
        return $this->hasMany(Dept::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * @param  string|array  $role_id
     */
    public function users($role_id = null)
    {
        $data = $this->belongsToMany(User::Class)
            ->withPivot(['role_id', 'is_default'])
            ->withTimestamps()
            ->using(OrgUser::Class);
        if ($role_id != null) {
            $data = $data->wherePivotIn('role_id', $role_id);
        }

        return $data;
    }

    public function parent(){
        return $this->belongsTo($this, 'parent_id');
    }

    public function children(){
        return $this->hasMany($this, 'parent_id');
    }

    public function sup_dept(){
        return $this->belongsToMany('App\Models\Dept', 'merge_dept_org', 'org_id', 'dept_id')
        ->withPivot(['deleted_at'])->where('is_active', 1)->whereNull('merge_dept_org.deleted_at');
    }

    public function ask_type(){
        return $this->hasMany(AskType::class);
    }
}
