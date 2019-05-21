<?php
use \test\fileDownload\fileDownload;

function download_client()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
    {
        // 测试 ajax
        $data = [];
        if (isset($_SERVER['HTTP_REQUEST_TYPE']))
        {
            $data['request'] = $_SERVER['HTTP_REQUEST_TYPE'];
        }
        if (isset($_SERVER['HTTP_RANGE']))
        {
            $data['range'] = $_SERVER['HTTP_RANGE'];
        }
        $data['request_type'] = $_SERVER['HTTP_X_REQUESTED_WITH'];  // 若是ajax 请求就会有该请求头参数

        echo json_encode($data);
    } else {
        $file = '43兆的音频.mp3.zip';
        //$file = '43兆的音频.mp3';
        //$name = time().'.mp3';
        $name = time().'.zip';
        $obj = new fileDownload();
        //$flag = $obj -> download($file, $name);
        $flag = $obj -> download($file, $name, true);
        //var_dump($flag);
        if (! $flag)
        {
            echo 'file not exists';
        }
    }


}

download_client(); // 浏览器中下载文件，用户将文件下载到本地

function download_to_server()
{
    $file = './43兆的音频.mp3.zip';
    $str = file_get_contents($file);
    file_put_contents('test.zip', $str);
}
//download_to_server();  // 将文件下载到服务器到某个位置


/**
 * 自动加载函数，use 只是引入类或者命名空间，若没有自动加载仍然会报错
 * 调用未加载的类时自动触发，定义在类外的魔术方法
 *
 * */
function __autoload($class)
{

    // 包含命名空间该如何引入呢？？？将命名空间去掉只保留类名部分
    $a = substr($class, strrpos($class, "\\") + 1);
    $name = $a . '.class.php';
    // 也可以使用 file_exists(),is_file()和file_exists()效率比较，结果当文件存在时，is_file函数比file_exists函数速度快14倍，当文件不存在时，两者速度相当。同理，当文件目录存在时，is_dir()比file_exists()快18倍。不存在时两者效率相当。

    if (is_file($name))
    {
        include_once($name);
    }
}
