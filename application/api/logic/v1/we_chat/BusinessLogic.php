<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use EasyWeChat\OpenPlatform\Guard;
use think\Log;
use app\api\common\Common;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Material;
use EasyWeChat\Message\Text;
use app\api\logic\v1\trajectory\InteractiveLogic;

class BusinessLogic extends Model
{
    private $default_message = '';

    private $release_appid = 'wx570bc396a51b8ff8';

    //微信授权事件处理
    public function authCallback()
    {
        $app = new Application(wxOptions());

        $openPlatform = $app->open_platform;
        $server = $openPlatform->server;
        $server->setMessageHandler(function ($event) use ($openPlatform) {
            // 事件类型常量定义在 \EasyWeChat\OpenPlatform\Guard 类里
            switch ($event->InfoType) {
                case Guard::EVENT_AUTHORIZED: // 授权成功
                    // $data['company_id'] = '51454009d703c86c91353f61011ecf2f';

                    // $authorization_info = $openPlatform->getAuthorizationInfo($event->AuthorizationCode)['authorization_info'];

                    // $authorizer_info = $openPlatform->getAuthorizerInfo($authorization_info['authorizer_appid'])['authorizer_info'];

                    // $auth_info = Db::name('openweixin_authinfo')->where(['appid'=>$authorization_info['authorizer_appid']])->find();
                    // if($auth_info){
                    //     if($auth_info['company_id'] != $data['company_id']){
                    //         return;
                    //     }else{
                    //         return;
                    //     }
                    // }

                    // $auth_id = Db::name('openweixin_authinfo')->insertGetId([
                    //     'appid' => $authorization_info['authorizer_appid'],
                    //     'access_token' => $authorization_info['authorizer_access_token'],
                    //     'refresh_token' => $authorization_info['authorizer_refresh_token'],
                    //     'refresh_time' => strtotime(date('Y-m-d H:i:s')),
                    //     'type' => 1,
                    //     'company_id' => $data['company_id'],
                    //     'nick_name' => $authorizer_info['nick_name'],
                    //     'logo' => $authorizer_info['head_img'],
                    //     'qrcode_url' => $authorizer_info['qrcode_url'],
                    //     'principal_name' => $authorizer_info['principal_name'],
                    //     'account_number' => $authorizer_info['user_name'],
                    //     'alias' => $authorizer_info['alias']
                    // ]);

                    // Db::name('wx_api_count')->insert([
                    //     'auth_id' => $auth_id,
                    //     'company_id' => $data['company_id']
                    // ]);
                    break;
                case Guard::EVENT_UPDATE_AUTHORIZED: // 更新授权
                    // 更新数据库操作等...
                    break;
                case Guard::EVENT_UNAUTHORIZED: // 授权取消
                    // 更新数据库操作等...
                    break;
            }
        });
        $response = $server->serve();

        return $response->send();
    }

    //获取第三方公众号授权链接
    public function getAuthUrl($company_id)
    {
        if (empty($company_id)) {
            return msg(3001, '缺少company_id参数');
        }

        $app = new Application(wxOptions());

        $openPlatform = $app->open_platform;
        $openPlatform->pre_auth->getCode();

        // 直接跳转
        $response = $openPlatform->pre_auth->redirect("http://kf.lyfz.net/api/v1/we_chat/Business/authCallbackPage?company_id=$company_id");

        // 获取跳转的 URL
        $url = $response->getTargetUrl();
        echo "<script>window.location.href='$url'</script>";
    }

