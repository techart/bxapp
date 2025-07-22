<?php
namespace Techart\BxApp\Middleware\Before;

class OpenApiHeaders
{
	/**
	 * Данный метод вызывается при использовании middleware
	 * 
	 * @return void
	 */
	function handle()
	{
		if (isset($_SERVER['HTTP_ISSWAGGER']) || strpos($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'], 'isswagger') !== false) {
			header('Access-Control-Allow-Headers:*');
			header('Access-Control-Allow-Origin:*');
			header('Access-Control-Allow-Methods:*');
			header('Access-Control-Allow-Credentials:true');

			if (strpos($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'], 'isswagger') !== false) {
				exit();
			}
		}
	}
}
