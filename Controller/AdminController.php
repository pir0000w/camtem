<?php

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminController
{
    private $app = null;

    const TYP_STR = 1;
    const TYP_INT = 2;
    const TYP_DATE = 3;
    const TYP_BOOL = 4;
    
    public function __construct(Container $app) {
        $this->app = $app;
    }
    
    public function getIndex(Request $request, Response $response)
    {
        // if (!array_key_exists('username', $_SESSION)) {
        if(!isset($_SESSION['username'])) {
            // return $response->withRedirect('/admin/login', 301);
            return $response->withRedirect('/admin/login');
        }
        return $this->app->view->render($response, 'admin/index.phtml');
    }
    
    public function getLogin(Request $request, Response $response)
    {
        return $this->app->view->render($response, 'admin/login.phtml');
    }
    
    public function postLogin(Request $request, Response $response)
    {
        $username = $request->getParsedBodyParam('username');
        $password = $request->getParsedBodyParam('password');
        
        // if ($input['username'] === NULL || $input['password'] === NULL || $input['username'] === '' || $input['password'] === '') {
        if ($username === NULL || $password === NULL || $username === '' || $password === '') {
            // いずれか未入力の場合エラー
            $errorMessage = "未入力の項目があります";
            return $this->app->view->render($response, 'admin/login.phtml', ['errorMessage' =>$errorMessage]);
        }
        
        $sql = "SELECT * FROM users WHERE username = :username";
        $sth = $this->app->db->prepare($sql);
        $sth->bindParam("username", $username);
        $sth->execute();
        $user = $sth->fetchObject();
        
        if (!$user) {
            throw new \Exception('could not save the user');
        }
        
        // verify username.
        if(!$user) {
            $errorMessage = "メールアドレスまたはパスワードが正しくありません。";
            return $this->app->view->render($response, 'admin/login.phtml', ['errorMessage' =>$errorMessage]);
        }
        // verify password.
        if (!password_verify($password, $user->password)) {
            $errorMessage = "メールアドレスまたはパスワードが正しくありません。";
            return $this->app->view->render($response, 'admin/login.phtml', ['errorMessage' =>$errorMessage]);
        }
        
        session_regenerate_id(true); 
        $_SESSION['username'] = $user->username;
        
        return $this->app->view->render($response, 'admin/index.phtml');
        // return $response->withRedirect('/admin');
    }
    
    public function getLogout(Request $request, Response $response)
    {
        $_SESSION = array();
        session_destroy();
        return $this->app->view->render($response, 'admin/logout.phtml');   
    }
    
    public function getManage(Request $request, Response $response)
    {
        $name = $request->getAttribute('name');
        $response->getBody()->write("admin/manage");
        return $response;
    }
    
    public function getRegist(Request $request, Response $response)
    {
        return $this->app->view->render($response, 'admin/regist.phtml');
    }
    
    public function postRegist(Request $request, Response $response)
    {
        $username = $request->getParsedBodyParam('username');
        $password = $request->getParsedBodyParam('password');
        if ($username === NULL || $password === NULL || $username === '' || $password === '') {
            // いずれか未入力の場合エラー
            $errorMessage = "未入力の項目があります";
            return $this->app->view->render($response, 'admin/regist.phtml', ['errorMessage' =>$errorMessage]);
        }
        
        // DB登録
        $sql = "INSERT INTO users (username, password, role, created_at, updated_at) 
                VALUES (:username, :password, :role,:created_at, :updated_at);";
        $stmt = $this->app->db->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hash, PDO::PARAM_STR);
        $stmt->bindValue(':role', 1);
        $stmt->bindParam(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindParam(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $result = $stmt->execute();
        if (!$result) {
            throw new \Exception('could not save the user');
        }
        
        // 正常終了
        $data = ['message' => 'SUCCESS'];
        // return $response->withRedirect('/admin/regist/complete', 301);
        return $response->withRedirect('/admin/regist/complete');
    }
    
    public function getRegistComplete(Request $request, Response $response)
    {
        return $this->app->view->render($response, 'admin/regist_complete.phtml');
    }
    
    public function getForms(Request $request, Response $response)
    {
        $sql = "SELECT * FROM form_items ";
        $stmt = $this->app->db->query($sql);
        $items = [];
        
        $i = 0;
        while($row = $stmt->fetch()) {
            $items[] = $row;
        }
        
        for ($i = 0; $i < count($items); $i++) {
            $items[$i]['type'] = intval($items[$i]['type']);
            
            if ($items[$i]['required'] == 1){
                $items[$i]['required'] = true;
            } else {
                $items[$i]['required'] = false;
            }
        }
        // var_dump($items);
        return $this->app->view->render($response, 'admin/forms.phtml', ['itemRows' => $items]);
    }
    
    public function postForms(Request $request, Response $response)
    {
        $req = $request->getParsedBody();
        $req_count = $req['row_length'];
        $result = [];
        
        for ($i = 0; $i < $req_count; $i++) {
            $result[$i]['id'] = strval($i + 1);
            $result[$i]['label_name'] = $req['templateDispName'][$i + 1];
            $result[$i]['schema_name'] = $req['templateColName'][$i + 1];
            $result[$i]['input_type'] = $req['templateInputType'][$i + 1];
            
            switch ($result[$i]['input_type']) {
                case 'checkbox':
                case 'radio':
                    $result[$i]['type'] = self::TYP_BOOL;
                    break;
                
                case 'text':
                    $result[$i]['type'] = self::TYP_STR;
                    break;
                
                default:
                    $result[$i]['type'] = self::TYP_STR;
                    break;
            }
            
            if ($req['templateRequired'][$i + 1] == 'true') {
                $result[$i]['required'] = true;
            }else {
                $result[$i]['required'] = false;
            }
            
            // $tmpChoiceName = str_replace(array(" ", "　"), "", $req['templateChoiceName'][$i]);
            // $result[$i]['choice_name'] = $tmpChoiceName;
            
            // $tmpChoiceVal = str_replace(array(" ", "　"), "", $req['templateChoiceValue'][$i]);
            // $result[$i]['choice_value'] = $tmpChoiceVal;
            
            if (empty($req['templatevalidateWith'][$i])) {
                $result[$i]['format_with'] = '';
            }else {
                $result[$i]['format_with'] = $req['templatevalidateWith'][$i];
            }
            
        }
        
        $sql = "SELECT * FROM form_items";
        $stmt = $this->app->db->query($sql);
        $form_items = [];
        
        while($row = $stmt->fetch()) {
            $form_items[] = $row;
        }
        
        for ($i = 0; $i < count($form_items); $i++) {
            if ($form_items[$i]['required'] == '0'){
                $form_items[$i]['required'] = true;
            }else{
                $form_items[$i]['required'] = false;
            }
        }
        var_dump(count($result));
        
        for ($i = 0; $i < count($result); $i++) {
            
            // var_dump($form_items[$i]['id']);
            // 更新 or 新規　処理
            if($result[$i]['id'] = $form_items[$i]['id']){
                $sql = "update form_items set
                    `label_name` = :label_name, 
                    `schema_name` = :schema_name, 
                    `input_type` = :input_type, 
                    `type` = :type, 
                    `required` = :required,
                    `format_with` = :format_with,
                    `updated_at` = :updated_at
                    where id = :id;";

                $stmt = $this->app->db->prepare($sql);
                $stmt->bindParam(':label_name', $result[$i]['label_name'], PDO::PARAM_STR);
                $stmt->bindParam(':schema_name', $result[$i]['schema_name'], PDO::PARAM_STR);
                $stmt->bindParam(':input_type', $result[$i]['input_type'], PDO::PARAM_STR);
                $stmt->bindParam(':type', $result[$i]['type'], PDO::PARAM_INT);
                $stmt->bindParam(':required', $result[$i]['required'], PDO::PARAM_BOOL);
                // $stmt->bindParam(':choice_name', $result[$i]['choice_name'], PDO::PARAM_STR);
                // $stmt->bindParam(':choice_value', $result[$i]['choice_value'], PDO::PARAM_STR);
                $stmt->bindParam(':format_with', $result[$i]['format_with'], PDO::PARAM_STR);
                $stmt->bindParam(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
                $stmt->bindParam(':id', $form_items[$i]['id'], PDO::PARAM_INT);
                    
                $res = $stmt->execute();
            } else {
                $sql = "INSERT INTO `form_items` (
                    `label_name`, 
                    `schema_name`, 
                    `input_type`, 
                    `type`, 
                    `required`,
                    `format_with`) 
                    VALUES (:label_name, :schema_name, :input_type, :type, :required, :format_with);";
                    
                $stmt = $this->app->db->prepare($sql);
                $stmt->bindParam(':label_name', $result[$i]['label_name'], PDO::PARAM_STR);
                $stmt->bindParam(':schema_name', $result[$i]['schema_name'], PDO::PARAM_STR);
                $stmt->bindParam(':input_type', $result[$i]['input_type'], PDO::PARAM_STR);
                $stmt->bindParam(':type', $result[$i]['type'], PDO::PARAM_INT);
                $stmt->bindParam(':required', $result[$i]['required'], PDO::PARAM_BOOL);
                // $stmt->bindParam(':choice_name', $result[$i]['choice_name'], PDO::PARAM_STR);
                // $stmt->bindParam(':choice_value', $result[$i]['choice_value'], PDO::PARAM_STR);
                $stmt->bindParam(':format_with', $result[$i]['format_with'], PDO::PARAM_STR);
                
                $res = $stmt->execute();
            }
        }
        // return $this->app->view->render($response, 'admin/forms_result.phtml');
        return $response->withRedirect('/admin/forms');
    }
    
    public function getLogs(Request $request, Response $response)
    {
        return $this->app->view->render($response, 'admin/logs.phtml');
    }
    
}