<?php

class AuthMiddleware extends \Slim\Middleware
{
    public function call()
    {

        // Get reference to application
        $app = &$this->app;
        
        $res = $app->response();
        $data = json_encode($res->body());
        print_r($data);
        exit;
        //process and get facebook id
        $app->fb_id = $data["fb_id"] || "123456";

        // Run inner middleware and application
        $this->next->call();        
        
    }
}