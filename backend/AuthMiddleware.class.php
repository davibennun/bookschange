<?php

class AuthMiddleware extends \Slim\Middleware
{
    public function call()
    {

        // Get reference to application
        $app = &$this->app;
        
        $req = $app->request();
        
        $keys = array_keys($req->post());
        $data = json_decode($keys[0]);

        
        var_dump($data->fb_id);
        //process and get facebook id
        $app->fb_id = $data->fb_id || "123456";

        // Run inner middleware and application
        $this->next->call();        
        
    }
}