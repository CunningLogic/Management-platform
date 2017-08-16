<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use app\models\Area;
use app\models\DraftArea;
use app\models\OperationLog;
use app\models\Email;

class DraftController extends Controller
{
	public $enableCsrfValidation = false;
		
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::className(),
                'rules'  => [
                    [
						'actions' => ['using'], 
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
						'actions' => ['index','beta','release','disable','using','add','edit','del','view','submit','garbage','audit','destroy'], 
                        'allow' => true,
                        'roles' => ['@'],
						'denyCallback' => function ($rule, $action) {
							$this->redirect('/site/login');
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
	
	public function getLimit()
	{
		$page = Yii::$app->request->get('page', 1);
		$size = Yii::$app->request->get('size', 20);
		$start = ($page - 1) * $size;
		if ( $start < 0 ) $start = 0;
		
		return [$start, $size];
	}

    public function actionIndex()
    {
		list($start, $limit) = $this->getLimit();
		
		$orderby = Yii::$app->request->get('orderby', 'id');
		$sort    = Yii::$app->request->get('sort', 1);
		
		$where = [
			['user_id', '=', Yii::$app->user->identity->id],
			['status', '!=', DraftArea::DELETED]
		];
		
		$audit = false;
		$role  = Yii::$app->user->identity->role;
		if ( $role == 'admin' or $role == 'godmode' ) {
			$audit = true;
		}
		
		$view = 'index';
		if ( Yii::$app->request->isAjax ) {
			$view  = 'list';
			$this->layout = false;
		}
		
		return $this->render($view, [
			'audit' => $audit,
			'rows'  => DraftArea::getAndWhere($where, $start, $limit, $orderby, $sort),
			'sort'  => $sort,
			'orderby' => $orderby
		]);
    }
	
	public function actionUsing()
	{
		$this->layout = '@app/views/layouts/map.php';
		return $this->render('using');
	}

    public function actionView()
    {
		$id = Yii::$app->request->get('id', 0);
		
		if ( Yii::$app->request->isAjax ) {
			$data = (new \yii\db\Query())
				->select('*')
				->from(DraftArea::tableName())
				->where(['id' => $id])
				->one();
			$this->jsonResponse($data);
		}
			
		$m = DraftArea::findOne(['id' => $id]);
		if ( !$m ) {
			$this->redirect('/?r=draft');
		}
		
		$view = Yii::$app->request->get('view', 'map');
		if ( $view == 'map' ) {
			$this->layout = '@app/views/layouts/map.php';
			return $this->render('view_in_map', ['model' => $m]);
		} else {
			return $this->render('view', ['model' => $m]);
		}
    }
	
	public function actionAdd()
	{
		$post = Yii::$app->request->post();
		if ( empty($post) ) {
			$view = Yii::$app->request->get('view', 'map');
			if ( $view == 'map' ) {
				$this->layout = '@app/views/layouts/map.php';
				return $this->render('add_in_map');
			} else {
				return $this->render('add');
			}
		}
		
		$status = DraftArea::add($post, $msg);
		if ( $status == 0 ) {
			OperationLog::write('add', DraftArea::tableName(), $post['id']);
		}
		
		if ( Yii::$app->request->isAjax ) {
			$this->jsonResponse(['status' => 0, 'status_msg' => 'success']);
		}
		
		$this->redirect('/?r=draft');
	}
	
	public function actionEdit()
	{
		$post = Yii::$app->request->post();
		if ( empty($post) ) {
			$user_id = Yii::$app->user->identity->id;
			$id = Yii::$app->request->get('id', 0);
			$m = DraftArea::findOne(['user_id' => $user_id, 'id' => $id]);
			if ( !$m ) {
				$this->redirect('/?r=draft');
			}
			
			$view = Yii::$app->request->get('view', 'map');
			if ( $view == 'map' ) {
				$this->layout = '@app/views/layouts/map.php';
				return $this->render('edit_in_map', ['model' => $m]);
			} else {
				return $this->render('edit', ['model' => $m]);
			}
		}
		
		$status = DraftArea::edit($post, $msg);
		if ( $status == 0 ) {
			OperationLog::write('edit', DraftArea::tableName(), $post['id']);
		}
		
		if ( Yii::$app->request->isAjax ) {
			$this->jsonResponse(['status' => $status, 'status_msg' => $msg]);
		}
		
		$this->redirect('/?r=draft');
	}
	
	public function actionSubmit()
	{
		$post = Yii::$app->request->post();
		$status = DraftArea::submit($post, $model, $msg);
		if ( $status == 0 ) {
			OperationLog::write('submit', DraftArea::tableName(), $post['id']);
		}
		
		if ( Yii::$app->request->isAjax ) {
			$this->jsonResponse(['status' => $status, 'status_msg' => $msg]);
		}
		
		$this->redirect('/?r=draft');
	}
	
	public function actionAudit()
	{
		$role = Yii::$app->user->identity->role;
		
		$post = Yii::$app->request->post();
		if ( empty($post) ) {
			if ( $role != 'admin' and $role != 'godmode' ) {
				return $this->render('permission_deny');
			}
			
			$id = Yii::$app->request->get('id');
			$m = DraftArea::findOne(['id' => $id]);
			if ( !$m ) {
				$this->redirect('/?r=draft');
			}
			
			$view = Yii::$app->request->get('view', 'map');
			if ( $view == 'map' ) {
				$this->layout = '@app/views/layouts/map.php';
				return $this->render('audit_in_map', ['model' => $m]);
			} else {
				return $this->render('audit', ['model' => $m]);
			}
		}
		//echo '<pre>';print_r($post);echo '</pre>';exit;
		if ( $role != 'admin' and $role != 'godmode' ) {
			$this->jsonResponse(['status' => 403, 'status_msg' => 'permission deny']);
		}
		
		$transaction = Yii::$app->db->beginTransaction();
		
		try {
			$status = DraftArea::audit($post, $model, $msg);
			if ( $status == 0 ) {
				if ( $model->status == DraftArea::APPROVE ) {
					OperationLog::write('approve', DraftArea::tableName(), $post['id']);
					Area::sync($model);
					//Email::send(yii::$app->user->identity->username, 'audit limit flight area result', 'has be approve.');
				} else {
					OperationLog::write('reject',  DraftArea::tableName(), $post['id']);
					//Email::send(yii::$app->user->identity->username, 'audit limit flight area result', 'has be reject.');
				}
			}
		} catch ( exception $e ) {
			$transaction->rollback();
			if ( Yii::$app->request->isAjax ) {
				$this->jsonResponse(['status' => 500, 'status_msg' => $e->getMessage()]);
			} else {
				return $this->render('error', ['errmsg' => $e->getMessage()]);
			}
		}
		
		$transaction->commit();
		
		if ( Yii::$app->request->isAjax ) {
			$this->jsonResponse(['status' => $status, 'status_msg' => $msg]);
		}
		
		$this->redirect('/?r=release/pending');
	}
	
	public function actionDel()
	{
		$id = Yii::$app->request->post('id', 0);
		if ( $id == 0 ) {
			$id = Yii::$app->request->get('id', 0);
		}
		
		$status = DraftArea::del($id, $msg);
		if ( $status == 0 ) {
			OperationLog::write('delete', DraftArea::tableName(), $id);
		}
		
		if ( Yii::$app->request->isAjax ) {
			$this->jsonResponse(['status' => $status, 'status_msg' => $msg]);
		}
		
		$this->redirect('/?r=draft');
	}

    public function actionGarbage()
    {
		list($start, $limit) = $this->getLimit();
		
		$orderby = Yii::$app->request->get('orderby', 'id');
		$sort    = Yii::$app->request->get('sort', 1);
		
		$user_id = Yii::$app->user->identity->id;
		
		return $this->render('garbage', [
			'rows'  => DraftArea::getAndEqualWhere(['status' => DraftArea::DELETED, 'user_id' => $user_id], $start, $limit, $orderby, $sort),
		]);
    }
	
	public function actionDestroy()
	{
		$role = Yii::$app->user->identity->role;
		
		$post = Yii::$app->request->post();
		if ( empty($post) ) {
			if ( $role != 'admin' and $role != 'godmode' ) {
				return $this->render('permission_deny');
			}
			
			$id = Yii::$app->request->get('id');
			$m = DraftArea::findOne(['id' => $id]);
			if ( !$m ) {
				$this->redirect('/?r=draft');
			}
			return $this->render('destroy', ['model' => $m]);
		}
		//echo '<pre>';print_r($post);echo '</pre>';exit;
		if ( $role != 'admin' or $role == 'godmode' ) {
			$this->jsonResponse(['status' => 403, 'status_msg' => 'permission deny']);
		}
		
		$status  = DraftArea::destroy($post, $msg, $allow);
		if ( $status == 0 ) {
			if ( $allow ) {
				OperationLog::write('destroy', DraftArea::tableName(), $post['id']);
				//Email::send(yii::$app->user->identity->email, 'audit limit place result', 'has be allow.')
			} else {
				//Email::send(yii::$app->user->identity->email, 'audit limit place result', 'has be deny.')
			}
		}

		if ( Yii::$app->request->isAjax ) {
			$this->jsonResponse(['status' => $status, 'status_msg' => $msg]);
		}
		
		$this->redirect('/?r=draft/garbage');
	}
}
