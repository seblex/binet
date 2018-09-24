<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\User;
use app\models\SignupForm;
use yii\db\Exception;

/**
 * Отображение страниц сайта
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays cabinet page.
     *
     * @return string
     */
    public function actionAbout()
    {
        //Родительский юзер
        $referal = User::find()->where(['id' => Yii::$app->user->identity->getParentId()])->one();
        $ref_email = '';
        if ($referal) {
            $ref_email = $referal->email;
        }
        //Приглашённые юзеры
        $children = User::find()->where(['parent_id' => Yii::$app->user->identity->getId()])->all();
        $ch = [];
        //Собираем массив, ибо передавать хэши паролей и прочую конфиденциальщину не камильфо
        foreach ($children as $child) {
            $ch[] = $child->email;
        }

        return $this->render('about', [
            'referal_link' => Yii::$app->params['domen'] . '?referal=' . Yii::$app->user->identity->getReferalLink(),
            'ref_email' => $ref_email,
            'children' => $ch
        ]);
    }

    /**
     * Регистрация
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if (Yii::$app->request->isGet ) {
            try {
                $p = [
                    'name' => '',
                    'id' => 0
                ];
                $query = Yii::$app->request->get();
                if (isset($query['referal'])) {
                    $parent = User::find()->where(['referance_link' => $query['referal']])->one();
                    $p['name'] = $parent->email;
                    $p['id'] = $parent->id;
                }

                return $this->render('signup', [
                    'model' => $model,
                    'parent' => $p
                ]);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        } else if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

    }

}
