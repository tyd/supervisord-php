supervisord-php
===============

Simple PHP XMLRPC Client for http://supervisord.org

Download
===============
	git clone https://github.com/tyd/supervisord-php.git
	
Basic Usage
===============
	require_once 'supervisord-php/lib/Supervisord.php';
	
	$s = new Supervisord('127.0.0.1', 9001, 'username', 'password');
	print_r( $s->getAllProcessInfo() );