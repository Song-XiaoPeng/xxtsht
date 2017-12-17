<?php
namespace app\api\controller\v1\task;
use \think\Log;
use think\Config;

//自动任务处理
class AautoMatic {
    //同步微信用户列表 url: http://kf.lyfz.net/api/v1/task/AautoMatic/syncWxUserList
    public function syncWxUserList(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->syncWxUserList();
    }

    //同步获取更新微信用户详情信息 url: http://kf.lyfz.net/api/v1/task/AautoMatic/syncWxUserDetails type 1拉取用户详情信息 2更新用户详情信息
    public function syncWxUserDetails(){
        $type = input('get.type');

        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->syncWxUserDetails($type);
    }

    //群发消息任务处理 url: http://kf.lyfz.net/api/v1/task/AautoMatic/massNews
    public function massNews(){
        $type = input('get.type');

        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->massNews();
    }

    //关闭超过24小时排队会话 url: http://kf.lyfz.net/api/v1/task/AautoMatic/colseQueuingSession
    public function colseQueuingSession(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->colseQueuingSession();
    }
}