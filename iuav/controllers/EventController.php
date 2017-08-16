<?php

namespace app\controllers;

use Yii;
use app\components\DjiController;


class EventController extends DjiController
{

    private static $country_array = [
        'at',
        'au',
        'be',
        'bg',
        'ca',
        'ch',
        'cn',
        'cy',
        'cz',
        'de',
        'dk',
        'ee',
        'es',
        'fi',
        'fr',
        'gb',
        'gr',
        'hk',
        'hr',
        'hu',
        'ie',
        'it',
        'jp',
        'li',
        'kr',
        'lt',
        'lu',
        'lv',
        'mc',
        'mo',
        'mt',
        'my',
        'nl',
        'nz',
        'no',
        'pl',
        'pt',
        'ro',
        'sg',
        'sk',
        'se',
        'si',
        'tw',
        'us'
    ];


    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /***
     * 圣诞活动不同国家链接跳转
     */
    public function actionXmas($country = 'cn')
    {
        $country = strtolower($country);

        if (!in_array($country, $this::$country_array)) {
            return $this->redirect('http://www.dji.com/404');
        }

        $cookies = Yii::$app->response->cookies;
        // 在要发送的响应中添加一个新的cookie
        $cookies->add(new \yii\web\Cookie([
            'name'      => 'country',
            'value'     => strtoupper($country),
            'expire'    => time() + 365 * 24 * 3600,
            'path'      => '/',
            'domain'    => '.dji.com',
            'httpOnly'  => false,
        ]));

        $url = '/event/xmas/';

        if (Yii::$app->request->queryString != '') {
            $url .= '?' . Yii::$app->request->queryString;
        }

        return $this->redirect($url);
    }

    // 10 years event
    public function action10years($country)
    {
        $country = strtolower($country);

        if (!in_array($country, $this::$country_array) && $country != 'en') {
            return $this->redirect('http://www.dji.com/404');
        }

        $cookies = Yii::$app->response->cookies;
        // 在要发送的响应中添加一个新的cookie
        $cookies->add(new \yii\web\Cookie([
            'name'      => 'event_country',
            'value'     => $country,
            'expire'    => time() + 365 * 24 * 3600,
            'path'      => '/',
            'domain'    => '.dji.com',
            'httpOnly'  => false,
        ]));

        $url = '/event/10years/';

        if (Yii::$app->request->queryString != '') {
            $url .= '?' . Yii::$app->request->queryString;
        }

        return $this->redirect($url);
    }

    // new year
    public function actionNewyear($country = 'cn')
    {

        $country_array = [
            'cn', 'hk', 'mo', 'tw', 'sg', 'my'
        ];

        $country = strtolower($country);

        if (!in_array($country, $country_array)) {
            return $this->redirect('http://store.dji.com/');
        }

        $cookies = Yii::$app->response->cookies;
        // 在要发送的响应中添加一个新的cookie
        $cookies->add(new \yii\web\Cookie([
            'name'      => 'event_country',
            'value'     => $country,
            'expire'    => time() + 365 * 24 * 3600,
            'path'      => '/',
            'domain'    => '.dji.com',
            'httpOnly'  => false,
        ]));

        $url = '/event/newyear/';

        if (Yii::$app->request->queryString != '') {
            $url .= '?' . Yii::$app->request->queryString;
        }

        return $this->redirect($url);
    }
}
