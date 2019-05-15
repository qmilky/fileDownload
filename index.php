<?php
use \test\fileDownload\fileDownload;


function download_client()
{
    $file = './43兆的音频.mp3.zip';
    $name = time().'.zip';
    $obj = new fileDownload();
    //$flag = $obj -> download($file, $name);
    $flag = $obj -> download($file, $name, true);
    var_dump($flag);
    if (! $flag)
    {
        echo 'file not exists';
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
    if (is_file($name))
    {
        include_once($name);
    }
}