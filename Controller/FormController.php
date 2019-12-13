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

    // public function regist(Request $request, Response $response)
    // {
    //     $name = $request->getAttribute('name');
    //     return $this->app->view->render($response, 'form/regist.html');
    // }
    
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
        
        $sql = "SELECT * FROM form_items ";
        $stmt = $this->app->db->query($sql);
        $form_items = [];
        
        while($row = $stmt->fetch()) {
            $form_items[] = $row;
        }
        
        
        // return $this->app->view->render($response, 'form/complete.html');
    }
}