<?php

namespace App\Models;

use App\Models\Common\Model;

class User extends Model
{

    public function orgs($org_id = null)
    {
        return $this->belongsToMany(Org::Class)
            ->when($org_id !== null, function ($query) use ($org_id) {
                return $query->where('org_id', $org_id);
            })
            ->withPivot(['is_default', 'role_id'])
            ->withTimestamps()
            ->using(OrgUser::Class);
    }


    public function depts($org_id = null)
    {
        return $this->belongsToMany(Dept::Class)
            ->when($org_id != null, function ($query) use ($org_id) {
                return $query->where('org_id', $org_id);
            })
            ->withTimestamps()
            ->using(DeptUser::Class);
    }

    public function groups($org_id = null)
    {
        return $this->belongsToMany(Group::Class)
            ->withPivot([
                'role_id'
            ])
            ->when($org_id != null, function ($query) use ($org_id) {
                return $query->where('org_id', $org_id);
            })
            ->withTimestamps()
            ->using(GroupUser::Class);
    }

    public function tasks($dept_id = null)
    {
        return $this->belongsToMany(Task::Class)
            ->withPivot([
                'id',
                'item_type',
                'dept_id',
                'receiver_id',
                'receive_time',
                'report_deadline',
                'report_text',
                'report_time',
                'audit_time',
                'audit_text',
                'status',
            ])
            ->whereNull('task_user.deleted_at')
            ->when($dept_id != 0, function ($query) use ($dept_id) {
                return $query->where('dept_id', $dept_id);
            })
            ->withTimestamps()
            ->using(TaskUser::Class);
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::Class)
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

    public function asks()
    {
        return $this->belongsToMany(Ask::Class)
            ->withPivot([
                'item_type',
                'audit_text',
                'audit_time',
                'status',
            ])
            ->withTimestamps()
            ->using(AskUser::Class);
    }

    public function orgRole($org_id)
    {
        return $this->orgs()->where('org_id',$org_id)->first();
    }

    public function isInOrg($org_id)
    {
        return $this->orgs()->where('org_id', $org_id)->exists();
    }

    public function isInDept($dept_id)
    {
        return $this->depts()->where('dept_id', $dept_id)->exists();
    }

    public function isInGroup($group_id)
    {
        return $this->groups()->where('group_id', $group_id)->exists();
    }





    // 检查用户是否和另一个ID的用户在同一个机构内
    public function isInTheSameOrgWith($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return false;
        }

        foreach ($user->orgs as $org) {
            // 在此机构
            if ($this->isInOrg($org->id)) {
                return true;
            }

            // 此机构是否为 to_user 的父辈机构
            if($this->availableOrg($org)) return true;
        }

        return false;
    }

    // 递归判断被转交用户的机构id是否在可选范围内
    public function availableOrg($org)
    {
        $parent_org = $org->parent;

        if ($parent_org->id !== null) {
            if ($this->isInOrg($parent_org->id)) {
                return true;
            } else {
                return $this->availableOrg($parent_org);
            }
        }else{
            return false;
        }
    }


    // 检查用户是否拥有某个ID的任务
    public function hasTask($task_id, $dept_id)
    {
        return $this->tasks($dept_id)->where('task_id', $task_id)->exists();
    }

    // 检查用户是否拥有某个ID的任务
    public function taskRole($task_id, $dept_id)
    {
        return $this->tasks($dept_id)->where('task_id', $task_id)->value('item_type');
    }

    // 检查用户是否已经签收某个ID的任务
    public function hasreceivedTask($task_id, $dept_id)
    {

        return $this->tasks($dept_id)->where('task_id', $task_id)->whereNotNull('receive_time')->exists();
    }

    // 检查用户是否可以转交某个ID的任务
    public function canTransferTask($task_id, $dept_id)
    {
        // 要求：用户拥有该任务，且尚未签收，若任务被签收后则不能再被转交
        return ($this->hasTask($task_id, $dept_id) && (!$this->hasreceivedTask($task_id, $dept_id)));
    }


    // 检查用户是否拥有某个ID的通知
    public function hasNotification($notification_id)
    {
        return $this->notifications()->where('notification_id', $notification_id)->exists();
    }

    // 检查用户是否可以转交某个ID的通知
    public function canTransferNotification($notification_id)
    {
        // 要求：用户拥有该通知
        return $this->hasNotification($notification_id);
    }
}
