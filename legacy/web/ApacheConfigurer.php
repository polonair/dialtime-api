<?php

namespace Polonairs\Dialtime\WebBundle;

class ApacheConfigurer
{
	public function __construct() { }
	public function configure($host, $path, $itk = true)
	{
		file_put_contents("/etc/apache2/sites-available/${host}.conf", 
			"<VirtualHost *:80>\n\t".
				"DocumentRoot \"$path\"\n\t" .
				"ServerName $host\n\t" .
		(($itk)?("AssignUserID dialtime dialtime\n\t"):("")) .
				"<Directory \"$path\">\n\t\t" .
					"AllowOverride All\n\t\t" .
					"Order allow,deny\n\t\t" .
					"Require all granted\n\t\t" .
					"Allow from all\n\t" .
				"</Directory>\n" .
			"</VirtualHost>\n");
	}
}
