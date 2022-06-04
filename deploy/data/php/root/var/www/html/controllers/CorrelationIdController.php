<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class CorrelationIdController extends Controller
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->response->format = \yii\web\Response::FORMAT_RAW;

        return print_r($this->request->getHeaders()->get('X-Request-Id'), true);
    }
}
