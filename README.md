# 处理CSV类库 thinkcsv
csv导入,导出,读取



## 安装
> composer require yzh52521/thinkcsv


### 使用
```
    //引入 
    use yzh52521\ThinkCsv;
    
    //浏览器渲染导出csv
    $header = ['姓名', '性别', '手机号'];
    $data = [
        ['小明', '男', 17699019191],
        ['小红', '男', 17699019191],
        ['小黑', '女', 17699019191],
        ['小白', '女', 17699019191],
    ];
    //浏览器访问渲染下载
    $csv = new ThinkCsv('demo.csv',$header,$data);
    $csv->export();
    
    //后端执行,无需浏览器访问,本例文件生成在   /网站根目录/upload/demo.csv
    $csv = new ThinkCsv('upload/demo.csv',$header,$data);
    $csv->csvtoFile();
    
    //读取文件 $filepath文件路径
    $filepath = 'public/demo.csv';
    $data = ThinkCsv::readCsvData($filepath);
    
    //携程导出
    $arr = [];
        $num = 0;
        for ($i = 0; $i < 2080000; $i++) {
            $arr[] = [
                '测试1',
                '测试2',
                '测试3',
                '测试4',
            ];
            if (count($arr) === 1040000) {
                $num++;
                ThinkCsv::createMoreDataToCsvFile(
                    $arr,
                    app()->getRootPath() . 'public/',
                    '测试',
                    function ($arr) {
                        return $arr;
                    },
                    [
                        'headerRow' => [
                            '测试列数1',
                            '测试列数2',
                            '测试列数3',
                            '测试列数4',
                        ],
                        'num'       => $num,
                    ]
                );
                $arr = [];
            }
        }
```
