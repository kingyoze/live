<?php

/**
 * Class MyAccountController
 * @author Demi 992392919@qq.com
 */
class MyAccountController extends BaseController
{
    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);
        $this->session = Yii::app()->session;

        //管理员模拟用户登陆
        if ($customer = $this->checkAdminId()) {
            $identify = new CustomerIdentity();
            $identify->assignCustomer($customer);
            Yii::app()->user->login($identify);
        }
        if (Yii::app()->user->isGuest) Yii::app()->user->loginRequired();
        $this->layout = 'sign_layout';
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/account.css');
    }

    public function actionIndex()
    {
        Yii::app()->session['subtime'];
        $customer_id = Yii::app()->user->id;
        $userInfo = Customer::model()->findByPk($customer_id);
        if ($userInfo->customer_type == 2) {
            $this->render('star/index', array('userInfo' => $userInfo));
        } else {
            $this->render('index', array('userInfo' => $userInfo));
        }
    }

    public function actionGold()
    {
        $this->render('gold');
    }

    public function actionModify()
    {
        $this->render('modifyAccount');
    }

    public function actionmydata()
    {
        $this->render('index');
    }

    public function actionmypassword()
    {
        $this->render('mypassword');
    }

    public function actionEditInfo()
    {
        $customer_id = Yii::app()->request->getParam('customer_id');
        $customer = Customer::model()->findByPk($customer_id);
        $customer->email = Yii::app()->request->getParam('email');
        $customer->user_name = Yii::app()->request->getParam('user_name');
        $customer->nick_name = Yii::app()->request->getParam('nick_name');
        $customer->gender = Yii::app()->request->getParam('gender');
        $customer->face = Yii::app()->request->getParam('face');
        if ($customer->save()) {
            echo CJSON::encode(array('ok' => true));
            Yii::app()->end();
        } else {
            echo CJSON::encode(array('ok' => true, 'message' => '保持失败，请联系管理员'));
            Yii::app()->end();
        }

    }

    private function checkAdminId()
    {
        $adminId = Yii::app()->request->getParam('p');
        $arr = explode('_', $adminId);
        if ($arr[1] == Yii::app()->params['admin']) {
            $customer_id = $arr[3];
            return Customer::model()->findByPk($customer_id);
        } else {
            return false;
        }
    }

    /**
     * 以下是明星管理相关
     */
    public function ActionEditStar()
    {
        $customer_id = Yii::app()->request->getParam('customer_id');
        $file = $_FILES['faces'];
        try{
            for ($i = 0; $i < count($file['tmp_name']); $i++) {
                if (!$file['tmp_name'][$i]) continue;
                $content = fopen($file['tmp_name'][$i], 'r');
                $extName = Yii::app()->aliyun->getExtName($file['name'][$i]);
                $key = Yii::app()->aliyun->savePath . '/' . md5_file($file['tmp_name'][$i]) . '.' . $extName;
                $size = $file['size'][$i];
                Yii::app()->aliyun->putResourceObject($key, $content, $size);
                $_POST['relation_star'][$i]['face'] = Yii::app()->params['cdnUrl'] . '/' . $key;
            }
            $relation_star = CJSON::encode($_POST['relation_star']);
            $customer = Customer::model()->findByPk($customer_id);
            $customerInfo = CustomerInfo::model()->findByPk($customer_id);
            $customer->face = Yii::app()->request->getParam('face');
            $customerInfo->content = Yii::app()->request->getParam('content');
            $customerInfo->birthday = Yii::app()->request->getParam('birthday');
            $customerInfo->address1 = Yii::app()->request->getParam('address1');
            $customerInfo->height = Yii::app()->request->getParam('height');
            $customerInfo->weight = Yii::app()->request->getParam('weight');
            $customerInfo->occupation = Yii::app()->request->getParam('occupation');
            $customerInfo->tag = Yii::app()->request->getParam('tag');
            $customerInfo->relation_star = $relation_star;
            $customer->save();
            $customerInfo->save();
            $this->redirect($this->createUrl('/myAccount'));
        }catch(Exception $e){
            $this->redirect($this->createUrl('/myAccount'));
            $e->getMessage();
            return false;
        }
    }
    public function actionNews()
    {
        $this->render('star/store');
    }

    public function actionPub()
    {
        $this->render('star/publish_news');
    }

    public function actionStore()
    {
        $this->render('star/store');
    }

    public function actionEvaluation()
    {
        $this->render('star/evaluation');
    }
}