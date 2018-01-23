<?php

namespace app\api\logic\v1\short_message;

use GuzzleHttp\Client;
use think\Exception;
use think\Model;
use think\Db;

class ShortMessageLogic extends Model
{
    public function getVerifyCode($data)
    {
        $phone = $data['phone'];

        if (!$this->isMobile($phone)) {
            return msg(3001, '手机号格式输入不正确');
        }
        $company_id = $data['company_id'];
        Db::startTrans();
        try {
            //短信验证码
            $verify_code = rand(100000, 999999);
            $insert_verify_data = [
                'verification_code' => $verify_code,
                'phone' => $phone,
                'create_time' => date('Y-m-d H:i:s'),
            ];
            $insert_res = Db::name('verification_code')->insert($insert_verify_data);
            if (!$insert_res) {
                throw new Exception('操作失败');
            }
            //要发的短信
            $message_save_data = [
                'content' => '您的短信验证码为{$verify_code}',
                'company_id' => $company_id,
                'send_phone' => $phone,
                'add_time' => date('Y-m-d H:i:s'),
            ];
            $insert_res = Db::name('short_message')->insert($message_save_data);
            if (!$insert_res) {
                throw new Exception('操作失败');
            }
            Db::commit();
            return msg(200, 'success');
        } catch (\Exception $e) {
            Db::rollback();
            return msg(3001, '插入数据失败');
        }
    }

    //批量发送验证码
    public function sendShortMessage()
    {
        $account = config('message_account');
        $password = md5(config('message_password'));
        $domin = config('message_api_url');
        $send_data = Db::name('short_message')->where('state', -1)->select();

        //短信验证码
        $client = new Client();
        foreach ($send_data as $v) {
            $send_time = date('Y-m-d H:i:s');
            $res = $client->request('POST', 'http://msg.lyfz.net:8600/ISmsService/SendMessage', [
                'account' => $account,
                'password' => $password,
                'phone' => $v['send_phone'],
                'content' => $v['content'],
                'time' => $send_time
            ]);
            if ($res->code == 0) {
                Db::name('short_message')->where('message_id', $v['message_id'])->update(['state' => 1, 'send_time' => $send_time]);
            } else {
                return msg(3001,$res->msg);
            }
        }
    }

    //修改密码
    public function resetPassword($data)
    {
        $phone = $data['phone'];

        if (!$this->isMobile($phone)) {
            return msg(3001, '手机号格式输入不正确');
        }
        $password = $data['password'];
        $repassword = $data['repassword'];
        if($password !== $repassword){
            return msg(3001,'两次输入的密码不一致');
        }
        $verify_code = $data['verify_code'];
        $company_id = $data['company_id'];
        $where = [
            'phone' => $phone,
            'verification_code' => $verify_code,
            'state' => -1
        ];
        $exist = Db::name('verification_code')->where($where)->find();
        if ($exist) {
            Db::name('verification_code')->where('phone', $phone)->update(['state' => 1]);
            $user_map = [
                'company_id' => $company_id,
                'phone_no' => $phone,
            ];
            $save_data = [
                'password' => md5($password)
            ];
            $res = Db::name('user')->where($user_map)->update($save_data);
            if ($res >= 0) {
                return msg(200, '修改密码成功');
            } else {
                return msg(3001, '密码修改失败');
            }
        } else {
            return msg(3001, '手机验证码输入有误');
        }
    }

    public function isMobile($phone)
    {
        preg_match("/^1[34578]{1}\d{9}$/", $phone) ? true : false;
    }
}