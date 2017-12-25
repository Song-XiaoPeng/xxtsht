<?php
namespace app\home\controller;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;
use think\Session;

class Redenvelopes{
    // 领取红包首页
    public function index(){
        $code = input('get.code');

        Session::set('jump_url', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

        $str = base64_decode($code);

        $data = json_decode($str,true);

        $arr = Db::name('red_envelopes')->where(['activity_id'=>$data['activity_id']])->cache(true,30)->find();

        $token_info = Common::getRefreshToken($arr['appid'], $arr['company_id']);
        if ($token_info['meta']['code'] == 200) {
            $refresh_token = $token_info['body']['refresh_token'];
        } else {
            return $token_info;
        }
          
        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $oauth = $openPlatform->createAuthorizerApplication($arr['appid'],$refresh_token)->oauth;

        if(empty(Session::get('wx_user_info'))){
            $response = $oauth->scopes(['snsapi_userinfo'])->redirect();
            $response->send();
        }

        $is_receive = Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->value('is_receive');
        if($is_receive == 1 || $is_receive == 2){
            return view('receive', ['title'=>$arr['activity_name']]);
        }

        return view('index', ['title'=>$arr['activity_name'],'code'=>$code,'appid'=>$arr['appid'],'company_id'=>$arr['company_id']]);
    }

    // 领取红包
    public function receive(){
        $input_data = input('post.');

        $code = $input_data['code'];
        $wx_user_info = Session::get('wx_user_info');

        $str = base64_decode($code);

        $data = json_decode($str,true);

        $arr = Db::name('red_envelopes')->where(['activity_id'=>$data['activity_id']])->cache(true,10)->find();

        $auth_info_res = Db::name('openweixin_authinfo')->where(['appid'=>$arr['appid'],'company_id'=>$arr['company_id']])->cache(true,30)->find();
        if(!$auth_info_res){
            return msg(3009,'无法获取公众号授权信息');
        }

        $cert_path = Db::name('resources')->where(['resources_id'=>$auth_info_res['cert_path']])->value('resources_route');
        if(!$cert_path){
            return msg(3003,'支付证书不全');
        }

        $key_path = Db::name('resources')->where(['resources_id'=>$auth_info_res['key_path']])->value('resources_route');
        if(!$key_path){
            return msg(3003,'支付证书不全');
        }

        //判断是否关注
        if ($arr['is_follow'] == 1) {
            $wx_user_res = Db::name('wx_user')
            ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['openid'=>$wx_user_info['original']['openid'], 'appid'=>$arr['appid'], 'subscribe'=>1])
            ->find();
            if(!$wx_user_res){
                return msg(3001, '请先关注公众号', ['jump_url'=>'http://'.$_SERVER['HTTP_HOST'].'/home/Redenvelopes/qrcode?appid='.$arr['appid']]);
            }    
        } 

        //判断是否分享
        if ($arr['is_share'] == 1) {
            $is_share = Db::name('red_envelopes_share')->where(['openid'=>$wx_user_info['original']['openid'],'appid'=>$arr['appid']])->find();
            if($is_share){
                return msg(3002, '请先分享');
            }
        }

        //判断是否已领取
        $is_receive = Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->value('is_receive');
        if($is_receive == 1 || $is_receive == 2){
            return msg(3003, '已领取红包');
        }

        // 锁定操作
        Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->update(['is_receive'=>2]);

        // 判断是随机金额或者固定金额
        if ($arr['amount_type'] == 1) {
            // 派发固定金额操作
            $receive_amount = $arr['amount'];
        } else if ($arr['amount_type'] == 2) {
            // 派发随机金额操作
            $receive_amount = randFloat($arr['amount_start'], $arr['amount_end']);
        }

        $wx_auth_info = wxOptions();
        $pay_auth_info = [
            'payment' => [
                'merchant_id'        => $auth_info_res['merchant_id'],
                'key'                => $auth_info_res['pay_key'],
                'cert_path'          => '..'.$cert_path,
                'key_path'           => '..'.$key_path,
            ],
            'app_id' => $arr['appid']
        ];

        $wxauth = array_merge($wx_auth_info,$pay_auth_info);

        // 调用微信api派送金额
        try {
            $token_info = Common::getRefreshToken($arr['appid'], $arr['company_id']);
            if ($token_info['meta']['code'] == 200) {
                $refresh_token = $token_info['body']['refresh_token'];
            } else {
                return $token_info;
            }

            $app = new Application($wxauth);
            $openPlatform = $app->open_platform;
            $lucky_money = $openPlatform->createAuthorizerApplication($arr['appid'], $refresh_token)->lucky_money;

            $luckyMoneyData = [
                'mch_billno'       => short_md5($data['activity_id'].$data['red_envelopes_id']),
                'send_name'        => $arr['activity_name'],
                're_openid'        => $wx_user_info['original']['openid'],
                'total_amount'     => floatval($receive_amount) * 100,
                'wishing'          => '祝福语',
                'act_name'         => $arr['activity_name'],
                'remark'           => '测试备注'
            ];
            
            $result = $lucky_money->sendNormal($luckyMoneyData)->toArray();
        } catch (\Exception $e) {
            Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->update(['is_receive'=>-1]);

            return msg(3001, $e->getMessage());
        }

        if($result['result_code'] != 'SUCCESS'){
            Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->update(['is_receive'=>-1]);
            return msg(3002, $result['return_msg']);
        }

        Db::name('red_envelopes_id')
        ->where(['red_envelopes_id'=>$data['red_envelopes_id']])
        ->update([
            'is_receive' => 1,
            'wx_nickname' => $wx_user_info['original']['nickname'],
            'wx_portrait' => $wx_user_info['avatar'],
            'receive_time' => date('Y-m-d H:i:s'),
            'receive_amount' => $receive_amount,
            'openid' => $wx_user_info['original']['openid']
        ]);

        $already_amount = Db::name('red_envelopes')
        ->where(['activity_id'=>$data['activity_id']])
        ->value('already_amount');

        $already_amount = $already_amount + $receive_amount;

        Db::name('red_envelopes')
        ->where(['activity_id'=>$data['activity_id']])
        ->update(['already_amount'=>$already_amount]);

        return msg(200, '领取成功返回消息列表查看');
    }

    // 设为已分享
    public function setShare(){
        $data = input('post.');
    
        Db::name('red_envelopes_share')->insert([
            'appid' => $data['appid'],
            'openid' => $data['openid'],
            'activity_id' => $data['activity_id'],
            'add_time' => date('Y-m-d H:i:s')
        ]);
    }

    // 显示二维码
    public function qrcode(){
        $appid = input('get.appid');

        $qrcode_url = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->cache(true,60)->value('qrcode_url');

        $qrcode_url = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getWxUrlImg?url='.$qrcode_url;

        return view('qrcode', ['qrcode_url'=>$qrcode_url]);
    }

    //获取jssdk授权数据
    public function getJsSdk(){
        $data = input('get.');

        $token_info = Common::getRefreshToken($data['appid'], $data['company_id']);
        if ($token_info['meta']['code'] == 200) {
            $refresh_token = $token_info['body']['refresh_token'];
        } else {
            return $token_info;
        }

        try {
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $js = $openPlatform->createAuthorizerApplication($data['appid'],$refresh_token)->js;

            if(!empty($data['url'])){
                $js->setUrl($data['url']);
            }

            return msg(200,'success', json_decode($js->config(array('checkJsApi', 'openLocation', 'getLocation', 'onMenuShareTimeline', 'onMenuShareAppMessage'), false)));
        } catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
    }
}
