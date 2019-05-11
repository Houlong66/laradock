<?php

namespace App\Http\Controllers;

use App\Models\Dept;
use App\Models\DeptUser;
use App\Models\Group;
use App\Models\User;
use App\Models\Org;
use App\Http\Requests\DeptRequest;
use Illuminate\Support\Facades\DB;

class DeptController extends Controller
{
    // 获取群组内用户对应的部门
    public function getByGroup(DeptRequest $request, Group $group)
    {
        $data = collect();
        $dept_ids = collect();

        $users = $group->users()->with(['depts', 'orgs'])->get();

        foreach(Dept::where('org_id', $group->org_id)->get() as $dept){
            $org_ids = [];
            $org = $dept->sub_org->first();
            $temp = [];
            if($org) $temp[] = $org->id;
//            \Log::info($temp);
            // 若部门对接子机构，则把子机构 id 和子机构对接的所有孙机构的 id 加入 $org_ids 数组
            while($temp != []){
                $temp_org = Org::find($temp[0]);
                $org_ids[] = $temp[0];
                array_splice($temp, 0, 1);
                $children = $temp_org->children()->get()->pluck('id')->toArray();
                array_splice($temp, count($temp)-1, 0, $children);
            }
//            \Log::info($org_ids);
//            if($org) $temp[] = $org->id;
//            while($temp != []){
//                $temp_org = Org::find($temp[0]);
//                $org_ids[] = $temp[0];
//                array_splice($temp, 0, 1);
//                $parent = $temp_org->parent()->get()->pluck('id')->toArray();
//                \Log::info("parent");
//                \Log::info($parent);
//                array_splice($temp, count($temp)-1, 0, $parent);
//            }
//            \Log::info($org_ids);


//            \Log::info("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@");
//            \Log::info($dept->name);
//            \Log::info($dept->id);
            foreach($users as $user){
//                \Log::info("#####################");
//                \Log::info($user->name);
                $orgs = $user->orgs->pluck('id')->toArray();
//                \Log::info($orgs);
                $depts = $user->depts->pluck('id')->toArray();
//                \Log::info($depts);
//                \Log::info($depts);
//                \Log::info([$dept->id]);
//                \Log::info(array_intersect($depts, [$dept->id]));
//                \Log::info($orgs);
//                \Log::info($org_ids);
//                \Log::info(array_intersect($orgs, $org_ids));
                if(array_intersect($orgs, $org_ids) || array_intersect($depts, [$dept->id])){
//                    \Log::info("in 1");
                    if (!$dept_ids->has($dept->id)) {
//                        \Log::info("in 2");
                        $dept_ids->put($dept->id, collect());
                        }
                    $dept_ids[$dept->id]->push($user);
                }
//                \Log::info($dept_ids[$dept->id]);
            }
        }

        // 根据上面集合实例化对应部门模型，并添加对应部门的群组内用户们的字段值
        foreach ($dept_ids as $dept_id => $in_group_users) {
            $dept = Dept::findOrFail($dept_id);
            $dept->in_group_users = $in_group_users;
            $data->push($dept);
        }

        return ResponseJson($data);
    }


    // 创建机构的部门
    public function store(DeptRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // 判断权限
        $role_id = $user->orgs()->where('orgs.id', $request->org_id)->first()->pivot->role_id;
        // hasRole判断是否存在权限，不存在直接返回错误
        hasRole($role_id, [ROLE_SYS, ROLE_GRP], "权限不足，无法操作");



        $dept = Dept::create([
            'org_id' => $request->org_id,
            'name' => $request->name,
            'level' => $request->level,
            'status' => 1
        ]);

        // 判断是否开启了让自己加入部门中
        if($request->adddept){
            DeptUser::create([
                'dept_id' => $dept->id,
                'user_id' => $user_id
            ]);
        }

        return ResponseJson($dept);
    }


    // 根据机构获取部门
    public function getByOrg(DeptRequest $request, Org $org)
    {

        $datas = $org->depts->load(['users']);
        $data = $datas->sortBy('level')->values()->all();
        return ResponseJson($data);
    }

    // 批量修改部门
    public function batchChange(DeptRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // 判断权限
        $role_id = $user->orgs()->where('orgs.id', $request->org_id)->first()->pivot->role_id;
        hasRole($role_id, [ROLE_SYS, ROLE_GRP], "权限不足，无法操作");

        $targets = explode(',', $request->targets);
        $depts = explode(',', $request->depts);

        foreach ($targets as $key => $value) {
            $user = User::find($value);
            $depts_user = $user->depts($request->org_id)->get();
            $user->depts()->detach($depts_user);

            $user->depts()->attach($depts);
        }

        return ResponseJson([]);
    }


    // 修改部门名称 and 部门类型
    public function modifyName(DeptRequest $request){
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $org = $user->orgs()->where('orgs.id', $request->orgId)->first();

        hasRole($org->pivot->role_id, [ROLE_SYS, ROLE_GRP], "权限不足，无法操作");
        $dept = $org->depts()->where('id', $request->deptId)->first();

        $dept->name = $request->deptName;
        $dept->level = $request->level;
        $dept->save();
        return ResponseJson();
    }

    //获取机构信息
    public function getOrg($orgsid){
        //先去拿到机构名
        $org_msg =  DB::table('orgs')->where('id',$orgsid)->first();
        return ResponseJson($org_msg);
    }


    //获取机构下所有部门单位
    public function geDepts($orgsid){
        $all[0] = ['title'=>'部门'];
        $depts = DB::table('depts')->where('org_id', $orgsid)->get()->toArray();
        $all[0]['content'] = $depts;
        foreach ($depts as $k => $v){
            $dept_user_count = DB::table('dept_user')->where('dept_id', $depts[$k]->id)->count();
            $depts[$k]->number = $dept_user_count;
        }
        return ResponseJson($depts);
    }

    //删除功能
    public function deleteDepts(DeptRequest $request){
        $depts_user = DB::table('dept_user')->where('dept_id', $request->deptsId)->count();
        $merge_id = DB::table('merge_dept_org')->where('dept_id', $request->deptsId)->count();
        if ($depts_user == 0 && $merge_id == 0) {
            $delete_depts = DB::table('depts')->where([
                ['org_id','=',  $request->orgId],
                ['id',    '=',  $request->deptsId]
            ])->delete();

        if ($delete_depts == 1)  return ResponseJson();
        return ResponseJson([],1,'服务繁忙!请稍后再试');
        }

        if ($merge_id != 0) return ResponseJson([],1,'删除的部门或下级单位不可以对接到其他机构');
        return ResponseJson([],1,'只有当部门或者下级单位里没有任何用户时,才可以被删除哦');



    }





}
