<?php
/**
 * 
 *  @name:      图片上传接口
 *  @author:    DYBOY
 *  @time:      2019-06-26
 *  @desc:      上传图片通用接口，支持多图上传。
 */

header("Access-Control-Allow-Origin:*");
header("API-AUYHOR:DYBOY");
error_reporting(7);

/**
 * 处理返回数据
 * @$code:  Integer
 * @$msg:   String
 * :return  Json
 */
function dyMsg($code=500, $msg='Server Error！') {
    // 考虑兼容php低版本 使用 array
    $result = array(
        'code' => $code,
        'msg' => $msg
    );
    header("Content-type: application/json; charset=utf-8");
    echo json_encode($result);
    exit(0);
}

/**
 * 生成随机图片名称字符串，jpg后缀保证其解析安全性
 * :return String
 */
function randStr() {
    return md5(time() . rand(1000000, 99999999)).'.jpg';
}


/**
 * 处理图片上传
 * @$fileName:  String
 * @$type:  String
 * :return  Array
 */
function uploadFile($fileName, $realname) {
    $uploadDir = '/img/';                 // 默认存储根路径下

    // 文件过大 ...    

    // 上传
    $name = randStr();

    $isSuccess = move_uploaded_file($fileName, dirname(__FILE__).'/..'.$uploadDir.$name);

    if($isSuccess) {
        // 获取图片信息
        $localImgInfo = getimagesize(dirname(__FILE__).'/..'.$uploadDir.$name);
        // 上传成功
        $arr = array(
            'code'      => '200',
            'name'      => $realname,
          	'pid'		=> @substr($name, 0, -4),
            'width'     => $localImgInfo[0],
            "height"    => $localImgInfo[1],
            "url"       => 'http'.(($_SERVER["SERVER_PORT"] == 443) ? 's':'').'://'.$_SERVER['SERVER_NAME'].((($_SERVER["SERVER_PORT"] == 443) || ($_SERVER["SERVER_PORT"] == 80)) ? '' : ':'.$_SERVER["SERVER_PORT"]).'/tc/img/'.$name,
          	"url2"       => 'http'.(($_SERVER["SERVER_PORT"] == 443) ? 's':'').'://'.$_SERVER['SERVER_NAME'].((($_SERVER["SERVER_PORT"] == 443) || ($_SERVER["SERVER_PORT"] == 80)) ? '' : ':'.$_SERVER["SERVER_PORT"]).'/tc/img/'.$name
        );
        
    } else {
        // 发生错误，返回默认图片
        $arr = array(
            'code'      => '200',
            'name'      => 'default.jpg',
          	'pid'		=> 'default',
            'width'     => '246px',
            "height"    => '205px',
            "url"       => 'http'.(($_SERVER["SERVER_PORT"] == 443) ? 's':'').'://'.$_SERVER['SERVER_NAME'].((($_SERVER["SERVER_PORT"] == 443) || ($_SERVER["SERVER_PORT"] == 80)) ? '' : ':'.$_SERVER["SERVER_PORT"]).'/images/default.jpg',
          	"url2"       => 'http'.(($_SERVER["SERVER_PORT"] == 443) ? 's':'').'://'.$_SERVER['SERVER_NAME'].((($_SERVER["SERVER_PORT"] == 443) || ($_SERVER["SERVER_PORT"] == 80)) ? '' : ':'.$_SERVER["SERVER_PORT"]).'/images/default.jpg'
        );
    }
    return $arr;
}



/**
 * 业务处理
 */
 
// type:  multipart [default]
$type = isset($_GET['type']) ? trim($_GET['type']) : 'multipart';

// 单张图片上传
if($type == 'single') {
	$imgInfo = uploadFile($_FILES['file']['tmp_name'], $_FILES['file']['name']);
	exit(json_encode($imgInfo));
}

// 判断图片数量
$num = count($_FILES['file']['name']);
if($num == 0 || $num > 10) {
    dyMsg(401, '请上传1~10张图片！');
}

// 获取用户上传图片
$FileUploadResult = [];
foreach ($_FILES['file']['tmp_name'] as $key => $value) {        
    $imgInfo = uploadFile($value, $_FILES['file']['name'][$key]);
    array_push($FileUploadResult, $imgInfo);
}
// 返回上传图片结果
dyMsg(200, $FileUploadResult);