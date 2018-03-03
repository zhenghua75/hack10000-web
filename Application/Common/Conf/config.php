<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common','User','Install'),
    'MODULE_ALLOW_LIST'  => array('Home','Admin','Service'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => '>EiL!Gv5C,`9f<I|8P0HVYQx3RJwK%?y/Bua$ql{', //默认数据加密KEY

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 数据库配置 */
    // 'DB_TYPE'   => '', // 数据库类型
    // 'DB_HOST'   => '', // 服务器地址
    // 'DB_NAME'   => '', // 数据库名
    // 'DB_USER'   => '', // 用户名
    // 'DB_PWD'    => '',  // 密码
    // 'DB_PORT'   => '', // 端口
    // 'DB_PREFIX' => '', // 数据库表前缀

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),

    /*加载扩展配置文件*/
    'LOAD_EXT_CONFIG' => 'db', 

    /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './hackimages/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）
    'PICTURE_UPLOAD_DRIVER'=>'ftp',
    //本地上传文件驱动配置
    'UPLOAD_LOCAL_CONFIG'=>array(),
    'UPLOAD_FTP_CONFIG'=>array(
        'host'=>'123.57.173.194',
        'port'=>21,
        'timeout'=>90,
        'username'=>'www',
        'password'=>'Jn7l7PFaW',
    ),

    'UNIONPAY_SDKCONFIG'=>array(
        'SIGN_CERT_PATH' => getcwd() . '/Public/certs/hack10000_acp.pfx',  // 签名证书路径
        'SIGN_CERT_PWD' => '186196',  // 签名证书密码
        'ENCRYPT_CERT_PATH' => getcwd() . '/Public/certs/acp_prod_enc.cer', // 密码加密证书（这条一般用不到的请随便配）
        'VERIFY_CERT_DIR' => getcwd() . '/Public/certs/',  // 验签证书路径（请配到文件夹，不要配到具体文件）
        'FRONT_TRANS_URL' => 'https://gateway.95516.com/gateway/api/frontTransReq.do', // 前台请求地址
        'BACK_TRANS_URL' => 'https://gateway.95516.com/gateway/api/backTransReq.do',  // 后台请求地址
        'BATCH_TRANS_URL' => 'https://gateway.95516.com/gateway/api/batchTrans.do',  // 批量交易
        'SINGLE_QUERY_URL' => 'https://gateway.95516.com/gateway/api/queryTrans.do',  //单笔查询请求地址
        'FILE_QUERY_URL' => 'https://filedownload.95516.com/',  //文件传输请求地址
        'Card_Request_Url' => 'https://gateway.95516.com/gateway/api/cardTransReq.do',  //有卡交易地址
        'App_Request_Url' => 'https://gateway.95516.com/gateway/api/appTransReq.do',  //App交易地址
        'FRONT_NOTIFY_URL' => 'http://www.hack10000.com/Unionpay/usernotify',  // 前台通知地址 (商户自行配置通知地址)
        'BACK_NOTIFY_URL' => 'http://www.hack10000.com/Unionpay/notify',  // 后台通知地址 (商户自行配置通知地址，需配置外网能访问的地址)
        'FILE_DOWN_PATH' => getcwd() . '/Uploads/Download/',  //文件下载目录 
        'LOG_FILE_PATH' => getcwd() . '/log/',  //日志 目录 
        'LOG_LEVEL' => 'INFO',  //日志级别
    ),
);
