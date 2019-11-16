<?php
namespace app\controller\user;

use think\Db;
use app\common\controller\BaseController;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\model\User as UserModel;
use app\common\model\UserMessage as MessageModel;

class Index extends BaseController
{
    /**
     * 储存来自表单发送的用户信息，以数组形式保存
     */
    private $messageArr;

    public function index()
    {
        /**
         * 如果用户没有登陆则强制退出当前页面
         */
        User::login();

        /**
         * 获取当前用户信息
         */
        $userData   = User::data();
        $id         = $userData->id;
        $nickname   = $userData->nickname;
        $face       = $userData->face;
        $autograph  = $userData->autograph;

        /**
         * 视图渲染
         */
        return view()->assign([
            'title'         => '用户中心',
            'description' => '',
            'userId'        => $id,
            'nickname'      => $nickname,
            'face'          => $face,
            'autograph'     => $autograph,
            'messageList'   => $this->read(),
            'empty'         => '<br/><br/><br/><p class="empty">您没有新的消息</p>',
            ]);
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        cookie(null, 'user_');
        // 删除安全码
        session('password2', null);
        return $this->redirect('/');
    }

    /**
     * 读取新信息
     */
    public function read(){
        // 读取数据库对话数据
        $user = User::id();
        $page = input('page');
        if (empty($page) || $page < 0) {
            $page = 1;
        }
        $item = 16;
        $s = ($page-1) * $item;
        $messageData = Db::query("select * from op_user_message where `to`={$user} and id in (select max(id) from op_user_message where `to`={$user} group by form) UNION select * from op_user_message where `form`={$user} and id in (select max(id) from op_user_message where `form`={$user} group by `to`) ORDER BY time desc limit {$s},{$item}");

        // 所有对话人
        $peopleAllArr = [];
        foreach ($messageData as $message) {
            if (!in_array($message['form'], $peopleAllArr) && $message['form'] != $user) {
                array_push($peopleAllArr, $message['form']);
            }
            if (!in_array($message['to'], $peopleAllArr) && $message['to'] != $user) {
                array_push($peopleAllArr, $message['to']);
            }
        }
        $newMessageData = [];
        foreach ($peopleAllArr as $people) {
            foreach ($messageData as $key => $message) {
                if (in_array($people, $message)) {
                    array_push($newMessageData, $message);
                    unset($messageData[$key]);
                    break;
                }
            }
        }
        $messageData = $newMessageData;

        // 将数据库所有的 未忽略信息 设置为 忽略
        MessageModel::where('to', User::id())
            ->where('state', 0)
            ->update(['state' => -1]);
        UserModel::where('id', User::id())
            ->update(['message' => 0]);

        // 所有对话的名单：包含最新的在线时间，名字，头像
        $arrUserOnline = UserModel::where('id', 'IN', $peopleAllArr)
            ->field('id,final_time,nickname,face')
            ->select();

        // 列表，用于输出的列表视图
        $messageList = [];

        // 开始循环：生成列表
        for($i = 0; $i < count($messageData); $i ++){
            // 当前对话的基本信息
            $form           = $messageData[$i]['form'];
            $to             = $messageData[$i]['to'];
            $content        = $messageData[$i]['content'];
            $time           = $messageData[$i]['time'];

            $content = preg_replace('#<[^>]+>#ims', '', $content);

            foreach ($arrUserOnline as $user) {
                if ($form == $user['id']) {
                    $formNickname = $user['nickname'];
                    $formFace = $user['face'];
                }
                if ($to == $user['id']) {
                    $toNickname = $user['nickname'];
                    $toFace = $user['face'];
                }
            }
            // 设置当前对话的时间显示格式
            $time = Str::simpleTime($time);
            // 对话内容显示格式
            if (mb_strlen($content) > 11){
                $content = mb_substr($content,0,11) .'..';
            }
            // 生成对话列表项
            if ($to == User::id()) {
                $to = $form;
                $toNickname = $formNickname;
                $toFace = $formFace;
            }
            // 在线状态
            $onlineTag = '';
            foreach ($arrUserOnline as $online) {
                if ($online['id'] == $to) {
                    $nowTime = strtotime(date('Y-m-d H:i:s'));
                    $onlineTime = strtotime($online['final_time']);
                    $timeSpan = ceil(($nowTime - $onlineTime) / 24);
                    if ($timeSpan <= 2 ) {
                        $onlineTag = '<span class="layui-badge layui-bg-green">在线</span>';
                    }
                }
            }
            $arr = array("to"=>$to, 'toFace'=>$toFace, 'toNickname'=>$toNickname, 'onlineTag'=>$onlineTag, 'content'=>$content, 'time'=>$time);
            array_push($messageList, $arr);
        }
        return $messageList;
    }
}
