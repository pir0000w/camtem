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
        // var_dump($items);
        return $this->app->view->render($response, 'form/index.html', ['form_items' => $form_items]);
    }

    public function regist(Request $request, Response $response)
    {
        $name = $request->getAttribute('name');
        return $this->app->view->render($response, 'form/regist.html');
    }
    
    public function confirm(Request $request, Response $response)
    {
        $name = $request->getAttribute('name');
        return $this->app->view->render($response, 'form/confirm.html');
    }
    
    public function complete(Request $request, Response $response)
    {
        $name = $request->getAttribute('name');
        return $this->app->view->render($response, 'form/complete.html');
    }
}