<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachment;
use App\Models\File;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Ask;
use App\Models\AskUser;
use Chumper\Zipper\Zipper;
use Mockery\Matcher\Not;

class CommonController extends Controller
{
    // 文件下载引导页面 & 实际下载请求处理
    public function index(Request $request)
    {
        $file = File::where('token', $request->token)->first();

        // token 不存在或已过期
        if (empty($file) || ($file->expire_at < date('Y-m-d H:i:s'))) {
            return view('file.expired');
        }

        // 判断下载文件是单个文件还是压缩包，并据此确定文件路径以及下载文件名称
        if (empty($file->attachment_id)) {
            $file_path = $file->zip_file_path;
            $display_name = $file->zip_display_name;
        } else {
            $attach = Attachment::find($file->attachment_id);
            $file_path = $attach->total_path;
            $display_name = $attach->original_name;
        }
        $full_path = storage_path('app/attachments/') . $file_path;

        // 分别处理下载引导页面和实际下载请求
        if ($request->guide == 1) {
            // 引导页面，获取文件类型和文件大小并将文件名、下载 token 值带到引导页面
            // 为了避免测试环境和上线环境没有做好配置的问题，这里把链接生成的工作放到前端了
            $token = $request->token;

            // 提取文件类型和文件大小信息
            $file_type = Storage::disk('attachments')->mimeType($file_path);
            $size = Storage::disk('attachments')->size($file_path);
            $file_size = strval(number_format(($size / pow(2, 20)), 2)) . ' MB';

            return view('file.guide', compact('display_name', 'file_size', 'file_type', 'token'));
        } else {
            // 处理实际下载请求
            $header = ['Content-Type' => 'application/octet-stream'];
            return response()->download($full_path, $display_name, $header);
        }
    }

    // 单个文件下载权限鉴定 & token 生成
    public function download(Request $request)
    {

        $mod = $this->getModel($request->works_type);
//        $model = $mod['model'];
        $modelItem = $mod['modelItem'];

        $attach = Attachment::where('id', $request->file_id)->whereIn('works_id', function($query) use ($request, $modelItem) {
            $query->select("{$request->works_type}_id")
            ->from(with($modelItem)->getTable())
            ->where('user_id', session()->get('user.id'));
        })->first();

        if (empty($attach)) return ResponseJson([], '很抱歉，您不在文件所属任务组中，不能下载该文件');

        // 检查是否已有生成的 token
        $file = File::where('attachment_id', $request->file_id)->first();

        if (empty($file)) {
            $file = new File;
            $file->attachment_id = $request->file_id;

            // 随机数的 SHA 1 值作为盐，拼接请求的文件 ID，计算所得 SHA256 值作为 token
            $file->token = hash('sha256', (sha1((mt_rand())) . $request->file_id));
        }

        $file->expire_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $file->save();

        return ResponseJson(['token' => $file->token]);
    }


    // 批量文件下载（压缩包）权限鉴定 & 压缩包生成 & token 生成
    public function downloadAll(Request $request)
    {
        // 获取工作类
        $mod = $this->getModel($request->works_type);
        $model = $mod['model'];
        $modelItem = $mod['modelItem'];

        // 判断是否有下载权限
        $permitted = $modelItem::where("{$request->works_type}_id", $request->work_id)
            ->where('user_id', session()->get('user.id'))
            ->exists();
        if (!$permitted) return ResponseJson([], '很抱歉，您不在文件所属任务组中，不能打包下载该任务的所有文件');

        // 找到相应任务项
        $work = $model::find($request->work_id);

        // 压缩包文件名和压缩包下载文件名
        // works_item_id 为0 代表工作附件，否则为上传附件
        $prefix = "{$request->works_type}_{$work->id}_";
        $display_name = "「{$work->title}」的";
        if ($request->works_item_id == 0) {
            $prefix .= 'attach';
            $display_name .= '任务附件.zip';
            $last_modified = $work->attachment_last_modified;
        } else {
            $prefix .= 'report';
            $display_name .= '上报附件.zip';
            // 上报附件使用最后一次上报时间作为压缩包最后修改时间
            $last_modified = TaskUser::find($request->works_item_id)->report_time;
        }
        $basename = $prefix . date('_YmdHis', strtotime($last_modified));

        $zip_file_path = "zip/$basename.zip";
        $zip_file_fullpath = storage_path('app/attachments/') . $zip_file_path;

        // 检查是否已有生成的 token
        $file = File::where('zip_file_path', $zip_file_path)->first();

        if (empty($file)) {
            // 提取打包用的文件列表
            $files_arr = [];
            $attachments = $work->attachments->filter(function ($v, $k) use ($request) {
                return $v->works_item_id == $request->works_item_id;
            });

            if ($attachments->isEmpty()) return ResponseJson([], '没有符合条件的附件，无法生成压缩包');

            // 用随机数做个分隔，避免同时压缩的情况下出现同名等情况
            // TODO: 因为时间关系仍然不能保证较高并发下的稳定，进一步的隔离可能要考虑加锁之类的操作了
            $rand = mt_rand();
            $dir =  "zip_prepare/$prefix-$rand";

            // 复制并重命名各文件
            foreach ($attachments as $k => $v) {
                $zip_single_file = "$dir/{$k}_{$v->original_name}"; // 文件的顺序号 + 实际名，便于解压后查看
                Storage::disk('attachments')->copy($v->total_path, $zip_single_file);
                $files_arr[] = storage_path('app/attachments/') . $zip_single_file;
            }

            // 保存压缩包文件
            $zipper = new Zipper();
            $zipper->make($zip_file_fullpath)->add($files_arr)->close();

            // 删除打包所用文件
            Storage::disk('attachments')->deleteDirectory($dir);

            $file = new File;
            $file->zip_file_path = $zip_file_path;
            $file->zip_display_name = $display_name;

            // 随机数的 SHA 1 值作为盐，拼接请求的压缩包名，计算所得 SHA256 值作为 token
            $file->token = hash('sha256', (sha1((mt_rand())) . $zip_file_path));
        }

        $file->expire_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $file->save();

        return ResponseJson(['token' => $file->token]);
    }

