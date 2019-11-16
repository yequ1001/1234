<?php

namespace app\controller\user;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Validate;
use think\Db;
use app\common\facade\User;
use app\common\model\UserMessage as MessageModel;
use app\common\model\User as UserModel;

class Message extends BaseController
{
    public function index()
    {
        /**
         * 如果用户没有登陆则强制退出当前页面
         */
        User::login();

        // 对方用户名字
        $toInfo = UserModel::get(input('from'));

        //查询 聊天目标用户的名字
        if(input('from') != '1000'){
            $formNickname = '与 '. $toInfo['nickname'] .' 对话';
        } else {
            $formNickname = '系统消息';
        }

        //获取信息
        View::assign([
            'description' => '',
            'to' => input('from'),
            'title' => $formNickname,
        ]);
        return View::fetch();
    }

    /**
     * 返回聊天记录,
     */
    public function read($him){
        $toInfo = UserModel::get($him);
        // 当前用户的ID和对话列表的更新时间
        $me         = User::id();
        $finalTime  = input('finalTime');
        if (empty($finalTime)) {
            $finalTime = '1991-12-03 12:00:00';
        }

        // 每页显示对话的数量
        $item = 16;

        // 对话总数
        $items = Db::query('SELECT id FROM op_user_message WHERE form = ? AND `to` = ? OR form = ? AND `to` = ?',[
            $me,$him,$him,$me
        ]);
        $items = count($items);

        //总页数
        $pages = ceil($items / $item);

        //当前页码
        $page = 1;
        if (!empty(input('page'))) {
            $page = input('page');
        }

        $item_ = $item;

        // 计算对话纪录分页的开始位置
        if ($finalTime == '1991-12-03 12:00:00') {
            if($page != $pages){
                $startItem = $item*($pages-$page-1)+($items % $item);
                if ($startItem < 0) {
                    $startItem = 0;
                }
            } else {
                $startItem = 0;
                $item = $items % $item;
				if ($item == 0) {
					$item = 16;
				}
            }
        } else {
            $startItem = 0;
        }
        // 分页查询对话记录
		
        $messageData = Db::query(
            'SELECT * FROM op_user_message WHERE (form = ? AND `to` = ? OR form = ? AND `to` = ?) AND time > ? AND id >= (SELECT id FROM op_user_message WHERE (form = ? AND `to` = ? OR form = ? AND `to` = ?) AND time > ? ORDER BY id ASC LIMIT ?,1) ORDER BY id ASC LIMIT ?',[
            $me,$him,$him,$me,$finalTime,$me,$him,$him,$me,$finalTime,$startItem,$item
        ]);

        // 设置已读
        if ($page == 1) {
            // 将已忽略消息设为 已读
//            MessageModel::where('to',$me)
//                -> where('form', $him)
//                -> where('state', 0)
//                -> update(['state' => 1]);
            // 将未读消息设为 已读/忽略，并计数
            $num = MessageModel::where('to',$me)
                -> where('form', $him)
                -> where('state', 0)
                -> update(['state' => 1]);
            $UserData = UserModel::get($me);
            $UserData->message = $UserData->message - $num; // 此处不能用自减的方式：['dec', $num];
            $UserData->save();
        }

        // 获取收件人在线状态
        $nowTime    = strtotime(date('Y-m-d H:i:s'));
        $onlineTime = strtotime($toInfo['final_time']);
        $timeSpan   = ceil(($nowTime - $onlineTime) / 24);
        if ($timeSpan <= 3 ) {
            $online = 1;
        } else {
            $online = 0;
        }

        // 合成并返回JSON
        if ($items % $item_ == 0) {
            $pages --;
        }

        $arr = [
            $online,        // 对方在线状态
            $messageData,   // 对话记录
            $pages,         // 总页数
            $item,          // 每页对话上限
            $items,         // 对话总数
            User::nickname($him),  // 对话用户的名字
            User::nickname(),      // 当前用户的名字
            User::face($him),      // 对话用户的头像
            User::face()           // 当前用户的头像
        ];
        return json($arr);
    }

    /**
     * 保存新消息
     */
    public function save(){
        $form = input('form');
        if (empty($form)) {
            $form = User::id();
        }

        // 将表单信息写入当前类变量$this->user_info_arr
        $this->messageArr = [
            'form'         => $form,
            'to'           => input('to'),
            'content'      => input('content'),
        ];

        // 建立表单验证规则
        Validate::rule([
            'form|发件人ID'     => 'require|number',
            'to|收信人'         => 'require|number',
            'content|内容'      => 'require',
        ]);

        // 表单验证，将验证结果（即bool值）写入变量$bool
        if (!Validate::check($this->messageArr)) {
            return Validate::getError();
        }
        if ($this->messageArr['to'] == '0') {
            return '系统消息，无须回复';
        }
        if ($this->messageArr['to'] == User::id()) {
            return '收件人不能是自己';
        }

        // 往数据库写入新用户信息
        $messageInfo = [
            [
                'to'                => $this->messageArr['to'],
                'form'              => $this->messageArr['form'],
                'content'           => $this->messageArr['content'],
                'time'              => date('Y-m-d H:i:s'),
            ]
        ];
        if ((new MessageModel) -> saveAll($messageInfo)) {
            $user = UserModel::get($this->messageArr['to']);
            $user->message = ['inc', 1];
            $user->save();
            return 'OK';
        } else {
            return (new MessageModel)->getError();
        }
    }

    /**
     * 新消息通知（用于Ajax）
     */
    public function inform(){
        $messageCount = MessageModel::where([
            'state' => 0,
            'neglect' => 0,
        ])
            ->where('to', 'IN', [cookie('user_id')])
            ->field('id,content,to,form,time')
            ->order('form', 'asc')
            ->order('id', 'asc')
            ->select();

        // 获取所有的发件人ID
        $fromArr = [];
        foreach ($messageCount as $message) {
            if (!in_array($message['form'], $fromArr)) {
                array_push($fromArr, $message['form']);
            }
        }
        $fromData = UserModel::where('id', 'IN', $fromArr)->field('id,nickname,face')->select();
        $arr = [];
        foreach ($messageCount as $message) {
            foreach ($fromData as $from) {
                if ($message['form'] == $from['id']) {
                    $message['formNickname'] = $from['nickname'];
                    $message['formFace'] = $from['face'];
                }
                array_push($arr, $message);
            }
        }
        return json($arr);
    }

    /**
     * 忽略新消息（用于Ajax）
     */
    public function neglect(){
        $num = MessageModel::where('to', User::id())
            -> where('state', 0)
            -> update(['state' => 1]);

        $user = UserModel::get(User::id());
        $user->message = ['dec', $num];
        $user->save();
        return true;
    }
}