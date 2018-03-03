<?php

namespace Common\Api;
class SystemApi {
    public function writeAccessLog($title,$channel='web'){
        $data['sessionid']=session_id();
        $data['title']=$title;
        $data['path'] = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : __SELF__;
        $data['requesturl'] = $this->get_url();
        $data['url'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $data['hostname'] = get_client_ip();
        $data['uid'] = is_login();
        $data['accesstime'] = time();
        $data['channel'] = $channel;
        D('accesslog')->add($data);
    }
        
    public function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }
}