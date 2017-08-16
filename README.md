本文档假定你已经在本地部署好了php运行环境，同时支持yii2框架，并且具有git版本库的权限。

1、 安装yii2运行环境后修改目录权限
---
```
git clone git@github.com:DJISZ/DJIIUAV.git
cd DJIIUAV/
cd iuav/
chmod 777 runtime/
chmod 777 web/assets/
```
2、 配置文件   
---
一些敏感的配置，挪动到文件 *@app/config/.config.php*内进行配置，但是文件本身是不包含在版本库内的    
你可以在 doc/config/文件夹中找到.config.php文件，这是 *@app/config/.config.php* 的一个例子    
将例子复制到相应目录，然后更改里面的配置即可    
```
  cp doc/config/.config.php  iuav/config/.config.php
  cp doc/config/mail.php  iuav/config/mail.php 
```

3、 定时程序   
---
使用crontab定时运行，用户名是iuav

#####3.1 /data/www/DJIIUAV/iuav/check_apply.sh  校验保险是否发送给对方，对方有ip白名单限制，如果ip变动要提前告诉保险公司
```
cd /data/www/DJIIUAV/iuav
/usr/local/bin/php /data/www/DJIIUAV/iuav/yii policies/checkapply
```
#####3.2 /data/www/DJIIUAV/iuav/update_policies.sh  读取保险状态是否已经投保成功
```
cd /data/www/DJIIUAV/iuav
/usr/local/bin/php /data/www/DJIIUAV/iuav/yii policies/checkorder
```
#####3.3 /data/www/DJIIUAV/iuav/send_email_data.sh  每日8点发送日报
```
cd /data/www/DJIIUAV/iuav
/usr/local/bin/php /data/www/DJIIUAV/iuav/yii total/index
```

4、数据结构
[数据结构](https://github.com/DJISZ/DJIIUAV/blob/master/iuav/README.md)

5、mis接口说明
```
 /* 
     *  代理商信息上传接口 https://iuav.dji.com/mispi/agroagent/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter info json格式( [{"uid":"121","agentname":"aabc","staff":"负责人",code":"12121","realname":"代理商负责人","idcard":"32432313","phone":"1213223","email":"wer@qq.com","country":"cn","province":"343","city":"dsfd","address":"334"},
     * {"uid":"122","agentname":"aabc","staff":"负责人","code":"121212121","realname":"代理商负责人","idcard":"32432313","phone":"1213223","email":"we1111r@qq.com","country":"cn","province":"343","city":"dsfd","address":"334"}] )
     *  @parameter signature  签名字符串 
     *
     *  return 
     *     
     * $signature = strtoupper(hash_hmac("sha1", $datetime, $key));
    */
    public function actionAgroagent()
```


```
/* 
     *  代理商和设备id上传接口 https://iuav.dji.com/mispi/agroagentbody/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter info json格式( [{"id":1,body_code":"12121","hardware_id":"23212","agentname":"sdfdsfd","code":"1213223","email":"wer@qq.com"},
     *              {"id":2"id":1,,"body_code":"12121","hardware_id":"23212","agentname":"sdfdsfd","code":"1213223","email":"wer@qq.com"}] )
     *  @parameter signature  签名字符串 
     *
     *  return 
     *     
     * $signature = strtoupper(hash_hmac("sha1", $datetime, $key));
    */

    public function actionAgroagentbody()
```

```
 /* 
     *  农业无人机激活码上传接口 https://iuav.dji.com/mispi/sninfo/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter info json格式( [{"id":1,"body_code":"121121","hardware_id":"23212","activation":"sdfdsfd"},
     *              {"id":2,"body_code":"1212211","hardware_id":"23ad212","activation":"sdvsfdsfdsdsfdsfd"}] )
     *  @parameter signature  签名字符串 
     *
     *  return {"status":200,"sn_count":0,"data":[{"id":"1"},{"id":"2"}]}  
     *  data 里面返回添加成功的机身码
     *  `body_code`  '机身码',
     *  `hardware_id` '硬件id',
     *   `activation`  '激活码',
     *     
     * $signature = strtoupper(hash_hmac("sha1", $datetime, $key));
    */
    public function actionSninfo()
```


6.保险接口
###6.1  配置文件
```
//保险对接参数 测试
$YII_GLOBAL['THIRDPOLICIES']['url']  = 'http://*:8888/ThirdPartPlat/execute.action';
$YII_GLOBAL['THIRDPOLICIES']['USER'] = '*';
$YII_GLOBAL['THIRDPOLICIES']['PASSWORD'] = '*';
```
###6.2 发送保单人信息给保险公司
```
PoliciesController->actionIndex


```
# Management-platform
