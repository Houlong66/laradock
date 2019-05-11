<?php
if (! function_exists('RouteUri')) {
    /**
     * 根据路由名称返回原始的路由地址
     *
     * @param  string  $name
     * @return string
     *
     * @throws \RuntimeException
     */
    function RouteUri($name)
    {
        return app('router')->getRoutes()->getByName($name)->uri();
    }
}

if (! function_exists('ResponseJson')) {
    /**
     * 返回json数据
     *
     * @param  array  $data  返回数据
     * @param  string  $errmsg  返回的错误信息
     * @param  string  $hintmsg  返回的提示信息
     * @return json  errcode：0成功，1失败
     */
    function ResponseJson($data = [], $errmsg = '', $hintmsg = '')
    {
        return response()->json([
            'data'  => $data,
            'errcode'  => $errmsg == '' ? 0 : 1,
            'errmsg' => $errmsg,
            'hintmsg' => $hintmsg
        ]);
    }
}

if(! function_exists('hasRole')){
    /**
     * 判断是否存在权限，不存在直接return
     * @param int $role_id
     * @param array $role_arr
     * @param string $msg 报错信息
     */
    function hasRole($role_id, $role_arr, $msg){
        if (!in_array($role_id, $role_arr)){
            header('Content-type: application/json');
            echo json_encode([
                'data' => "",
                'errcode' => 1,
                'errmsg' => $msg,
                'hintmsg' => ""
            ]);
            exit;
        }
    } 
}