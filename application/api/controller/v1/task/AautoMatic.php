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

    //关闭超过2天的排队会话 url: http://kf.lyfz.net/api/v1/task/AautoMatic/colseQueuingSession
    public function colseQueuingSession(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->colseQueuingSession();
    }

    //关闭超时等待中会话 url: http://kf.lyfz.net/api/v1/task/AautoMatic/colseWaitingSession
    public function colseWaitingSession(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->colseWaitingSession();
    }

    //关闭无效会话中数据 url: http://kf.lyfz.net/api/v1/task/AautoMatic/closeInvalidSession
    public function closeInvalidSession(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->closeInvalidSession();
    }

    //删除多余的二维码 url: http://kf.lyfz.net/api/v1/task/AautoMatic/delQrCodeFile
    public function delQrCodeFile(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->delQrCodeFile();
    }

    //回收线索客户 url: http://kf.lyfz.net/api/v1/task/AautoMatic/recoveryClueCustomer
    public function recoveryClueCustomer(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->recoveryClueCustomer();
    }

    //回收意向客户 url: http://kf.lyfz.net/api/v1/task/AautoMatic/recoveryIntentionCustomer
    public function recoveryIntentionCustomer(){
        return \think\Loader::model('AautoMaticLogic','logic\v1\task')->recoveryIntentionCustomer();
    }

    //发送手机验证码
    public function sendShortMessage()
    {
        return \think\Loader::model('ShortMessageLogic','logic\v1\short_message')->sendShortMessage();
    }
}