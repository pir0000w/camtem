<?php

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class FormController
{
    private $app = null;
    
    public function __construct(Container $app) {
        $this->app = $app;
    }
    
    public function index(Request $request, Response $response)
    {
        
        $sql = "SELECT * FROM form_items ";
        $stmt = $this->app->db->query($sql);
        $form_items = [];
        
        $i = 0;
        while($row = $stmt->fetch()) {
            $form_items[] = $row;
        }
        
        for ($i = 0; $i < count($form_items); $i++) {
            $form_items[$i]['type'] = intval($form_items[$i]['type']);
            
            if ($form_items[$i]['required'] == 1){
                $form_items[$i]['required'] = true;
            } else {
                $form_items[$i]['required'] = false;
            }
        }
        return $this->app->view->render($response, 'form/index.html', ['form_items' => $form_items]);
    }
    
    public function confirm(Request $request, Response $response)
    {
        $postParams = $request->getParsedBody();
        
        $sql = "SELECT schema_name, required, format_with FROM form_items ";
        $stmt = $this->app->db->query($sql);
        $form_items = [];
        
        while($row = $stmt->fetch()) {
            $form_items[] = $row;
        }

         $_SESSION['form']= array();
         foreach($form_items as $form_item){
            $err_msg='';
             
            ($form_item['required'] === '1') ? $form_item['required'] = true: $form_item['required']  =false;
            if($form_item['required']){
                if ($postParams[$form_item['schema_name']] == ''){
                     $err_msg = '未入力項目があります';
                     return $this->app->view->render($response, 'form/index.html', ['err_msg' => $err_msg]);
                }
            }
            $schema_name = $form_item['schema_name'];
            $_SESSION['form'][$schema_name] = $postParams[$schema_name];
            // var_dump($_SESSION);
         }
        return $this->app->view->render($response, 'form/confirm.html', ['session' => $_SESSION['form']]);
    }
    
    public function complete(Request $request, Response $response)
    {
        $postParams = $request->getParsedBody();
        var_dump($postParams);
        
        
        // form_submitsに登録してid取得
        $sql = "INSERT INTO `form_submits` (
                    `created_at`, `updated_at`) 
                    VALUES (:created_at, :updated_at);";
        $stmt = $this->app->db->prepare($sql);

        $this->app->db->beginTransaction();
        try {
            $stmt->bindValue(':created_at', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $res = $stmt->execute();
            
            $last_id = $this->app->db->lastInsertId('id');
            // var_dump($last_id);
            
            // form_valueに登録
            foreach ($postParams as $key => $value) {
                
                $fi_sql = "SELECT label_name, schema_name, input_type FROM form_items where schema_name = :key ";
                $fi_stmt = $this->app->db->prepare($fi_sql);
                $fi_stmt = bindValue(':key', $key, PDO::PARAM_STR);
                $fi_stmt->execute();
                $fi_result = $fi_stmt->fetch();
                
                var_dump($fi_result);
                

                $fv_sql = "INSERT INTO `form_values` (
                        `submit_id`, ,`colmun_name`, `text`, `textarea`, `int`, `datetime`, `bool`) 
                        VALUES (:submit_id, :colmun_name, :text, :textarea, :int, :datetime, :bool);";
                $fv_stmt = $this->app->db->prepare($fv_sql);
                        
                $fv_stmt->bindValue(':submit_id', intVal($last_id), PDO::PARAM_INT);
                $fv_stmt->bindValue(':colmun_name', $key, PDO::PARAM_STR);
                
                switch($fi_result['input_type']) {
                    
                    case "text":
                    case "textarea":
                        $fv_stmt->bindValue(":${fi_result['input_type']}", $value, PDO::PARAM_STR);
                        break;
                    
                    case "int":
                        $fv_stmt->bindValue(':int', intVal($value), PDO::PARAM_INT);
                        break;
                        
                    case "datetime":
                        $fv_stmt->bindValue(':datetime', date("Y-m-d H:i:s", $value), PDO::PARAM_STR);
                        break;
                        
                    case "bool":
                        $fv_stmt->bindValue(':bool', $value, PDO::PARAM_BOOL);
                        break;
                        
                    default:
                        break;
                }
                
                $fv_res = $fv_stmt->execute();
                
            }
        } catch (Exception $e) {
            $this->app->db->rollBack();
            throw $e;
        }
        
        
        
        // return $this->app->view->render($response, 'form/complete.html');
    }
    
    private function get_formitems(){
        $sql = "SELECT * FROM form_items ";
        $stmt = $this->app->db->query($sql);
        $form_items = [];
        
        while($row = $stmt->fetch()) {
            $form_items[] = $row;
        }
        return $form_items;
    }
}