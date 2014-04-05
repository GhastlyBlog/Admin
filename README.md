Admin
=======

A simple admin panel for Ghastly. 

# Installation

Add `"ghastly/admin" : "dev-master"` to your blogs `composer.json`. Add `archive` to your list of installed plugins in `config.php` and configure it.

## Example config.php

```'plugins' => array(
		'archive', 
		'rss', 
		'admin'=>array(
			'username'=>'myuser',
			'password'=>'mypass'
		)
	),```


Go to `http://localhost/admin` to start writing
