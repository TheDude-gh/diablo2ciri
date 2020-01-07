<?php
	DEFINE('TAB', "\t");
	DEFINE('EOL', "\n");
	DEFINE('ELD', "\r\n");

	//config path of diablo files version, change this to desired version
	DEFINE('D2PATH', './d2ver/114d/');

	//subdirs of diablo files
	DEFINE('D2PATHFILES', D2PATH.'string/');
	DEFINE('D2PATHGFX', D2PATH.'gfx/');
	DEFINE('D2PATHPAL', D2PATH.'palette/') ;
	DEFINE('D2PATHTXT', D2PATH.'txt/');

	//path of dc6 converted to png
	DEFINE('D2PATHIMG', './img/');

	//saves folders
	DEFINE('WORKSAVE', './save/');
	DEFINE('WORKSTASH', './stash/');
	DEFINE('WORKITEMS', './items/');

	//folders of game saves and atma stash
	DEFINE('D2SAVE', '/Games/Diablo II/save/');
	DEFINE('D2STASH', '/Games/Diablo II/Stash/');


	date_default_timezone_set('Europe/Prague');

	require_once './fun/access.php';
?>
