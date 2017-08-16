<?php
/***
 * 错误处理
 */
namespace app\components;

use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\base\UserException;
use yii\base\ErrorException;

use yii\web\HttpException;

/*
 * @author wangyeshen <yeshen.wang@gmail.com>
 * $time 2015/12/19
 */
class ErrorAction extends Action
{
    /**
     * @var string the view file to be rendered. If not set, it will take the value of [[id]].
     * That means, if you name the action as "error" in "SiteController", then the view name
     * would be "error", and the corresponding view file would be "views/site/error.php".
     */
    public $view;
    /**
     * @var string the name of the error when the exception name cannot be determined.
     * Defaults to "Error".
     */
    public $defaultName;
    /**
     * @var string the message to be displayed when the exception message contains sensitive information.
     * Defaults to "An internal server error occurred.".
     */
    public $defaultMessage;


    /**
     * Runs the action
     *
     * @return string result content
     */
    public function run()
    {
        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            $exception = new HttpException(404, Yii::t('yii', 'Page not found.'));
        }

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }
        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = $this->defaultName ?: Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            $message = $this->defaultMessage ?: Yii::t('yii', 'An internal server error occurred.');
        }

        if ($code == 404) {
            return $this->controller->redirect('http://www.dji.com/404');
        } else {
            $data = [
                'name' => $name,
                'message' => $message,
                'exception' => $exception,
            ];

            if ($exception instanceof ErrorException) {
            /**发送邮件给管理员**/
                $errorData = $data;
                //错误信息，发送报警邮件的时候显示真实错误信息，但是页面不显示，所以需要这里赋值
                $errorData['message'] = $exception->getMessage();
                $this->sendEmail($errorData);
            }

            if (Yii::$app->getRequest()->getIsAjax()) {
                return "$name: $message";
            } else {
                return $this->controller->render($this->view ?: $this->id, $data);
            }
        }
    }

    public function sendEmail($data)
    {
        $app = Yii::$app;
        $request = $app->request;
        $subject = "页面错误报警" . date('Y-m-d H:i:s');

        $htmlBody = 'url : ' . $request->getAbsoluteUrl() . '<br>';
        $htmlBody .= 'name :' . $data['name'] . '<br>';
        $htmlBody .= 'message：' . $data['message'] . '<br>';

        $emails = $app->params['adminEmail'];
        if ($emails != '') {
            $emails = explode(',', $emails);
            foreach($emails as $email) {
                $app->mailer->compose()
                    ->setTo($email)
                    ->setSubject($subject)
                    ->setHtmlBody($htmlBody)
                    ->send();
            }
        }
    }
}
