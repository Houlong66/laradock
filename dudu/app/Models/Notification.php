<?php

namespace App\Models;

use App\Models\Common\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsToMany(User::Class)
            ->withPivot([
                'item_type',
                'check_time',
                'audit_text',
                'audit_time',
                'status',
            ])
            ->whereNull('notification_user.deleted_at')
            ->withTimestamps()
            ->using(NotificationUser::Class);
    }

    public function notification_items()
    {
        return $this->hasMany(NotificationUser::Class);
    }

    public function org(){
        return $this->belongsTo(Org::Class);
    }

    public function group(){
        return $this->belongsTo(Group::Class);
    }

    /**
     * 获得此通知的所有附件
     */
    public function attachments()
    {
        return $this->morphMany('App\Models\Attachment', 'works');
    }

    /**
     * 获取通知关联的日程
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::Class);
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
