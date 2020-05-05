<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

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
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSearch() {
        $requestParams = Yii::$app->request->getQueryParams();
        $bookName   = $requestParams['bookName'];
//        $rootName   = $this->getRoot($bookName);
//        $nodeName   = $this->getNode($bookName);
        $strNodeInfo = $this->getNode($bookName);
        if (empty($strNodeInfo)) {
            return $this->makeRerurn([]);
        }

        $arrNodeInfo = explode(':', $strNodeInfo);
        $rootName = $arrNodeInfo[0];
        $nodeName = $arrNodeInfo[1];
        $tree       = $this->getTree($rootName, $nodeName);
        return $this->makeRerurn($tree);
    }

    public function actionGetdetail() {
        $requesParams = Yii::$app->request->getQueryParams();
        $bookId = $requesParams['bookId'];

        return $this->makeRerurn($this->getBook($bookId));

    }

    private function makeRerurn($data, $errNo = 0, $errstr = 'OK') {
        $arrOutput = array(
            'errNo'     =>  $errNo,
            'errstr'    =>  $errstr,
            'data'      =>  $data,
        );
        return json_encode($arrOutput);
    }

    private function getNode($bookName) {
        $arrMap = array(
            'bookname1001'   =>  'root1:node2',
            'bookname1002'   =>  'root1:node2',
            'bookname1003'   =>  'root1:node3',
            'bookname2001'   =>  'root2:node5',
            'bookname2002'   =>  'root2:node5',
            'bookname2003'   =>  'root2:node6',
        );

        if (key_exists($bookName, $arrMap)) {
            return $arrMap[$bookName];
        }
        else {
            return '';
        }
    }

    private function getBookList($nodeId) {
        $arrMap = array(
            2   =>  [
                array(
                    'name'  =>  'bookname1001',
                ),
                array(
                    'name'  =>  'bookname1002',
                ),
                array(
                    'name'  =>  'bookname1003',
                ),
            ],
            5   =>  [
                array(
                    'name'  =>  'bookname2001',
                ),
                array(
                    'name'  =>  'bookname2002',
                ),
                array(
                    'name'  =>  'bookname2003',
                ),
            ],
        );

        if (key_exists($nodeId, $arrMap)) {
            return $arrMap[$nodeId];
        }
        else {
            return [];
        }

    }

    private function getBook($bookId) {
        $arrMap = array(
            1001   =>  array(
                'nodeId'    =>  2,
                'name'      =>  'bookname1001',
                'imgUrl'    =>  'http://www.xxx.com/xxxx.jpg',
                'prize'     =>  '50',
                'jumpUrl'   =>  'http://www.xxx.com/xxxx.jpg'
            ),
            1002   =>  array(
                'nodeId'    =>  2,
                'name'      =>  'bookname1002',
                'imgUrl'    =>  'http://www.xxx.com/xxxx.jpg',
                'prize'     =>  '50',
                'jumpUrl'   =>  'http://www.xxx.com/xxxx.jpg'
            ),
        );
        return $arrMap[$bookId];
    }

    private function getTree($rootName, $nodeName = '') {
        $arrMap = array(
            'root1' =>  array(
                array(
                    'id'=>1,
                    'name'=>'node1',
                    'parent_id'=>0,
                    'level'=>0 //一级分类
                ),
                array(
                    'id'=>2,
                    'name'=>'node2',
                    'parent_id'=>1,
                    'level'=>1 //二级分类
                ),
            ),
            'root2' =>  array(
                array(
                    'id'=>4,
                    'name'=>'node4',
                    'parent_id'=>0,
                    'level'=>0 //一级分类
                ),
                array(
                    'id'=>5,
                    'name'=>'node5',
                    'parent_id'=>4,
                    'level'=>1 //二级分类
                ),
                array(
                    'id'=>6,
                    'name'=>'node6',
                    'parent_id'=>5,
                    'level'=>2 //三级分类
                )

            ),
        );

        $arrTree = $arrMap['root1'];
        if (key_exists($rootName, $arrMap)) {
            $arrTree = $arrMap[$rootName];
        }

        $arrTreeNew = [];
        foreach ($arrTree as $item) {
            if ($item['name'] == $nodeName) {
                $item['searched'] = $this->getBookList($item['id']);
            }
            else {
                $item['searched'] = [];
            }
            $arrTreeNew[] = $item;
        }

        return $arrTreeNew;
    }
}
