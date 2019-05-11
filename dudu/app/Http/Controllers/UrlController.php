<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Url;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class UrlController extends Controller
{
    // 把url插入到数据库
    public function uploadUrl(Request $request){
        switch($request->works_type){
            case 1:
                $works_type = 'App\Models\Task';
                break;
            case 2:
                $works_type = 'App\Models\Notification';
                break;
            case 3:
                $works_type = 'App\Models\Ask';
                break;
            default:
        }

        $url = new Url;
        $url->url_title = $request->url_title;
        $url->url_path = $request->url_path;
        $url->works_type = $works_type;
        $url->works_id = $request->works_id;
        $url->save();

        return $url->id;
    }

    // 查询url
    public function lookupUrl(Request $request){
        switch($request->works_type){
            case 1:
                $works_type = 'App\Models\Task';
                break;
            case 2:
                $works_type = 'App\Models\Notification';
                break;
            case 3:
                $works_type = 'App\Models\Ask';
                break;
            default:
        }

        if ($request->works_id){
            $urlList = Url::where([
                ['works_id',$request->works_id], ['works_type',$works_type ]
            ])->get();
        }
        else{
            $urlList = Url::whereIn('id', $request->id)->get();
        }
        return ResponseJson($urlList);
    }

    // 删除
    public function deleteUrl(Request $request){
        if($request->clear === '1'){
            Url::whereIn('id', $request->urlIdList)->delete();
        }else{
            Url::where('id', $request->id)->delete();
        }
        return ResponseJson();
    }

    // 更新
    public function editUrl(Request $request){
        Url::where('id', $request->id)
            ->update(['url_title' => $request->url_title, 'url_path' => $request->url_path]);
        return ResponseJson();
    }

    // 创建任务时更新任务id
    public function updateTaskId(Request $request){
        Url::whereIn('id', $request->id)->update(['works_id' => $request->works_id]);
        return ResponseJson();
    }
}
