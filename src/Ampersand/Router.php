<?php

namespace Ampersand;

class Router
{
	
	public static function route($method, $route, $handler, $regex = array()) {

		if (strtoupper($method) === $_SERVER['REQUEST_METHOD']) {

			$request_uri = $_SERVER['REQUEST_URI'];
			
			if(Router::route_matches($route, $request_uri, $regex)) {
						
				if(preg_match('/:/', $route)) {
					$variables_array = Router::route_variables($route, $request_uri);
				} else {
					$variables_array = array();
				}
				
				$handler($variables_array);
				return true;
			}	
		}
		return false;			
	}

	public static function route_matches($expected_route, $request_uri, $regex = array())
	{


		//remove trailing '/'
		$request_uri = preg_replace('/\/$/', '', $request_uri);	
		$expected_route = preg_replace('/\/$/', '', $expected_route);	
		
		//split strings
		$route = preg_split('/\//', $expected_route);
		$uri = preg_split('/\//', $request_uri);	
		//
		//if they are different lengths they cannot match
		if(count($route) === count($uri)) {


			for($i = 0; $i < count($route); $i++) {
				
				if(preg_match('/^:/', $route[$i])) {
					
					// get regex array index from router :variable
					$index = ltrim($route[$i], ':');
				
					if(isset($regex[$index])) {
						//pattern match with regex
						if(!preg_match($regex[$index], $uri[$i])) {
							
							return false;	
						}
					}

				} else {
				
				
					//check pattern against request_uri
					if($route[$i] !== $uri[$i]) {
							return false;	
					} 
				} 
			}

			return true;
			
		} else {
			return false;
		}
			
	}


	public static function route_variables($expected_route, $request_uri)
	{
		
		$route = preg_split('/\//', $expected_route);
		$uri = preg_split('/\//', $request_uri);	

		// array of variables from URI
		$var_array = array();
		
		if(count($route) === count($uri)) {

			for($i = 0; $i < count($route); $i++) {
			
				if(preg_match('/^:/', $route[$i])) {
				
					$index = ltrim($route[$i], ':');
					$var_array[$index] = $uri[$i];

					} 

				} 
			}
		return $var_array;
	}

}
