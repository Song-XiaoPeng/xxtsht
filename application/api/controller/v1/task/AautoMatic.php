<?php
namespace app\api\controller\v1\task;
use \think\Log;
use think\Config;

//自动任务处理
class AautoMatic {
    //同步微信用户列表 url: http://kf.lyfz.net/api/v1/task/AautoMatic/syncWxUserList
    public function syncWxUserList(){
        return \think\Loader::model('AautoMaticModel','logic\v1\task')->syncWxUserList();
    }
}