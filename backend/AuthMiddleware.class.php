<?php

class AuthMiddleware extends \Slim\Middleware
{
    public function call()
    {

        // Get reference to application
        $app = &$this->app;
        
        $req = $app->request();
        $post = $req->post();

        if(!empty($post)){
        	$keys = array_keys($post);
print_r($post);
        	if(!empty($keys)){
        		print_r($keys);
        		$data = json_decode($keys[0]);
		        //process and get facebook id
		        $app->fb_id = $data->fb_id || "123456";		
        	}
	        
        }

        exit;

        // Run inner middleware and application
        $this->next->call();        
        
    }
}