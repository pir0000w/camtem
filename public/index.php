<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require __DIR__ . '/../vendor/autoload.php';

session_start();

require __DIR__ . '/../Controller/AdminController.php';
require __DIR__ . '/../Controller/FormController.php';
require __DIR__ . '/../src/routes.php';

