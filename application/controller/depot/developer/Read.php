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
use think\facade\Cache;

class Read extends BaseController
{
    public function index()
    {
        // 建立缓存：将开发者笔记整个数据表存入缓存（该缓存共用于笔记详细页和列表页）
        Cache::remember('DeveloperNote_server',function(){
            $DeveloperNoteData = DeveloperNoteModel::where('type', 'server')->order('id', 'desc')->select();
            return $DeveloperNoteData;
        });
        Cache::remember('DeveloperNote_client',function(){
            $DeveloperNoteData = DeveloperNoteModel::where('type', 'client')->order('id', 'desc')->select();
            return $DeveloperNoteData;
        });

        // 读取笔记缓存，并遍历列表，获取上一笔记、当前笔记、下一笔记的数据
        $DeveloperNoteData = cache('DeveloperNote_'. input('type'));
        foreach ($DeveloperNoteData as $DeveloperNote) {
            if ($DeveloperNote->id > input('id')) {
                $lastData = $DeveloperNote;
            } else if ($DeveloperNote->id == input('id')) {
                $currentData = $DeveloperNote;
            } else {
                $nextData = $DeveloperNote;
                break;
            }
        }

        // 如果当前id的笔记不存在
        if (!isset($currentData)) {
            return '不存在的笔记';
        }

        // 读取当前id的笔记数据
        $id = $currentData->id;
        $title = $currentData->title;
        $user = $currentData->user;
        $nickname = User::nickname($user);
        $belong = $currentData->belong;
        $click = $currentData->click;
        $content = $currentData->content;

        // 笔记内容的UBB代码，不要用正则替换取代以下代码，因为正则匹配字符有长度限制，不适合在此场合使用
        $content = str_replace('[code]', '<pre><code>', $content);
        $content = str_replace('[/code]', '</code></pre>', $content);
        $content = str_replace("\n", "<br/>\n", $content);
        $content = str_replace('</code></pre><br/>', '</code></pre>', $content);

        // 插入html原样代码
        preg_match_all('#\[html\]((?!\[/html\]).)+\[/html\]#ims', $content, $htmlArr);
        foreach ($htmlArr as $html) {
            $html_ = str_replace('[html]', '', $html);
            $html_ = str_replace('[/html]', '', $html_);
            $html_ = str_replace('&lt;', '<', $html_);
            $html_ = str_replace('&gt;', '>', $html_);
            $html_ = str_replace('&quot;', '"', $html_);
            $html_ = str_replace('&amp;', '&', $html_);
            $html_ = str_replace('<br/>', '', $html_);
            $content = str_replace($html, $html_, $content);
        }

        // 更新浏览量+1
        $currentData->click = ['inc', 1];
        $currentData->save();

        // 获取上、下一篇笔记
        if (isset($lastData)) {
            $lastId = $lastData->id;
            $lastTitle = $lastData->title;
            $lastType = $lastData->type;
            $last = "<a href='/depot/developer/{$lastType}/{$lastId}.html'>上一篇：{$lastTitle}</a>";
        } else {
            $last = '<a style="color: #a7aab7">上一篇：没有了</a>';
        }
        if (isset($nextData)) {
            $nextId = $nextData->id;
            $nextTitle = $nextData->title;
            $nextType = $nextData->type;
            $next = "<a href='/depot/developer/{$nextType}/{$nextId}.html'>下一篇：{$nextTitle}</a>";
        } else {
            $next = '<a style="color: #a7aab7">下一篇：没有了</a>';
        }

        // 视图渲染
        return view()->assign([
            'description' => '',
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'user' => $user,
            'nickname' => $nickname,
            'belong' => $belong,
            'click' => $click,
            'last' => $last,
            'next' => $next,
            'type' => input('type'),
        ]);
    }
}