<?php
/**
*  记录参数不符合规则
*/
class esapiowasp
{
    protected function isQueryString($reqstr)
    {
        $pattern = "^[a-zA-Z0-9!#@{}\[\]()\-=\*\.\?;,+\/:&amp;_ ]{1,150}$";
        if (is_array($reqstr)) {
           return true;
        }else if ($reqstr && ! preg_match("/{$pattern}/", $reqstr)) {
           return false;
        }else{
           return true;
        }
    }
    protected function isQuerykey($reqstr)
    {
        $pattern = "^[a-zA-Z0-9\-_]{1,32}$";
        if (is_array($reqstr)) {
           return true;
        }else if ($reqstr && ! preg_match("/{$pattern}/", $reqstr)) {
           return false;
        }else{
           return true;
        }
    }
    public function getParameterMap($pagename = 'index',$isstop = 0,$type='all')
    { 
        $get_str = "REQUEST=".json_encode($_REQUEST);
        $tmp_server = $_SERVER;
        $tmp_server['MYSQL_HOST'] = '';
        $tmp_server['MYSQL_PASSWORD'] = '';
        $get_str .= "&SERVER=".json_encode($tmp_server);
        $jumplist = array('Filename','message','extra','unused','attachnew','attachupdate','submit','confirmed','editsubmit','subject','noticetrimstr','pollanswers','keyword','noticeauthormsg');
        $jumplist[] = 'moderate';
        $jumplist[] = 'aids';
        
         // self::add_log($get_str, $pagename);
        if ($type== 'all' || $type== 'post') {
            foreach ($_POST as $unsafePname => $unsafePvalue) {
               if ( in_array($unsafePname, $jumplist ) ) {
                  continue;
               }
               if (!self::isQuerykey($unsafePname)) {
                   self::add_log($get_str."&unsafePname=".$unsafePname."&error=postkey&status=9999", $pagename."_post");
                   if ($isstop == 1) {
                       echo "key error";exit;
                   }
                   //echo "key error";exit;
                   //$_GET[$unsafePname] = '';
               }
               if (!self::isQueryString($unsafePvalue)) {
                   self::add_log($get_str."&unsafePname=".$unsafePname."&error=postvalue&status=9998", $pagename."_post");
                   if ($isstop == 1) {
                       echo "key error";exit;
                   }
                   //$_GET[$unsafePname] = '';
               }
            }
        }
        if ($type== 'all' || $type== 'get') {
          foreach ($_GET as $unsafePname => $unsafePvalue) {
           if ( in_array($unsafePname, $jumplist ) ) {
                  continue;
           }
           if (!self::isQuerykey($unsafePname)) {
               self::add_log($get_str."&unsafePname=".$unsafePname."&error=getkey&status=9997", $pagename."_get");
               if ($isstop == 1) {
                   echo "key error";exit;
               }
               //$_GET[$unsafePname] = '';
           }
           if (!self::isQueryString($unsafePvalue)) {
               self::add_log($get_str."&unsafePname=".$unsafePname."&error=getvalue&status=9996", $pagename."_get");
               if ($isstop == 1) {
                   echo "key error";exit;
               }
               // $_GET[$unsafePname] = '';
           }
         } 
        }

               
    }
    // 写入文件
    protected function add_log($msg, $type = 'site_login')
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $logdir = __DIR__.'/../../runtime/logs';       
        if( !is_dir($logdir) )
        {
            return false;
        }
        $logfile = $logdir.'/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = $_SERVER["SERVER_ADDR"];
        file_put_contents($logfile, date('Y/m/d H:i:s',time())." $msg >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR \r\n", FILE_APPEND);
    }
 }   


 