    //授权成功跳转页面
    public function authCallbackPage($data)
    {
        //判断是否达到最大授权数量
        $max_auth_wx_num = Db::name('company')->where(['company_id' => $data['company_id']])->value('max_auth_wx_num');
        if (empty($max_auth_wx_num)) {
            return msg(3003, '商户未在系统注册拒绝接入！');
        }

        $count = Db::name('openweixin_authinfo')->where(['company_id' => $data['company_id']])->count();
        if ($count >= $max_auth_wx_num) {
            return msg(3004, '商户公众号或小程序已达到最大接入数量');
        }

        $app = new Application(wxOptions());

        $openPlatform = $app->open_platform;

        $authorization_info = $openPlatform->getAuthorizationInfo()['authorization_info'];

        $authorizer_info = $openPlatform->getAuthorizerInfo($authorization_info['authorizer_appid'])['authorizer_info'];

        $auth_info = Db::name('openweixin_authinfo')->where(['appid' => $authorization_info['authorizer_appid']])->find();
        if ($auth_info) {
            if ($auth_info['company_id'] != $data['company_id']) {
                $company_id = $auth_info['company_id'];
                return msg(3001, '绑定失败,此公众平台或小程序已绑定company_id为:' . $company_id . '的账号,请先解绑原账号!');
            } else {
                return msg(3002, "此公众平台或小程序已绑定完成，请勿重复绑定!");
            }
        }

        $auth_id = Db::name('openweixin_authinfo')->insertGetId([
            'appid' => $authorization_info['authorizer_appid'],
            'access_token' => $authorization_info['authorizer_access_token'],
            'refresh_token' => $authorization_info['authorizer_refresh_token'],
            'refresh_time' => strtotime(date('Y-m-d H:i:s')),
            'type' => $authorizer_info['service_type_info']['id'] == 0 ? 2 : 1,
            'company_id' => $data['company_id'],
            'nick_name' => $authorizer_info['nick_name'],
            'logo' => $authorizer_info['head_img'],
            'qrcode_url' => $authorizer_info['qrcode_url'],
            'principal_name' => $authorizer_info['principal_name'],
            'account_number' => $authorizer_info['user_name'],
            'alias' => $authorizer_info['alias']
        ]);

        Db::name('wx_api_count')->insert([
            'auth_id' => $auth_id,
            'company_id' => $data['company_id']
        ]);

        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>授权成功</title><style>.box{height:250px;text-align:center}.btn{text-align:center}#btn{background-color:#0c6;color:#fff;font-size:18px;border:0;padding:5px 20px;border-radius:5px;cursor:pointer}#btn:hover{background-color:#0c0}</style></head><body><div class="box"><img src="http://kf.lyfz.net/static/images/auth_success.png" alt=""></div><div class="btn"><button id="btn">关闭</button></div><script>const btn = document.getElementById("btn");btn.addEventListener("click", () => {window.close();
        }, false)</script></body></html>';
    }

    //微信公众号事件响应处理
    public function messageEvent($data)
    {
        $appid = $data['appid'];
        $openid = $data['openid'];

        $token_info = Common::getRefreshToken($appid);
        if ($token_info['meta']['code'] == 200) {
            $refresh_token = $token_info['body']['refresh_token'];
        } else {
            return $token_info;
        }

        $apc = new Application(wxOptions());
        $openPlatform = $apc->open_platform;
        $app = $openPlatform->createAuthorizerApplication($appid, $refresh_token);
        $server = $app->server;

        Common::setWxUserLastTime($appid, $openid);

        $message = $server->getMessage();
        switch ($message['MsgType']) {
            case 'event':
                //判断是否小程序用户
                if ($message['Event'] == 'user_enter_tempsession' && $message['SessionFrom'] == 'wxapp') {
                    $this->addWxUserInfo($appid, $openid);
                }

                if ($appid == $this->release_appid) {
                    $returnMessage = $message['Event'] . 'from_callback';

                    $app = new Application(wxOptions());
                    $staff = $openPlatform->createAuthorizerApplication($appid, $refresh_token)->staff;
                    $message_text = new Text(['content' => $returnMessage]);
                    $staff->message($message_text)->to($openid)->send();
                    exit;
                } else {
                    $returnMessage = $this->clickEvent($appid, $openid, $message);
                }

                break;
            case 'text':
                if ($appid == $this->release_appid) {
                    if ($message['Content'] == 'TESTCOMPONENT_MSG_TYPE_TEXT') {
                        $returnMessage = 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
                    } else if (substr($message['Content'], 0, 15) == 'QUERY_AUTH_CODE') {
                        $returnMessage = substr($message['Content'], 16) . '_from_api';

                        $app = new Application(wxOptions());
                        $staff = $openPlatform->createAuthorizerApplication($appid, $refresh_token)->staff;
                        $message_text = new Text(['content' => $returnMessage]);
                        $staff->message($message_text)->to($openid)->send();
                        exit;
                    }
                } else {
                    $returnMessage = $this->textEvent($appid, $openid, $message['Content']);
                }

                break;
            case 'image':
                $returnMessage = $this->imgEvent($appid, $openid, $message);
                break;
            case 'voice':
                $returnMessage = $this->voiceEvent($appid, $openid, $message);
                break;
            case 'video':
                $returnMessage = $this->videoEvent($appid, $openid, $message);
                break;
            case 'location':
                $returnMessage = $this->locationEvent($appid, $openid, $message);
                break;
            case 'link':
                $returnMessage = $this->linkEvent($appid, $openid, $message['Url']);
                break;
            default:
                $returnMessage = '您好有什么需要帮助吗？';
                break;
        }

        // Log::record('收到微信数据');
        // Log::record(json_encode($message));

        $server->setMessageHandler(function ($message) use ($returnMessage) {
            return $returnMessage;
        });

        $response = $server->serve();
        return $response->send();
    }

    /**
     * 视频消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息数据
     * @return code 200->成功
     */
    private function videoEvent($appid, $openid, $message_arr)
    {
        //判断是否存在客服会话
        $session_res = $this->getSession($appid, $openid);
        if ($session_res) {
            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                4,
                2,
                ['media_id' => $message_arr['MediaId']]
            );

            if ($add_res) {
                return '';
            } else {
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 语音消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息数据
     * @return code 200->成功
     */
    private function voiceEvent($appid, $openid, $message_arr)
    {
        //判断是否存在客服会话
        $session_res = $this->getSession($appid, $openid);
        if ($session_res) {
            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                3,
                2,
                ['media_id' => $message_arr['MediaId']]
            );

            if ($add_res) {
                return '';
            } else {
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 文本消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param key_word 关键词
     * @return code 200->成功
     */
    private function textEvent($appid, $openid, $key_word)
    {
        $this->createSession($appid, $openid, 'other');

        //判断是否存在客服会话
        $session_res = $this->getSession($appid, $openid);
        if ($session_res) {
            if ($session_res['session_state'] == 2) {//群聊
                $opercode = 4;
            } else {
                $opercode = 2;
            }
            Common::addMessagge($appid, $openid, $session_res['session_id'], $session_res['customer_service_id'], $session_res['uid'], 1, $opercode, ['text' => $key_word]);

            $map['appid'] = $appid;
            $map1['pattern'] = 2;
            $map['key_word'] = array('like', "%$key_word%");
            $reply_text = Db::name('message_rule')->where($map)->where($map1)->cache(true, 60)->value('reply_text');
            if (!$reply_text) {
                $map2['pattern'] = 1;
                $reply_text = Db::name('message_rule')->where($map)->where($map2)->value('reply_text');
            }

            return empty($reply_text) == true ? '' : emoji_decode($reply_text);
        }
    }

    /**
     * 位置消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息内容
     * @return code 200->成功
     */
    private function locationEvent($appid, $openid, $message_arr = '')
    {
        //判断是否存在客服会话
        $session_res = $this->getSession($appid, $openid);
        if ($session_res) {
            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                5,
                2,
                ['map_scale' => $message_arr['Scale'], 'map_label' => $message_arr['Label'], 'map_img' => '', 'lng' => $message_arr['Location_Y'], 'lat' => $message_arr['Location_X']]
            );

            if ($add_res) {
                return '';
            } else {
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 记录微信用户公众号点击进入次数
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     */
    private function setIntoCount($appid, $openid)
    {
        $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 60)->value('company_id');
        if (empty($company_id)) {
            return false;
        }

        $update_res = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->setInc('get_into_count');

        if ($update_res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 记录微信用户地理位置
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param lng 用户所在经度
     * @param lat 用户所在纬度
     */
    private function setWxUserPosition($appid, $openid, $lng, $lat, $precision)
    {
        $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 60)->value('company_id');
        if (empty($company_id)) {
            return false;
        }

        $this->setIntoCount($appid, $openid);

        $update_res = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->update(['lng' => $lng, 'lat' => $lat, 'precision' => $precision]);

        $update_res2 = Db::name('geographical_position')->insert([
            'geographical_position_id' => md5(uniqid()),
            'appid' => $appid,
            'openid' => $openid,
            'lng' => $lng,
            'lat' => $lat,
            'precision' => $precision,
            'company_id' => $company_id,
            'establish_time' => date('Y-m-d H:i:s'),
        ]);

        if ($update_res !== false && $update_res2 !== false) {
            // $this->createSession($appid,$openid,'other');
            return true;
        } else {
            return false;
        }
    }

    /**
     * 链接消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param key_word 关键词
     * @return code 200->成功
     */
    private function linkEvent($appid, $openid, $key_word)
    {
        //判断是否存在客服会话
        $session_res = $this->getSession($appid, $openid);
        if ($session_res) {
            $client = new \GuzzleHttp\Client();
            $request_res = $client->request('GET', $key_word, [
                'timeout' => 3,
            ]);
            $html = $request_res->getBody();

            if (!empty($html)) {
                $page_title = get_title($html);
            } else {
                $page_title = '无法获取标题';
            }

            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                6,
                2,
                ['text' => $key_word, 'page_title' => $page_title, 'page_desc' => '暂无页面描述']
            );

            if ($add_res) {
                return '';
            } else {
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 图片消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息数据
     * @return code 200->成功
     */
    private function imgEvent($appid, $openid, $message_arr)
    {
        //判断是否存在客服会话
        $session_res = $this->getSession($appid, $openid);
        if (!$session_res) {
            return '';
        }

        $add_res = Common::addMessagge(
            $appid,
            $openid,
            $session_res['session_id'],
            $session_res['customer_service_id'],
            $session_res['uid'],
            2,
            2,
            ['file_url' => $message_arr['PicUrl'], 'media_id' => $message_arr['MediaId']]
        );

        if ($add_res) {
            return '';
        } else {
            return '系统繁忙请稍候重试!';
        }
    }

    /**
     * 获取用户会话
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @return code 200->成功
     */
    private function getSession($appid, $openid)
    {
        $res = Db::name('message_session')
            ->partition('', '', ['type' => 'md5', 'num' => config('separate')['message_session']])
            ->where(['appid' => $appid, 'customer_wx_openid' => $openid, 'state' => array('in', [0, 1, 2, 3])])
            ->find();

        if ($res) {
            return [
                'session_id' => $res['session_id'],
                'session_state' => $res['state'],
                'uid' => empty($res['uid']) == true ? '' : $res['uid'],
                'customer_service_id' => empty($res['customer_service_id']) == true ? '' : $res['uid'],
            ];
        } else {
            return false;
        }
    }

    /**
     * 事件消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param message 消息对象
     * @return code 200->成功
     */
    private function clickEvent($appid, $openid, $message)
    {
        //记录用户操作轨迹数据
        if (!empty($message['EventKey'])) {
            InteractiveLogic::recordInteractiveEvent([
                'appid' => $appid,
                'openid' => $openid,
                'event_type' => $message['Event'],
                'event_key' => $message['EventKey']
            ]);
        }

        if (!empty($message['EventKey'])) {
            $event_arr = explode('_', $message['EventKey']);
            switch ($event_arr[0]) {
                case 'kf':
                    $id = empty($event_arr[2]) == true ? '' : $event_arr[2];

                    return $this->createSession($appid, $openid, $event_arr[1], $id);
                    break;
                case 'qrscene':
                    // 兼容数据处理
                    if ($event_arr[1] == 'qrscene') {
                        $event_arr[1] = $event_arr[2];
                    }

                    return $this->qrcodeEvent($appid, $openid, $event_arr[1]);
                    break;
                default:
                    return $this->default_message;
                    break;
            }
        } else if ($message['Event'] == 'LOCATION') {
            $this->setWxUserPosition($appid, $openid, $message['Longitude'], $message['Latitude'], $message['Precision']);
        } else if ($message['Event'] == 'unsubscribe') {
            $this->unSubScribe($appid, $openid);
        } else if ($message['Event'] == 'subscribe') {
            $this->subScribe($appid, $openid);
        } else {
            return '';
        }
    }

    /**
     * 订阅事件处理
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @return code 200->成功
     */
    private function subScribe($appid, $openid)
    {
        $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 60)->value('company_id');
        if (empty($company_id)) {
            return false;
        }

        $update_res = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->update(['subscribe' => 1]);

        if ($update_res) {
            return true;
        } else {
            $this->addWxUserInfo($appid, $openid);
            return false;
        }
    }

    /**
     * 派送红包处理
     */
    private function receiveRedEnvelopes($data)
    {
        $appid = $data['appid'];
        $openid = $data['openid'];
        $activity_id = $data['activity_id'];
        $red_envelopes_id = $data['red_envelopes_id'];
        $receive_amount = $data['receive_amount'];
        $wx_nickname = $data['wx_nickname'];
        $wx_portrait = $data['wx_portrait'];
        $merchant_id = $data['merchant_id'];
        $pay_key = $data['pay_key'];
        $cert_path = $data['cert_path'];
        $key_path = $data['key_path'];
        $company_id = $data['company_id'];
        $cache_key = $data['cache_key'];

        //判断是否已领取
        $is_receive = Db::name('red_envelopes_id')->where(['red_envelopes_id' => $red_envelopes_id])->value('is_receive');
        if ($is_receive == 1 || $is_receive == 2) {
            return;
        }

        // 锁定操作
        Db::name('red_envelopes_id')->where(['red_envelopes_id' => $red_envelopes_id])->update(['is_receive' => 2]);

        $wx_auth_info = wxOptions();
        $pay_auth_info = [
            'payment' => [
                'merchant_id' => $merchant_id,
                'key' => $pay_key,
                'cert_path' => '..' . $cert_path,
                'key_path' => '..' . $key_path,
            ],
            'app_id' => $appid
        ];

        $wxauth = array_merge($wx_auth_info, $pay_auth_info);

        // 调用微信api派送金额
        try {
            $token_info = Common::getRefreshToken($appid, $company_id);
            if ($token_info['meta']['code'] == 200) {
                $refresh_token = $token_info['body']['refresh_token'];
            } else {
                return;
            }

            $app = new Application($wxauth);
            $openPlatform = $app->open_platform;
            $lucky_money = $openPlatform->createAuthorizerApplication($appid, $refresh_token)->lucky_money;

            $luckyMoneyData = [
                'mch_billno' => short_md5($activity_id . $red_envelopes_id),
                'send_name' => '红包',
                're_openid' => $openid,
                'total_amount' => floatval($receive_amount) * 100,
                'wishing' => '谢谢领取',
                'act_name' => '红包领取',
                'remark' => '红包'
            ];

            $result = $lucky_money->sendNormal($luckyMoneyData)->toArray();
        } catch (\Exception $e) {
            Db::name('red_envelopes_id')->where(['red_envelopes_id' => $red_envelopes_id])->update(['is_receive' => -1]);
            return;
        }

        if ($result['result_code'] != 'SUCCESS') {
            Db::name('red_envelopes_id')->where(['red_envelopes_id' => $red_envelopes_id])->update(['is_receive' => -1]);
            return;
        }

        Db::name('red_envelopes_id')
            ->where(['red_envelopes_id' => $red_envelopes_id])
            ->update([
                'is_receive' => 1,
                'wx_nickname' => $wx_nickname,
                'wx_portrait' => $wx_portrait,
                'receive_time' => date('Y-m-d H:i:s'),
                'receive_amount' => $receive_amount,
                'openid' => $openid
            ]);

        $already_amount = Db::name('red_envelopes')
            ->where(['activity_id' => $activity_id])
            ->value('already_amount');

        $already_amount = $already_amount + $receive_amount;

        Db::name('red_envelopes')
            ->where(['activity_id' => $activity_id])
            ->update(['already_amount' => $already_amount]);

        cache($cache_key, NULL);
    }

    /**
     * 取消订阅事件处理
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @return code 200->成功
     */
    private function unSubScribe($appid, $openid)
    {
        $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 3600)->value('company_id');
        if (empty($company_id)) {
            return false;
        }

        $qrcode_id = Db::name('wx_user')
            ->partition([], "", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->value('qrcode_id');

        if(!empty($qrcode_id)){
            Db::name('extension_qrcode')->where(['qrcode_id'=>$qrcode_id,'appid'=>$appid])->setInc('canel_attention');
        }

        $update_res = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->update(['subscribe' => 0]);

        if ($update_res !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 二维码来源用户处理
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param qrcode_id 二维码id
     * @return code 200->成功
     */
    private function qrcodeEvent($appid, $openid, $qrcode_id)
    {
        $info = $this->addWxUserInfo($appid, $openid, $qrcode_id);
        if (!$info['is_update']) {
            Db::name('extension_qrcode')->where(['qrcode_id' => $qrcode_id])->setInc('attention');
        }

        //判断是否可领取红包
        $cache_key = 'RedEnvelopes' . '_' . $appid . '_' . $openid;
        if (cache($cache_key)) {
            $this->receiveRedEnvelopes(cache($cache_key));
        }

        $qrcode_res = Db::name('extension_qrcode')->where(['qrcode_id' => $qrcode_id, 'is_del' => -1])->cache(true, 30)->find();
        if (!$qrcode_res) {
            return '欢迎关注！';
        }

        if ($qrcode_res['reception_type'] == 1) {
            $uid = Db::name('customer_service')->where(['customer_service_id' => $qrcode_res['customer_service_id']])->value('uid');

            $this->createSession($appid, $openid, 'user', $uid);
        }

        if ($qrcode_res['reception_type'] == 2) {
            $this->createSession($appid, $openid, 'group', $qrcode_res['customer_service_group_id']);
        }

        if ($qrcode_res['reception_type'] == 3) {
            $this->createSession($appid, $openid, 'other');
        }

        // if (count(json_decode($qrcode_res['label'], true)) != 0) {
        //     try {
        //         $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->value('company_id');
        //         $label_list = json_decode($qrcode_res['label'], true);

        //         foreach ($label_list as $label_id) {
        //             $WxOperationLogic = new WxOperationLogic();
        //             $data['company_id'] = $company_id;
        //             $data['appid'] = $appid;
        //             $data['openid'] = $openid;
        //             $data['label_id'] = $label_id;
        //             $WxOperationLogic->setWxUserLabel($data);
        //         }
        //     } catch (\Exception $e) {
        //     }
        // }

        $qrcode_res['openid'] = $openid;

        return $this->authReply($qrcode_res);
    }

    /**
     * 自动回复内容
     * @param appid 公众号或小程序appid
     * @param reply_text 回复文本内容
     * @param media_id 回复微信媒体id
     * @param resources_id 资源id
     * @param reply_type 回复类型 1文本内容 2图片 3微信图文信息
     * @return code 200->成功
     */
    public function authReply($data)
    {
        $appid = $data['appid'];
        $openid = $data['openid'];
        $reply_text = empty($data['reply_text']) == true ? '' : $data['reply_text'];
        $media_id = empty($data['media_id']) == true ? '' : $data['media_id'];
        $resources_id = empty($data['resources_id']) == true ? '' : $data['resources_id'];
        $reply_type = $data['reply_type'];

        switch ($reply_type) {
            case 1:
                $type = 1;
                $message_data = ['content'=>$reply_text];
                break;
            case 2:
                $type = 2;
                $message_data = ['resources_id'=>$resources_id];
                break;
            case 3:
                $type = 3;
                $message_data = ['media_id'=>$media_id];
                break;
            default:
                return '欢迎关注';
        }

        Common::sendWxMessage([
            'appid' => $appid,
            'openid' => $openid,
            'type' => $type,
            'message_data' => $message_data
        ]);
    }

    /**
     * 创建客服会话
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param type 分配类型 user->指定到具体的客服 group->指定到具体的客服分组 other->不指定客服
     * @param id 分配的客服id或客服分组id
     * @param is_code 是否返回code码
     * @return code 200->成功
     */
    public function createSession($appid, $openid, $type, $id = '', $is_code = false, $is_push = true)
    {
        $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 120)->value('company_id');
        if (empty($company_id)) {
            return '公众号未绑定第三方平台';
        }
        //匹配是否存在正在会话中数据
        $session_res = Db::name('message_session')
            ->partition('', '', ['type' => 'md5', 'num' => config('separate')['message_session']])
            ->where(['appid' => $appid, 'customer_wx_openid' => $openid, 'state' => array('in', [0, 1, 2, 3])])
            ->find();

        if ($session_res) {
            if ($session_res['state'] == 0 || $session_res['state'] == 1 || $session_res['state'] == 2) {
                $customer_service_name = Db::name('customer_service')->where(['customer_service_id' => $session_res['customer_service_id']])->value('name');
            }

            switch ($session_res['state']) {
                case 0:
                    if ($is_code) {
                        return msg(3001, '已被' . $customer_service_name . '客服接入');
                    } else {
                        return '正在为您接入客服' . $customer_service_name . '请稍等！';
                    }

                    break;
                case 1:
                    if ($is_code) {
                        return msg(3002, '已被' . $customer_service_name . '客服接入');
                    } else {
                        return '客服' . $customer_service_name . '正在为您服务！';
                    }

                    break;
                case 2:
                    if ($is_code) {
                        return msg(3002, '群聊已被' . $customer_service_name . '客服接入');
                    } else {
                        return '客服' . $customer_service_name . '正在为您服务！';
                    }

                    break;
                case 3:
                    if ($is_code) {
                        return msg(3003, '此客户正在排队会话中');
                    } else {
                        return '正在为您分配客服，请稍等！';
                    }

                    break;
            }
        }

        //判断是否存在专属客服
        $wx_user_res = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->find();
        if ($wx_user_res['customer_service_uid'] != '-1') {
            $id = $wx_user_res['customer_service_uid'];
            $type = 'user';
        }

        switch ($type) {
            case 'user':
                $customer_service_res = Db::name('customer_service')->where(['appid' => $appid, 'uid' => $id])->cache(true, 60)->find();
                if (empty($customer_service_res)) {
                    if ($is_code) {
                        return msg(3001, '暂无可分配的客服');
                    } else {
                        return '暂无可分配的客服！';
                    }
                }

                if ($is_push) {
                    $session_state = 0;
                } else {
                    $session_state = 1;
                }
                break;

            case 'group':
                $list = Db::name('customer_service')->where(['appid' => $appid, 'user_group_id' => $id])->cache(true, 60)->select();
                if (empty($list)) {
                    if ($is_code) {
                        return msg(3002, '暂无可分配的客服');
                    } else {
                        return '暂无可分配的客服！';
                    }
                }

                $customer_service_res = array_rand($list);

                $session_state = 0;
                break;

            case 'other':
                $wx_user_res = Db::name('wx_user')
                    ->partition([], '', ['type' => 'md5', 'num' => config('separate')['wx_user']])
                    ->where(['appid' => $appid, 'openid' => $openid])
                    ->cache(true, 5)
                    ->find();
                if (!empty($wx_user['qrcode_id'])) {
                    $qrcode_res = Db::name('extension_qrcode')->where(['qrcode_id' => $wx_user['qrcode_id']])->cache(true, 60)->find();
                    switch ($qrcode_res['reception_type']) {
                        case 1:
                            $customer_service_res = Db::name('customer_service')->where(['appid' => $appid, 'uid' => $qrcode_res['customer_service_id']])->cache(true, 60)->find();
                            if (empty($customer_service_res)) {
                                if ($is_code) {
                                    return msg(3003, '暂无可分配的客服');
                                } else {
                                    return '暂无可分配的客服！';
                                }
                            }
                            break;
                        case 2:
                            $list = Db::name('customer_service')->where(['appid' => $appid, 'user_group_id' => $qrcode_res['customer_service_group_id']])->cache(true, 60)->select();
                            if (empty($list)) {
                                if ($is_code) {
                                    return msg(3004, '暂无可分配的客服');
                                } else {
                                    return '暂无可分配的客服！';
                                }
                            }

                            $customer_service_res = array_rand($list);
                            break;
                    }

                    $session_state = 0;
                } else {
                    $customer_service_res['customer_service_id'] = null;
                    $customer_service_res['uid'] = null;
                    $customer_service_res['company_id'] = $company_id;
                    $customer_service_res['name'] = '';
                    $session_state = 3;
                }

                break;

            default:
                return $this->default_message;
        }

        $customer_service_id = $customer_service_res['customer_service_id'];
        $customer_service_uid = $customer_service_res['uid'];
        $company_id = $customer_service_res['company_id'];
        $customer_service_name = $customer_service_res['name'];

        $wx_info = $this->addWxUserInfo($appid, $openid, '', $customer_service_uid);
        if (empty($wx_info)) {
            if ($is_code) {
                return msg(3005, '系统繁忙');
            } else {
                return '系统繁忙';
            }
        }

        try {
            $time = date('Y-m-d H:i:s');

            $session_id = md5(uniqid());

            $insert_data = [
                'session_id' => $session_id,
                'customer_service_id' => $customer_service_id,
                'customer_wx_openid' => $openid,
                'add_time' => $time,
                'uid' => $customer_service_uid,
                'appid' => $appid,
                'company_id' => $company_id,
                'customer_wx_nickname' => empty($wx_info['nickname']) == true ? '昵称字符异常' : $wx_info['nickname'],
                'customer_wx_portrait' => $wx_info['headimgurl'],
                'state' => $session_state,
                'wx_user_id' => $wx_info['wx_user_id']
            ];

            $add_res = Db::name('message_session')
                ->partition(['session_id' => $session_id], 'session_id', ['type' => 'md5', 'num' => config('separate')['message_session']])
                ->insert($insert_data);

            if ($customer_service_uid) {
                Db::name('wx_user')
                    ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
                    ->where(['appid' => $appid, 'openid' => $openid])
                    ->update(['customer_service_uid' => $customer_service_uid, 'is_clue' => -1, 'last_time' => $time]);
            }

            $insert_data['session_frequency'] = Db::name('message_session')
                ->partition('', '', ['type' => 'md5', 'num' => config('separate')['message_session']])
                ->where(['customer_wx_openid' => $openid, 'company_id' => $company_id])
                ->cache(true, 60)
                ->count();

            $insert_data['invitation_frequency'] = $wx_user_res['active_count'];

            $nick_name = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 60)->value('nick_name');
            $insert_data['app_name'] = empty($nick_name) == true ? '来源公众号已解绑' : $nick_name;

            $redis = Common::createRedis();

            if (empty($customer_service_uid) == false && $is_push == true) {
                $redis->select(config('redis_business')['waiting_session']);
                $redis->sAdd($customer_service_uid, json_encode($insert_data));
            } else {
                if ($is_push == true) {
                    $redis->select(config('redis_business')['line_up_session']);
                    $redis->sAdd($company_id, json_encode($insert_data));
                }
            }

            if ($add_res) {
                if ($is_code) {
                    return msg(200, 'success', ['session_id' => $session_id, 'insert_data' => $insert_data]);
                } else {
                    return '正在为您接入客服' . $customer_service_name . '请稍等！';
                }
            } else {
                if ($is_code) {
                    return msg(3006, '系统繁忙');
                } else {
                    return '系统繁忙';
                }
            }
        } catch (\Exception $e) {
            return msg(3003, $e->getMessage());
        }
    }

    /**
     * 添加微信用户信息
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param qrcode_id 二维码id
     * @return code 200->成功
     */
    private function addWxUserInfo($appid, $openid, $qrcode_id = '', $uid = '')
    {
        $time = date('Y-m-d H:i:s');

        if ($uid == 0) {
            $uid = -1;
        }

        $authinfo_res = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 60)->find();
        if (empty($authinfo_res)) {
            return;
        }

        $company_id = $authinfo_res['company_id'];

        try {
            $token_info = Common::getRefreshToken($appid, $company_id);
            if ($token_info['meta']['code'] == 200) {
                $refresh_token = $token_info['body']['refresh_token'];
            } else {
                return;
            }

            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;

            $userService = $openPlatform->createAuthorizerApplication($appid, $refresh_token)->user;
            $wx_info = $userService->get($openid);
        } catch (\Exception $e) {
            $wx_info['nickname'] = '小程序客户' . date('YmdHis');
            $wx_info['headimgurl'] = 'http://kf.lyfz.net/static/images/portrait.jpg';
            $wx_info['city'] = '';
            $wx_info['province'] = '';
            $wx_info['language'] = 'zh_CN';
            $wx_info['country'] = '中国';
            $wx_info['sex'] = 0;
            $wx_info['groupid'] = 0;
            $wx_info['remark'] = '';
            $wx_info['tagid_list'] = [];
            $wx_info['unionid'] = null;
            $wx_info['subscribe_time'] = time();
            $wx_info['subscribe'] = 1;
        }

        $wx_user_res = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->find();

        if ($wx_user_res) {
            $update_map = [
                'nickname' => $wx_info['nickname'],
                'portrait' => $wx_info['headimgurl'],
                'gender' => $wx_info['sex'],
                'city' => $wx_info['city'],
                'province' => $wx_info['province'],
                'language' => $wx_info['language'],
                'country' => $wx_info['country'],
                'groupid' => $wx_info['groupid'],
                'subscribe_time' => date("Y-m-d H:i:s", $wx_info['subscribe_time']),
                'desc' => $wx_info['remark'],
                'company_id' => $company_id,
                'tagid_list' => json_encode($wx_info['tagid_list']),
                'unionid' => $wx_info['unionid'],
                'is_sync' => 1,
                'subscribe' => $wx_info['subscribe'],
                'update_time' => $time,
                'customer_service_uid' => empty($uid) == true ? -1 : $uid
            ];

            if (!empty($qrcode_id)) {
                $update_map['qrcode_id'] = $qrcode_id;
            }

            Db::name('wx_user')
                ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
                ->where(['appid' => $appid, 'openid' => $openid])
                ->update($update_map);

            $wx_info['is_update'] = true;
            $wx_info['wx_user_id'] = $wx_user_res['wx_user_id'];

            return $wx_info;
        }

        $wx_user_id = md5(uniqid());

        $wx_user_count = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->insert([
                'wx_user_id' => $wx_user_id,
                'nickname' => $wx_info['nickname'],
                'portrait' => $wx_info['headimgurl'],
                'gender' => $wx_info['sex'],
                'city' => $wx_info['city'],
                'province' => $wx_info['province'],
                'language' => $wx_info['language'],
                'country' => $wx_info['country'],
                'groupid' => $wx_info['groupid'],
                'subscribe_time' => date("Y-m-d H:i:s", $wx_info['subscribe_time']),
                'openid' => $openid,
                'add_time' => $time,
                'appid' => $appid,
                'desc' => $wx_info['remark'],
                'company_id' => $company_id,
                'tagid_list' => json_encode($wx_info['tagid_list']),
                'unionid' => $wx_info['unionid'],
                'is_sync' => 1,
                'subscribe' => $wx_info['subscribe'],
                'update_time' => $time,
                'qrcode_id' => $qrcode_id,
                'customer_service_uid' => empty($uid) == true ? -1 : $uid
            ]);

        $wx_info['is_update'] = false;
        $wx_info['wx_user_id'] = $wx_user_id;

        return $wx_info;
    }

    /**
     * 获取素材内容
     * @param company_id 商户company_id
     * @param appid 公众号或小程序appid
     * @param media_id 素材id
     * @param type 素材类型 1图片 2视频 3语音
     * @return code 200->成功
     */
    public function getMaterial($appid, $company_id, $media_id, $type)
    {
        switch ($type) {
            case 1:
                $content_type = 'image/png';
                break;
            case 2:
                $content_type = 'video/mp4';
                break;
            case 3:
                $content_type = 'audio/mp3';
                break;
            default:
                return msg(3003, 'type参数错误');
        }

        $token_info = Common::getRefreshToken($appid, $company_id);
        if ($token_info['meta']['code'] == 200) {
            $refresh_token = $token_info['body']['refresh_token'];
        } else {
            return $token_info;
        }

        try {
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $temporary = $openPlatform->createAuthorizerApplication($appid, $refresh_token)->material_temporary;

            $content = $temporary->getStream($media_id);

            //语音amr转mp3
            if ($type == 3) {
                $audio_name = md5(uniqid());
                if (!file_put_contents('../uploads/source_material/' . $audio_name . '.amr', $content, true)) {
                    return msg(3005, 'file_error');
                }

                $amr_file = "../uploads/source_material/$audio_name.amr";

                $mp3_file = "../uploads/source_material/$audio_name.mp3";

                $command = "/usr/local/bin/ffmpeg -i $amr_file $mp3_file";

                exec($command, $log, $status);

                if (!file_exists($mp3_file)) {
                    return msg(3004, 'file_error');
                }

                $PSize = filesize($mp3_file);
                $picture_data = fread(fopen($mp3_file, "r"), $PSize);

                unlink($amr_file);
                unlink($mp3_file);

                return response($picture_data)->contentType($content_type);
            }

            return response($content)->contentType($content_type);
        } catch (\Exception $e) {
            return msg(3001, $e->getMessage());
        }
    }

    /**
     * 获取上传的图片资源流数据
     * @param resources_id 资源id
     * @return code 200->成功
     */
    public function getImg($resources_id)
    {
        $res = Db::name('resources')->where([
            'resources_id' => $resources_id
        ])->find();
        if (!$res) {
            header("Content-Type:image/png");
            $im = imagecreate(300, 300);
            $black = imagecolorallocate($im, 100, 100, 100);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagettftext($im, 18, 0, 105, 100, $white, "../uploads/static/fonts/hwxh.ttf", "Error 003");
            imagettftext($im, 14, 0, 65, 150, $white, "../uploads/static/fonts/hwxh.ttf", "图片不存在或已过期！");
            imagepng($im);
            exit();
        }

        $file_ize = filesize('..' . $res['resources_route']);
        $picture_data = fread(fopen('..' . $res['resources_route'], "r"), $file_ize);

        return response($picture_data)->contentType($res['mime_type']);
    }

    /**
     * 获取上传的资源流数据
     * @param resources_id 资源id
     * @return code 200->成功
     */
    public function getFile($resources_id)
    {
        $res = Db::name('resources')->where([
            'resources_id' => $resources_id
        ])->find();
        if (!$res) {
            return msg(3001, 'not_file');
        }

        $file_ize = filesize('..' . $res['resources_route']);
        $picture_data = fread(fopen('..' . $res['resources_route'], "r"), $file_ize);

        return response($picture_data)->contentType($res['mime_type']);
    }

    //获取微信外链图片
    public function getWxUrlImg($url)
    {
        $im = imagecreate(300, 300);
        $black = imagecolorallocate($im, 100, 100, 100);
        $white = imagecolorallocate($im, 255, 255, 255);

        if (!$url) {
            header("Content-Type:image/png");
            imagettftext($im, 18, 0, 105, 100, $white, "../uploads/static/fonts/hwxh.ttf", "Error 001");
            imagettftext($im, 14, 0, 10, 150, $white, "../uploads/static/fonts/hwxh.ttf", "请在参数中输入图片的绝对地址！");
            imagepng($im);
            exit();
        }
        @$imgString = urlOpen($url);
        if ($imgString == "") {
            header("Content-Type:image/png");
            imagettftext($im, 18, 0, 105, 100, $white, "../uploads/static/fonts/hwxh.ttf", "Error 002");
            imagettftext($im, 14, 0, 10, 150, $white, "../uploads/static/fonts/hwxh.ttf", "加载远程图片失败地址无法访问！");
            imagepng($im);
            exit();
        }

        return response($imgString)->contentType('image/png');
    }
}