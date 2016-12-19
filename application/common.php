<?php
use think\Request;
use think\Log;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function log_action($uid , $action , $data){
    $data['uid'] = $uid;
    $data['action'] = $action;
    $data['data'] = $data;
    
    $msg = json_encode($data);
    Log::write($msg , 'data');
}

/**
 * 清空缓存
 * @param unknown $name
 * @param unknown $key
 */
function cache_clear($name , $key = ''){
    
    $data = cache($name);
    if ($key){
        if (isset($data[$key]) && $data[$key]){
            unset($data[$key]);
        }
        cache($name , $data);
    }else{
        cache($name , null);
    }
    
}

/**
 * 获取缓存
 * @param unknown $name
 * @param string $key
 */
function cache_get($name , $key = ''){
    
    $data = cache($name);
    if ($key){
        if (isset($data[$key]) && $data[$key]){
            return $data[$key];
        }else {
            return null;
        }
        
    }else{
        return cache($name);
    }
}

/**
 * 设置缓存
 * @param unknown $name
 * @param unknown $value
 * @param string $key
 */
function cache_set($name , $value , $key = ''){
    
    if ($key){
        $data = cache($name) ? cache($name) :[];
        $data[$key] = $value;
        cache($name , $data);
    }else{
        cache($name, $value);
    }
}

/**
 * 获取用户组
 * @param unknown $uid
 * @param unknown $field
 * @return string|string|unknown|unknown
 */
function get_user_group($uid , $field = null){
    
    $data = cache('user_group_list');
    if (isset($data[$uid]) && $data[$uid]){
        if ($field){
            return $data[$uid][$field] ? $data[$uid][$field] : '';
        }else{
            return $data[$uid];
        }
    }else{
        $data = [];
        $userGroup = db('AuthUserGroup')->where('user_id' , $uid)->find();
        if ($userGroup){
            
            $groupId = $userGroup['group_id'];
            $group = db('AuthGroup')->find($groupId);
            $data[$uid] = $group;
            
            cache('user_group_list' , $data);
            
            if ($field){
                return $group[$field] ? $group[$field]: '';
            }else{
                return $group;
            }
        }else{
            return '';
        }
    }
}

/**
 * 获取用户管理的项目
 * @param unknown $uid
 * @param unknown $status
 * @return \think\Collection|\think\db\false|PDOStatement|string|unknown[]|\think\Collection[]|\think\db\false[]|PDOStatement[]|string[]
 */
function get_user_project($uid , $status = null){
    
    $data = cache('user_project_list');
    if (isset($data[$uid]) && $data[$uid]){
        $result = $data[$uid];
    }else{
        $user = db('user')->find($uid);
        if ($user['pid'] == 0){
            $projects = db('Project')->where('uid' , $uid)->select();
        }else{
            $userProject = db('UserProject')->where('user_id' , $uid)->find();
            if ($userProject){
                $projectIds = $userProject ? $userProject['projects'] : [];
                $projects = db('Project')->where('id' , 'in' , $projectIds)->select();
            }else{
                $projects = null;
            }
        }
        
        $data[$uid] = $projects;
        cache('user_project_list' , $data);
        $result = $projects;
    }
    
    if ($status == null){
        return $result;
    }else{
        $res = [];
        foreach ($result as $v){
            if ($v['status'] == $status){
                $res[] = $v;
            }
        }
        return $res;
    }
}
/**
 * 菜单格式化
 * @param unknown $menu
 * @param unknown $current
 * @param number $p_id
 */
function menu_format($menu , $current ,$p_id = 0){
    
    $pid = 0;
    foreach ($menu as $k=>$v){
        
        $pre = config('dev.admin_prefix');
        $v['name'] = $pre.'/'.$v['name'];
        if ($current){
            if (strtolower($v['name']) == $current){
                $v['active'] = 'active';
                $pid = $v['pid'];
            }else {
                $v['active'] = isset($v['active']) ? $v['active'] :'';
            }
        }
        
        if ($p_id){
            if ($v['id'] == $p_id){
                $v['active'] = 'active';
                $pid = $v['pid'];
            }else{
                $v['active'] = isset($v['active']) ? $v['active'] :'';
            }
            
        }
        
        $menu[$k] = $v;
    }
    
    if ($pid == 0){
        $result = $menu;
    }else{
        $result = menu_format($menu, '',$pid);
    }
    
    return $result;
}

function parse_config($value){
    
    $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
    $value = [];
    foreach ($array as $v){
        $value[] = explode('|', $v);
    }
    
    return $value;
}