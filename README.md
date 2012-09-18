## supervisord-php

Simple PHP XMLRPC Client for http://supervisord.org

## Download
	git clone https://github.com/tyd/supervisord-php.git
	
## Basic Usage
	require_once 'supervisord-php/lib/Supervisord.php';
	
	$s = new Supervisord('127.0.0.1', 9001, 'username', 'password');
	print_r( $s->getAllProcessInfo() );
	
## How to Contribute

### Pull Requests

1. Fork the repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **develop** branch

It is very important to separate new features or improvements into separate feature branches, and to send a pull
request for each branch. This allows me to review and pull in new features or improvements individually.