<?php
namespace MvcBuilder\system\tp51;
use think\Controller;
use think\exception\HttpResponseException;
use think\Response;

class BaseController extends Controller {

    /**
     * 重写 返回封装后的API数据到客户端
     * @access protected
     * @param  mixed     $data 要返回的数据
     * @param  integer   $code 返回的code
     * @param  mixed     $msg 提示信息
     * @param  string    $type 返回数据格式
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function result($data,$code = 0, $msg = '', $type = '', array $header = [])
    {
        $count = $data['count'];
        unset($data['count']);
        $result = [
            'code' => $code,
            'count' => $count,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data,
        ];

        $type     = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

}