<?php

// Kickstart the framework
$f3 = require('lib/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');

$f3->route('GET /',
	function($f3) {
		$classes=array(
			'Base'=>
				array(
					'hash',
					'json',
					'session'
				),
			'Cache'=>
				array(
					'apc',
					'memcache',
					'wincache',
					'xcache'
				),
			'DB\SQL'=>
				array(
					'pdo',
					'pdo_dblib',
					'pdo_mssql',
					'pdo_mysql',
					'pdo_odbc',
					'pdo_pgsql',
					'pdo_sqlite',
					'pdo_sqlsrv'
				),
			'DB\Jig'=>
				array('json'),
			'DB\Mongo'=>
				array(
					'json',
					'mongo'
				),
			'Auth'=>
				array('ldap','pdo'),
			'Bcrypt'=>
				array(
					'mcrypt',
					'openssl'
				),
			'Image'=>
				array('gd'),
			'Lexicon'=>
				array('iconv'),
			'SMTP'=>
				array('openssl'),
			'Web'=>
				array('curl','openssl','simplexml'),
			'Web\Geo'=>
				array('geoip','json'),
			'Web\OpenID'=>
				array('json','simplexml'),
			'Web\Pingback'=>
				array('dom','xmlrpc')
		);
		$f3->set('classes',$classes);
		$f3->set('content','welcome.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->route('GET /submit',
	function($f3) {
		$f3->set('content','submit.html');
		echo View::instance()->render('layout.htm');
	}
);
	
$f3->route('POST /test',
	function($f3) {
		  $f3->set('content','submit.html');
          $f3->set('UPLOADS','uploads/'); // don't forget to set an Upload directory, and make it writable!
          $web = \Web::instance();
          $overwrite = false; // set to true, to overwrite an existing file; Default: false
          $slug = function($DD){ return time()."{$DD}";};
          //; // rename file to filesystem-friendly version
          $files = $web->receive(function($file,$formFieldName){
               //var_dump($file); $out = $file; 
                 if($file['type'] != "application/zip"){
                     return false; // this file is not valid, return false will skip moving it
                 }
          	    $db=new DB\SQL(
                    'mysql:host=127.0.0.1;port=3306;dbname=codershub',
                    'root',
                    ''
                );
                $submisions = new DB\SQL\Mapper($db,'submisions');
                  $submisions->name= $_POST['name'];
                  $submisions->email = $_POST['email'];
                  $submisions->file= $file['name'];
                  $submisions->save();
                  return true;
                  // allows the file to be moved from php tmp dir to your defined upload dir     	

             },
             $overwrite,
             $slug
         );
        $returned = array_keys($files);
        ($files[$returned[0]]) ? $f3->set('success',true) :  $f3->set('success',false);
        ($files[$returned[0]]) ? $f3->set('message','file uploaded!. Now chill. we would anounce the winner soon.') :  $f3->set('message','File format disallowed');
		$f3->set('content','submit.html');
		echo View::instance()->render('layout.htm');
	}
);

$f3->run();
