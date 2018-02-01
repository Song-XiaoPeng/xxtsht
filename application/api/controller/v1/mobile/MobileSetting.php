<?php
/**
 * Created by PhpStorm.
 * User: SuperMan
 * Date: 2018/1/27
 * Time: 16:01
 */

namespace app\api\controller\v1\mobile;

use app\api\common\Auth;
use think\Loader;

class MobileSetting extends Auth
{
    /**
     * 个人信息设置
     * $params sex 性别
     * $params autograph 个性签名
     * $params user_name 姓名
     *
     */
    public function profileSetting()
    {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        return Loader::model('MobileSettingLogic', 'logic\v1\mobile')->profileSetting($data);
    }

    /**
     * 个人信息列表
     */
    public function profile()
    {
        return Loader::model('MobileSettingLogic', 'logic\v1\mobile')->profile($this->uid);
    }
}