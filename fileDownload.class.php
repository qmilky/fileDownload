<?php
/**
 *  php 断点续传下载类
 * */
namespace test\fileDownload;
class fileDownload {
    private $_speed = 10240; // 下载速度 ,默认 1M

    /**
     * 下载方法
     * @param string $file 要下载的文件路径
     * @param string $name 文件名称，为空则与下载的文件名一样
     * @param string $reload 是否开启断点续传
     * 此方法可以实现断点续传，但是下载下来的文件只有断点之后的文件内容，之前的没有保留；而且加 HTTP/1.1 206 Partial Content 头信息返回时会一直重试不下载
     *
     * */
    public function download($file, $name = '', $reload = false)
    {
        if (file_exists($file))
        {
            if ($name === '')
                $name = basename($file);
            // fopen ( string $filename , string $mode [, bool $use_include_path = false [, resource $context ]] ) : resource
            // 如果 filename 是 "scheme://..." 的格式，则被当成一个 URL，PHP 将搜索协议处理器（也被称为封装协议）来处理此模式。
            // 如果 PHP 认为 filename 指定的是一个本地文件，将尝试在该文件上打开一个流。该文件必须是 PHP 可以访问的，因此需要确认文件访问权限允许该访问。
            $fp = fopen($file, 'rb'); // r 是只读方式打开，b 与操作系统的行结束符号的使用有关
            $size = filesize($file);
            $ranges = $this->get_range($size);
//            $fp1 = fopen('./test.zip', 'w');

            header('cache-control:public');
            // application/octet-stream 只能提交二进制，而且只能提交一个二进制，如果提交文件的话，只能提交一个文件,后台接收参数只能有一个，而且只能是流（或者字节数组）；很少使用
            header('content-type:application/octet-stream');
            header('content-disposition:attachment; filename='.$name);



            if ($reload && $ranges != null)
            {
                // 使用续传,此处每次断点重新打开继续下载时都会执行一次
                //不注释此步会一直重试，注释之后只下载了断点之后的文件，断点之前的文件未保留，如何保留？？？
//                header('HTTP/1.1 206 Partial Content');
                header('Accept-Ranges:bytes');
                // 剩余长度
                header(sprintf('content-length:%u', $ranges['end'] - $ranges['start']));
                // range信息
                header(sprintf('content-range:bytes %s-%s/%s', $ranges['start'], $ranges['end'], $size));
                // fp 指针跳到断点位置
                fseek($fp, sprintf('%u', $ranges['start']));
//                file_put_contents('./log.php', var_export(333,true), FILE_APPEND);  // 测试是否走断点

            } else {
                header('HTTP/1.1 200 OK');
                header('content-length:'. $size); //content-length: 不是Content-length:，首字母无需大写

            }
            // feof（）检测是否已经到达文件末尾，文件指针到了EOF 或者出错时返回 true，否则返回一个错误（包括socket超时），其他情况则返回 false
            // $fp 规定要检查的打开文件，$fp 参数是一个文件指针，这个文件指针必须有效，并且必须指向一个由 fopen 或 fsockopen（） 成功打开（但还没有被fclose（）关闭）的文件
            while (!feof($fp))
            {
                //此处会一直执行，直到文件下载完成
                // fread（resource $handle , int $length） — 读取文件（可安全用于二进制文件），$handle 文件系统指针，是典型地由 fopen() 创建的 resource(资源)。
                // 返回所读取的字符串， 或者在失败时返回 FALSE。
                echo fread($fp, round($this->_speed*1024,0));// 变量名 $this->_speed要写对不能写成 $this->__speed，否则文件被损坏无法解压
//                fwrite($fp1, 1111);   //打开此处会下载非常慢
//                此处将内容以追加的方式存储到对应文件中
                // 实现file_put_contents高并发写入文件，需要使用到第三个参数flags，flags参数为LOCK_EX即可在高并发时获得一个独占锁定。如：file_put_contents('pickles.txt', $contents, FILE_APPEND | LOCK_EX);
//                $res = file_put_contents('./test.zip', fread($fp, round($this->__speed * 1024, 0)), FILE_APPEND);
//                file_put_contents('./log.php', var_export([$res], true), FILE_APPEND);
                ob_flush();  // 刷新PHP自身的缓冲区作用
//                sleep(1); // 用于测试，减慢下载速度
            }

            ($fp != null) && fclose($fp);  // 2 个条件同时执行，相当于若 $fp 不为空就关闭该文件

        } else {
            return '';
        }
    }


    /**
     * 获取 header 的 range 信息
     * @param int $size 文件大小
     * @return Array
     *
     * */
    private  function get_range($size)
    {
        if (isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE']))
        {
            $range = $_SERVER['HTTP_RANGE'];  //'bytes=20971520-',
            file_put_contents('log.php', var_export([$range,7], true),FILE_APPEND);

            $range = preg_replace('/[\s|,].*/', '', $range);  // TODO 'bytes=20971520-',
            file_put_contents('log.php', var_export([$range,8], true),FILE_APPEND);
            $range = explode('-', substr($range, 6));// TODO 截取 '=' 后面的字符串
            if (count($range) < 2)// TODO
            {
                $range[1] = $size;
            }
            $range = array_combine(array('start', 'end'), $range);
            if (empty($range['start']))
            {
                $range['start'] = 0;
            }
            if (empty($range['end']))
            {
                $range['end'] = $size;
            }
            file_put_contents('log.php', var_export($range, true),FILE_APPEND);

            return $range;
        }

        return null;
    }

    /**
     * 设置下载速度
     * @param $speed int
     * 暂时未使用
     * */
    public function setSpeed($speed)
    {
        if (is_numeric($speed) && $speed > 16 && $speed < 4096)
        {
            $this->_speed = $speed;
        }
    }




}