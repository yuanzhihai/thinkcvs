<?php

declare (strict_types=1);

namespace yzh52521;

/**
 * Class ThinkCsv
 * @package yzh52521
 */
class ThinkCsv
{
    public $filename;
    public $header;
    public $data;

    /**
     * constructor.
     * @param string $filename 文件名称
     * @param array $header 表头数组
     * @param array $data 数据数组
     */
    public function __construct($filename = '', array $header = [], array $data = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        $this->filename = $filename;
        $this->header   = $header;
        $this->data     = $data;
    }

    /**
     * 导出csv
     * DateTime: 2021/4/27
     */
    public function export()
    {
        //下载csv的文件名
        $fileName = $this->filename;
        //设置header头
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) {
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
        } else {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        //打开php数据输入缓冲区
        $fp     = fopen('php://output', 'ab');
        $header = $this->header;
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $header);
        $data = $this->data;
        foreach ($data as $row) {
            fputcsv($fp, $row);
            unset($row);
        }
        fclose($fp);
    }

    /**
     * 服务器存储csv,生成文件链接给到前端
     */
    public function csvToFile()
    {
        $filename = $this->filename;
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');
        ob_start();
        header("Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition:attachment;filename=" . $filename);
        @file_put_contents($filename, '');
        @chmod($filename, 0777);
        $fp = fopen($filename, 'wb');
        //转码 防止乱码(比如微信昵称(乱七八糟的))
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $this->header);
        $index = 0;
        foreach ($this->data as $item) {
            if ($index === 1000) {
                $index = 0;
                ob_flush();
                flush();
            }
            $index++;
            fputcsv($fp, $item);
        }
        ob_flush();
        flush();
    }

    /**
     *  读取csv数据
     * @param $filePath
     * @return false|string
     */
    public function getCsvData($filePath)
    {
        $handle = fopen($filePath, "rb");
        $data   = [];
        while (!feof($handle)) {
            $data[] = fgetcsv($handle);
        }
        fclose($handle);
        //字符转码操作
        return iconv('gb2312', 'utf-8', var_export($data, true));
    }

    /**
     * 静态方法导入csv
     * @param $filePath
     * @return false|string
     */
    public static function readCsvData($filePath)
    {
        $handle = fopen($filePath, "rb");
        $data   = [];
        while (!feof($handle)) {
            $data[] = fgetcsv($handle);
        }
        fclose($handle);
        //字符转码操作
        return iconv('gb2312', 'utf-8', var_export($data, true));
    }

    /**
     * 协程导出大量数据
     * @param array $dataList 数据源
     * @param string $path 对应文件路径
     * @param string $filename 文件名字 如 [部门,名字]
     * @param callable $callback 自定义回调函数 返回一位数组
     * @param array $config
     * @return bool
     * @throws \Exception
     */
    public static function createDataToCsvFile(array $dataList, string $path, string $filename, callable $callback, array $config): bool
    {
        set_time_limit(0);// 设置不超时
        ini_set('memory_limit', '1024M');
        $fileInfoArr = explode('.', $filename);
        $newFilename     = $fileInfoArr[0] . '_' . $config['num'] . '.csv';
        $newPathFilename = $path . $newFilename;
        // 缓冲区写临时文件
        $output = fopen($newPathFilename, 'wb');
        // 写头
        if (!empty($config['headerRow'])) {
            // 给excel添加bom头
            $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
            foreach ($config['headerRow'] as $key => $value) {
                $config['headerRow'][$key] = (string)$value;
            }
            fputcsv($output, [$bom]);
            fputcsv($output, $config['headerRow']);
        }
        // 执行协程
        $data = (static function () use ($dataList, $callback) {
            foreach ($dataList as $k => $v) {
                yield $callback($v);
            }
        })($dataList, $callback);

        foreach ($data as $value) {
            fputcsv($output, $value);
        }
        fclose($output);
        return true;
    }
}
