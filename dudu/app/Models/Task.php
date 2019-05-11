<?php

namespace App\Models;

use App\Models\Common\Model;

use Illuminate\Database\Eloquent\SoftDeletes;


class Task extends Model
{
    use SoftDeletes;
    protected $table = 'tasks'; //表名
    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsToMany(User::Class)
            ->withPivot([
                'item_type',
                'dept_id',
                'receiver_id',
                'receive_time',
                'report_deadline',
                'report_text',
                'report_time',
                'audit_text',
                'audit_time',
                'status',
            ])
            ->whereNull('task_user.deleted_at')
            ->withTimestamps()
            ->using(TaskUser::Class);
    }

    public function task_items()
    {
        return $this->hasMany(TaskUser::Class);
    }

    public function org(){
        return $this->belongsTo(Org::Class);
    }

    public function dept(){
        return $this->hasOne(Dept::Class);
    }

    public function group(){
        return $this->belongsTo(Group::Class);
    }

    /**
     * 获得此任务的所有附件
     */

    public function attachments()
    {
        return $this->morphMany(Attachment::Class, 'works');
    }



    // 获取讨论板信息
    public function msg_boards(){
        return $this->hasMany('App\Models\MsgBoard', 'foreign_id', 'id');
    }

    // 指明一个任务有多个url
    public function url(){
        return $this->hasMany(Url::class, "works_id");
    }
}
