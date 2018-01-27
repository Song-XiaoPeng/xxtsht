<?php
/**
 * Created by PhpStorm.
 * User: SuperMan
 * Date: 2018/1/27
 * Time: 16:04
 */

namespace app\api\logic\v1\mobile;

use think\Db;
use think\Model;

class MobileSettingLogic extends Model
{
    //个人信息设置
    public function profileSetting($data)
    {
        $uid = $data['uid'];
        $company_id = $data['company_id'];
        $name = !empty($data['user_name']) ? $data['user_name'] : '';
        $sex = !empty($data['sex']) ? $data['sex'] : '';
        $autograph = !empty($data['autograph']) ? $data['autograph'] : '';
        $where = [
            'uid' => $uid,
            'company_id' => $company_id
        ];
        if ($name) {
            $res = Db::name('user')->where($where)->update(['user_name' => $name]);
        }

        if ($sex) {
            $res = Db::name('user')->where($where)->update(['sex' => $sex]);
        }
        if ($autograph) {
            $res = Db::name('user')->where($where)->update(['autograph' => $autograph]);
        }

        if ($res >= 0) {
            return msg(200, 'success');
        } else {
            return msg(6001, '修改失败');
        }
    }
}