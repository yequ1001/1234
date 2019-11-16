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
use think\facade\Cache;

class Index extends BaseController
{
    public function index()
    {
        return view()->assign([
            'title'         => '开发者笔记 - PHP',
            'description' => '',
            'server_note' => $this->read('server'),
            'client_note' => $this->read('client'),
        ]);
    }

    public function read($type)
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

        if ($type == 'server') {
            return cache('DeveloperNote_server');
        } else {
            return cache('DeveloperNote_client');
        }
    }
}