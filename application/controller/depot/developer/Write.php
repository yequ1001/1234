<?php
/**
 * Created by PhpStorm.
 * User: Yequ1001
 * Date: 2019/7/3
 * Time: 21:34
 */

namespace app\controller\depot\developer;

use app\common\controller\BaseController;
use app\common\model\DeveloperNote as DeveloperNoteModel;
use app\common\facade\User;
use app\common\facade\Str;
use think\facade\Validate;

class Write extends BaseController
{
    public function index()
    {
        User::login();

        $id = input('id');
        if (!empty($id)) {
            $DeveloperNoteData = DeveloperNoteModel::get($id);
            $noteId = $DeveloperNoteData->id;
            $noteType = $DeveloperNoteData->type;
            $noteTitle = $DeveloperNoteData->title;
            $noteContent = $DeveloperNoteData->content;
        } else {
            $noteId = '';
            $noteType = '';
            $noteTitle = '';
            $noteContent = '';
        }
        return view()->assign([
            'title'         => '写开发笔记 - PHP',
            'description' => '',
            'noteId' => $noteId,
            'noteType' => $noteType,
            'noteTitle' => $noteTitle,
            'noteContent' => $noteContent,
        ]);
    }

    /**
     * 保存笔记
     */
    public function save()
    {
        if (User::type() != 'member') {
            return '您还没有登录';
        }

        $id = input('id');
        $title = input('title');
        $content = input('content');
        $type = input('type');
        $belong = input('belong');

        // 表单验证
        $validate = Validate::make([
            'id|ID' => 'number|max:20',
            'title|标题' => 'require|length:3,50',
            'content|内容' => 'require',
            'type|类型' => 'require|length:1,10',
            'belong|来源' => 'require|length:1,10'
        ]);
        $data = [
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'belong' => $belong
        ];
        if (!$validate->check($data)) {
            return $validate->getError();
        }

        $title = htmlspecialchars($title);
        $title = Str::filter($title);
        $content = trim(htmlspecialchars($content));
        $content = Str::filter($content);
        $type = htmlspecialchars($type);
        $belong = htmlspecialchars($belong);

        if (empty($id)) {
            DeveloperNoteModel::create([
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'time' => date('Y-m-d H:i:s'),
                'user' => User::id(),
                'belong' => $belong,
            ]);
        } else {
            $DeveloperNoteData = DeveloperNoteModel::get($id);
            $user = $DeveloperNoteData->user;
            if ($user != User::id()) {
                return '您没有权限修改这篇文章';
            }
            DeveloperNoteModel::where('id', $id)
                ->update([
                    'title' => $title,
                    'content' => $content,
                    'type' => $type,
                    'update_time' => date('Y-m-d H:i:s'),
                    'belong' => $belong,
                ]);
        }

        // 清除旧的缓存
        cache("DeveloperNote_{$type}", null);

        return true;
    }

    /**
     * 删除文章
     */
    public function del()
    {
        $id = input('id');
        $DeveloperNoteData = DeveloperNoteModel::get($id);
        if (!$DeveloperNoteData) {
            return '不存在的文章';
        }
        $user = $DeveloperNoteData->user;
        $type = $DeveloperNoteData->type;
        if ($user != User::id()) {
            return '您没有权限删除这篇文章';
        }
        DeveloperNoteModel::destroy($id);

        // 清除旧的缓存
        cache("DeveloperNote_{$type}", null);

        return true;
    }
}