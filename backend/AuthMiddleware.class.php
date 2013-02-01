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
        	if(!empty($keys)){
        		
        		$data = json_decode($keys[0]);
                if($data){
    		        //process and get facebook id
    		        $app->fb_id = $data->fb_id || "123456";		
                }else{
                    $app->fb_id = "123456";
                }
        	}
	        
        }


        // Run inner middleware and application
        $this->next->call();        
        
    }
}