<?php

class AuthMiddleware extends \Slim\Middleware
{
    public function call()
    {

        // Get reference to application
        $app = &$this->app;
        
        //process and get facebook id
        $app->fb_id = "123456";

        // Run inner middleware and application
        $this->next->call();        
        
    }
}