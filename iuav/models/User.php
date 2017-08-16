<?php
namespace app\models;

use Yii;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    public static function add($model)
    {
        $release = new User;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;       
        $release->username = strip_tags($model['username']);
        $release->password = strip_tags($model['password']);
        $release->email = strip_tags($model['email']);
        $release->phone = strip_tags($model['phone']);
        $release->authKey = strip_tags($model['authKey']);
        $release->accessToken = strip_tags($model['accessToken']);
       // $release->role = $model['role']; 
        $release->upper_agent_id = $model['upper_agent_id'];  
        $release->remark = $model['remark']; 
        $release->google_auth = strip_tags($model['google_auth']);
        //$release->longitude = $model['longitude'];
        //$release->latitude = $model['latitude'];
        
        $release->updated_at = $now_time;       
        $release->save();
        return $release->id;
    } 
    public static function updateInfo($model)
    {
        $release = User::findOne(['id' => $model['id']]);
        $release->username = $model['username'];
        if ($model['password']) {
           $release->password = $model['password'];
           $release->authKey = strval($model['authKey']);
        }        
        $release->upper_agent_id = strip_tags($model['upper_agent_id']);
        $release->remark = strip_tags($model['remark']);
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }  

    public static function updatePasswordInfo($model)
    {
        $release = User::findOne(['id' => $model['id'],'username' => $model['username'] ]);
        $release->password = $model['password'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 
    
    public static function updateRoleidInfo($model)
    {
        $release = User::findOne(['id' => $model['id']]);
        $release->role_id = $model['role_id'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }    





    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 100],
            [['authKey'], 'string', 'max' => 100],
            [['accessToken'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'authKey'  => 'AuthKey',
            'accessToken' => 'AccessToken',
            'role'  => 'Role'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
        //return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
        /*foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;*/
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
          $user = User::find()
            ->where(['username' => $username])
            ->asArray()
            ->one();

            if($user){
            return new static($user);
        }

        return null;
        /*foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;*/
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {

        //echo $password;exit;
        $password = md5($this->getAuthKey().$password);
      
        //$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
        //var_dump($password,$hash,Yii::$app->getSecurity()->validatePassword($password, $this->password));exit;       

        //echo $password;exit;
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }
    
    public static function get($start, $limit, $where = [])
    {
        return (new \yii\db\Query())
            ->select('*')
            ->from('users')
            ->where($where)
            ->orderBy(['id' => SORT_DESC])
            ->offset($start)
            ->limit($limit)
            ->all();
    }
    
    public static function assign($id, $role, &$errmsg)
    {
        $errmsg = 'success';
        
        $m = User::findOne($id);
        if ( !$m ) {
            $errmsg = 'user not found';
            return Err_Data_Notfound;
        }
        
        $m->role = $role;
        $m->save();
        
        return 0;
    }
    
}