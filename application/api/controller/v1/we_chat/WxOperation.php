<?php
namespace app\api\controller\v1\we_chat;
use app\api\controller\common\AuthController;

//微信后台操作业务处理
class WxOperation extends AuthController{
    public function test(){
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->test();
    }
}