    public function upload(Request $request)
    {
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
            case 4:
                $works_type = 'App\Models\Feedback';
                break;
            case 5:
                $works_type = 'App\Models\Article';
                break;
            default:
        }
        $original_name = $request->file->getClientOriginalName();
        $path = $request->file->store('attach', 'attachments');
        $attachment = new Attachment;
        $attachment->original_name = $original_name;
        $attachment->total_path = $path;
        $attachment->works_type = $works_type;
        $attachment->works_item_id = $request->works_item_id;
        $attachment->save();
        return $attachment->id;
    }

    public function getimg(Request $request){
//        var_dump(13);
        $img =  Attachment::find($request->fileid);
        return ResponseJson($img);
    }

    /*
     * 注：因为文件下载整个逻辑的更新，删除附件目前仅删除实际对应的文件，而不处理关联到的压缩包
     * 压缩包的删除在定时任务内，关联到过期链接一起删除
     */
    public function deleteAttach(Request $request) {
        $mod = $this->getModel($request->works_type);
        $modelItem = $mod['modelItem'];

        $permitted = Attachment::where('id', $request->file_id)->whereIn('works_id', function($query) use ($request, $modelItem) {
            $query->select("{$request->works_type}_id")
            ->from(with($modelItem)->getTable())
            ->where('user_id', session()->get('user.id'));
        })->exists();
        if (!$permitted) return ResponseJson([], '文件不存在或用户不在文件所属任务组中');

        $attachment = Attachment::find($request->file_id);

        // 删除附件对应的实际文件
        Storage::disk('attachments')->delete($attachment->total_path);

        // 删除附件数据库记录
        $attachment->delete();

        return ResponseJson('附件删除成功');
    }

    /**
     * 工作关联附件
     * @param $work_id: 工作id
     * @param $r_work_id: 原工作id
     * @param $attachment: 附件id字符串
     * @param $filter: 工作子项id，用于筛选过滤出符合要求的数据
     * @param $work_type: 工作类型
     *
     * todo 控制器间互相调用不符合最佳实践，待优化
     */
    public function attachAttachment($work_id, $r_work_id, $attachment, $filter, $work_type='task'){
        $add_id_arr = []; // 需要关联的附件的id数组
        $delete_id_arr = []; // 需要取消关联的附件的id数组

        // 获取工作类
        $mod = $this->getModel($work_type);
        $model = $mod['model'];

        // 新工作首次关联
        if($r_work_id === 0){
            if($attachment === 0){
                // 无需关联
                return;
            }else{
                $add_id_arr = explode(',',$attachment);
            }
        }else{ // 非新工作
            // 获取新附件的id数组
            $n_attachments_arr = explode(',',$attachment);


            // 获取已关联附件的id数组
            $o_attachments = $model::find($work_id)->attachments
                ->filter(function($value, $key) use ($filter){
                    return (int)$value->task_item_id === $filter;
                });
            $o_attachments_arr = [];
            foreach($o_attachments as $item){
                $o_attachments_arr[] = $item->id;
            }

            // 判断新附件id数组中哪些是附件是未关联过的
            foreach($n_attachments_arr as $n_a){
                if(!in_array($n_a, $o_attachments_arr)){
                    $add_id_arr[] = $n_a;
                }
            }
            // 判断已关联附件id数组中哪些附件是要取消关联的
            foreach($o_attachments_arr as $o_a){
                if(!in_array($o_a, $n_attachments_arr)){
                    $delete_id_arr[] = $o_a;
                }
            }
        }

        // 关联
        Attachment::whereIn('id',$add_id_arr)
            ->update(['works_id' => $work_id]);

        // 取消关联
        Attachment::whereIn('id',$delete_id_arr)
            ->update(['works_id' => NULL]);

        // 根据附件是否有改动决定是否更新附件最后修改时间
        if (!(empty($add_id_arr) && empty($delete_id_arr))) {
            // 有附件新增（可能是新工作首次关联）或删除，则将附件最后修改时间设置为当前时间
            $model::where('id', $work_id)->update([
                'attachment_last_modified' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 判断附件关联工作的类型
     *
     */
    public function getModel($works_type){
        $model = null;
        $modelItem = null;
        switch($works_type){
            case 'task':
                $model = new Task();
                $modelItem = new TaskUser();
                break;
            case 'notification':
                $model = new Notification();
                $modelItem = new NotificationUser();
                break;
            case 'ask':
                $model = new Ask;
                $modelItem = new AskUser();
                break;
        }

        $res = [
            "model" => $model,
            "modelItem" => $modelItem,
        ];

        return $res;
    }
}
