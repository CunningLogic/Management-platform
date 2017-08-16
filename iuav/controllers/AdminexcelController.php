<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use app\models\User;
use app\models\Role;
use app\models\Rolepurview;
use app\models\Purview;
use app\models\OperationLog;
use app\models\Email;
use app\models\Agroagent;
use app\models\Agronotice;
use app\models\Agroagentmis;
use app\components\DjiAgentUser;
use app\models\Agroactiveinfo;
use app\models\Agropolicies;
use app\models\Agroreport;
use app\models\Agroreportreply;
use app\models\Agroagentbody;
use app\models\Agroapplyinfo;
use app\components\AdminUser;

class AdminexcelController extends Controller
{
    //public $enableCsrfValidation = false;

    public function init() {
        parent::init();
        //用户id
        //$userId = Yii::$app->user->getId();
      //  echo $userId."------";
      // var_dump(Yii::$app);
       // exit;
    }
    public function beforeAction($action)
    {
         parent::beforeAction($action);
         $userId = Yii::$app->user->getId();
         $adminUser = new AdminUser();
         //判断权限
         $adminUser->checklogin(__CLASS__.'::'.$action->actionMethod);
         return true;        
    }
        
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::className(),
                'rules'  => [
                    [
                        'actions' => ['adfsafdsa21321'], 
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index','downlistpolicies','downlistapply','checkpolicy','markpolicy'], 
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                           $this->redirect('/admin/');
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function jsonResponse($data)
    {
        header('Content-type: text/json');
        echo json_encode($data);exit;
    }

    //adminexcel/checkpolicy 上传保险公司列表
    public function actionCheckpolicy()
    {
        $act = $this->getPostValue("act");
        $list = $listMark = $listFind = $listNoFind = $listRepeat = array();
        if (empty($_FILES) || !is_uploaded_file($_FILES["xlsfile"]["tmp_name"])  )  
        { 
           $status = 1006;                    
           $result = array('status' => $status, 'status_msg'=>'failed','msg' => 'xlsfile is empty' );
           //die(json_encode($result)); 
        }else{
           //不能上传1M的图片
          if ($_FILES["xlsfile"]["size"] > 10485760 || $_FILES["xlsfile"]["size"] < 10) {
              $status = 1007;                   
              $result = array('status' => $status, 'status_msg'=>'failed','msg' => 'xlsfile is empty'.$_FILES["xlsfile"]["size"]  );
              die(json_encode($result)); 
          }else{
            $info = pathinfo($_FILES['xlsfile']['name']);
            $info['extension'] = strtolower($info['extension']);
            if ($info['extension'] != 'xls') {
                 $status = 1008;                
                 $result = array('status' => $status, 'status_msg'=>'failed','msg' => 'subtitletxt extension' );  
                 die(json_encode($result));               
            }else{
              $inputFileName =  $_FILES['xlsfile']['tmp_name'];
              $inputFileType = 'Excel5';
              $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
              $objPHPExcel = $objReader->load($inputFileName);        
              $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
              if ($sheetData && is_array($sheetData)) {
                array_shift($sheetData);
                $where = array();
                $fields = "id,mark";
                foreach ($sheetData as $key => $value) {
                  if (in_array($value['B'], $list)) {
                     $listRepeat[] = $value;
                     continue;
                   }
                   if ($value['B'] === 'NULL' || empty($value['B'])) {
                     continue;
                   }
                   $list[] = $value['B'];  
                   $where['order_id'] = $value['C'];
                   $where['pol_no'] = $value['B']; 
                   $listSearch = Agropolicies::getSearchWhere($where, $fields);
                   if ($listSearch && $listSearch[0]['id'] > 0) {
                      if ($listSearch[0]['mark'] == '1') {
                        $listMark[] =  $value;
                      }else{
                        $listFind[] =  $value;
                      }
                      
                   }else{
                      $listNoFind[] =  $value;
                   }
                }
              }
              unset($list);
              unset($sheetData);
              //var_dump($listRepeat,$listFind,$listNoFind);exit;

            }
          } 
        }
        $listFindCount = count($listFind);
        $listNoFindCount = count($listNoFind);
        $listMarkCount = count($listMark);
        
        $request = Yii::$app->getRequest();        
        return $this->renderSmartyTpl('checkpolicy.tpl', ['listMarkCount' => $listMarkCount,'listMark' => $listMark,'listFindCount' => $listFindCount,'listNoFindCount' => $listNoFindCount,'listRepeat' => $listRepeat,'listFind' => $listFind,'listNoFind' => $listNoFind,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);


    }

    //adminexcel/checkpolicy 上传保险公司列表
    public function actionMarkpolicy()
    {
        $list = $listMark = $listFind = $listNoFind = $listRepeat = array();
        if (empty($_FILES) || !is_uploaded_file($_FILES["xlsfile"]["tmp_name"])  )  
        { 
           $status = 1006;                    
           $result = array('status' => $status, 'status_msg'=>'failed','msg' => 'xlsfile is empty' );
           //die(json_encode($result)); 
        }else{
           //不能上传1M的图片
          if ($_FILES["xlsfile"]["size"] > 10485760 || $_FILES["xlsfile"]["size"] < 10) {
              $status = 1007;                   
              $result = array('status' => $status, 'status_msg'=>'failed','msg' => 'xlsfile is empty'.$_FILES["xlsfile"]["size"]  );
              die(json_encode($result)); 
          }else{
            $info = pathinfo($_FILES['xlsfile']['name']);
            $info['extension'] = strtolower($info['extension']);
            if ($info['extension'] != 'xls') {
                 $status = 1008;                
                 $result = array('status' => $status, 'status_msg'=>'failed','msg' => 'subtitletxt extension' );  
                 die(json_encode($result));               
            }else{
              $inputFileName =  $_FILES['xlsfile']['tmp_name'];
              $inputFileType = 'Excel5';
              $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
              $objPHPExcel = $objReader->load($inputFileName);        
              $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
              if ($sheetData && is_array($sheetData)) {
                array_shift($sheetData);
                $where = array();
                $fields = "id";
                foreach ($sheetData as $key => $value) {
                  if (in_array($value['B'], $list)) {
                     $listRepeat[] = $value;
                     continue;
                   }
                   if ($value['B'] === 'NULL' || empty($value['B'])) {
                     continue;
                   }
                   $list[] = $value['B'];  
                   $where['order_id'] = $value['C'];
                   $where['pol_no'] = $value['B']; 
                   $listSearch = Agropolicies::getSearchWhere($where, $fields);
                   if ($listSearch && $listSearch[0]['id'] > 0) {
                      $listFind[] =  $value;
                      $listMark[] = $listSearch[0]['id'];
                   }else{
                      $listNoFind[] =  $value;
                   }
                }
              }
              unset($list);
              unset($sheetData);
              //var_dump($listRepeat,$listFind,$listNoFind);exit;

            }
          } 
        }
        if ($listMark) {
          $model = array();
          $model['mark'] = 1;
          $model['id'] = $listMark;
          Agropolicies::changeStatus($model);

        }
        $listFindCount = count($listFind);
        $listNoFindCount = count($listNoFind);
        $request = Yii::$app->getRequest();        
        return $this->renderSmartyTpl('markpolicy.tpl', ['listFindCount' => $listFindCount,'listNoFindCount' => $listNoFindCount,'listRepeat' => $listRepeat,'listFind' => $listFind,'listNoFind' => $listNoFind,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);


    }
    
   
    //下载保险列表
    public function actionDownlistpolicies()
    {

        $begin = $this->getPostValue("begin");
        $end = $this->getPostValue("end");
        if (empty($begin) || empty($end)) {
            echo "请选择日期";
            exit;
        }
        $list = array();   
        $where = array('deleted'=> 0);
        if (isset($begin) && $begin) {
            $where['begin'] = trim($begin); 
        }
        if (isset($end) && $end) {
            $where['end'] = trim($end); 
        }

        $fields = "id,apply_id,order_id,query_id,pol_no,eff_tm,exp_tm,amount,premium,updated_at,query_flag";
        $list = Agropolicies::getSearchWhere($where, $fields);
        $this->exportData(explode(',', $fields),$list,$begin."~".$end.'.xlsx');
        exit;

    }

    //下载已经激活的数据
    public function actionDownlistapply()
    {

        $begin = $this->getPostValue("begin");
        $end = $this->getPostValue("end");
        if (empty($begin) || empty($end)) {
            echo "请选择日期";
            exit;
        }
        $list = array();   
        $where = array('deleted'=> 0);
        if (isset($begin) && $begin) {
            $where['begin'] = trim($begin); 
        }
        if (isset($end) && $end) {
            $where['end'] = trim($end); 
        }
        $fields = 'agro_active_info.id,agro_active_info.order_id,agro_active_info.apply_id,agro_active_info.body_code,agro_active_info.hardware_id,agro_active_info.activation,agro_apply_info.company_name,agro_apply_info.account,agro_apply_info.realname,agro_apply_info.phone';
          $fields .=',agro_active_info.agent_id,agro_active_info.upper_agent_id,agro_active_info.created_at,agro_apply_info.country,agro_apply_info.province,agro_apply_info.city,agro_apply_info.area,agro_apply_info.street,agro_apply_info.address';
          
        $data = Agroactiveinfo::getActiveWhere($where,$fields);
        foreach ($data as $key => $value) {
            $tmpValue = array();
            $tmpValue['id'] = $value['id'];
            $tmpValue['body_code'] = $value['body_code'];
            $tmpValue['company_name'] = $value['company_name']."/".$value['realname'];
            $tmpValue['phone'] = $value['phone'];            
            $tmpValue['agentname'] = Agroagent::getAgentname($value['agent_id']);
            $tmpValue['upperagentname'] = Agroagent::getAgentname($value['upper_agent_id']);
            $tmpValue['country'] = $value['country'];
            $tmpValue['province'] = $value['province'];
            $tmpValue['city'] = $value['city'];
            $tmpValue['address'] = $value['area'].$value['street'].$value['address'];            
            $tmpValue['created_date'] = substr($value['created_at'], 0,10);
            $tmpValue['created_time'] = substr($value['created_at'], 11);

            $list[] =$tmpValue;
              
        }
        $fields = 'id,整机序列,公司/用户,手机号,代理名称,上级代理名称,国家,省份,城市,地址,日期,时间';
        $this->exportData(explode(',', $fields),$list, "apply".$begin."~".$end.'.xlsx');
        exit;

    }


    protected function exportData($firstList,$list,$filename='iuav.xlsx')
    {
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                             ->setLastModifiedBy("Maarten Balliauw")
                             ->setTitle("Office 2007 XLSX Test Document")
                             ->setSubject("Office 2007 XLSX Test Document")
                             ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Test result file");  

        $t = 1;
        $n = ord ( "A" ); // 表示小写字母A的ASCLL码(阿斯克码);
        foreach ( $firstList as $k => $rs ) {
            $num = $k + 2;
            // 写出Excel表A1,B1,C1...如此类推的值;
            $objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( chr ( $n ) . "$t", "$rs" );
            // echo chr($n)."$t <br />";
            $n ++;
        }

        $t = 2;
        foreach ( $list as $vvv ) {
            $n = ord ( "A" ); // 表示小写字母A的ASCLL码(阿斯克码);
            foreach ( $vvv as $k => $rs ) {
                $num = $k + 2;
                // 写出Excel表A2,B2,C2...A3,B3,C3....如此类推的值;
                $objPHPExcel->setActiveSheetIndex ( 0 )->setCellValue ( chr ( $n ) . "$t", "$rs" );
                // echo chr($n)."$t <br />";
                $n ++;
            }
            $t ++;
        }       
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
      
    } 


    protected function renderSmartyTpl($tpl,$list)
    {
        $adminUser = new AdminUser();
        $rolePurvieData = $adminUser->getRolePurvie();
        $list['headerrolePurvieData'] = $rolePurvieData;
        $list['isGuest'] = Yii::$app->user->isGuest;
        if (!$list['isGuest']) {
           $list['sessionUserName'] =  Yii::$app->user->identity->username;
        }
        if (!isset($list['headtitle'])) {
           $list['headtitle'] =  '农业无人机内部系统';
        }   
          
        return $this->renderPartial($tpl,$list);
    }
     //优先读取post，然后get
    protected function getPostValue($key) {
       $value = Yii::$app->request->post($key);
       return $value ? $value : Yii::$app->request->get($key); 
    }
    // 写入文件
    protected function add_log($msg, $type = 'friday')
    {
        $ip = $this->get_client_ip();
        $logfile = __DIR__.'/../runtime/logs/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = $_SERVER["SERVER_ADDR"];
        file_put_contents($logfile, date('Y/m/d H:i:s').":  $msg >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR \r\n", FILE_APPEND);
    }

    protected function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }  

    
    
}
