<?php

class AuthMiddleware extends \Slim\Middleware
{
    public function call()
    {

        // Get reference to application
        $app = &$this->app;
        
        $req = $app->request();
        $data = json_encode($req->body());
        var_dump($data);
        var_dump($req->post());
        var_dump($req->get());
        exit;
        //process and get facebook id
        $app->fb_id = $data["fb_id"] || "123456";

        // Run inner middleware and application
        $this->next->call();        
        
    }
}