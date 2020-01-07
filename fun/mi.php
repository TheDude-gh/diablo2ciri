<?php

//mysql function and common functions

//MYSQL
	function db_link() {
		global $db_link;
		if($db_link) return $db_link;
		global $_cfg;
		@$db_link = mysqli_connect($_cfg['mq.host'], $_cfg['mq.user'], $_cfg['mq.pass'], $_cfg['mq.db'], $_cfg['mq.port']) or die('Database not available' );
		mq("SET NAMES 'UTF8'");
		return $db_link;
	}

	function db_unlink() {
		global $db_link;
		if($db_link) {
			mysqli_close($db_link);
		}
		$db_link = false;
	}

	function expost($name, $def = null){
		return isSet($_POST[$name]) ? $_POST[$name] : $def;
	}

	function exget($name, $def = null){
		return isSet($_GET[$name]) ? $_GET[$name] : $def;
	}

	function excookie($name, $def = null){
		return isSet($_COOKIE[$name]) ? $_COOKIE[$name] : $def;
	}

	function SetDef($var, $def = null){
		if(!$var) $var = $def;
	}

	function mq($sql){
		$qres = mysqli_query(db_link(), $sql);
		if(!$qres) mlog($sql);
		return $qres;
	}

	function mfa($query){
		return mysqli_fetch_array($query, MYSQLI_BOTH);
	}

	function mfr($query){
		return mysqli_fetch_array($query, MYSQLI_NUM);
	}

	function mfs($query){
		return mysqli_fetch_array($query, MYSQLI_ASSOC);
	}

	function mgr($sql){ //mysql get result - for one value results
		@$res = mfa(mq($sql));
		return $res[0];
	}

	function mgrow($sql){ //mysql get row - for one row result
		@$res = mfa(mq($sql));
		return $res;
	}

	function me(){
		$me = mysqli_error(db_link());
		if($me) echo '<p style="border:solid 1px #f00;">'.$me.'</p>';
	}

	function mes($string){
		return mysqli_real_escape_string(db_link(), $string);
	}

	function mar(){
		return mysqli_affected_rows(db_link());
	}

	function mii(){
		return mysqli_insert_id(db_link());
	}

	function mlog($sql){
		$me = mysqli_error(db_link());

		echo '<p>['.$sql.']<br />'.$me.'</p>';
		if(!file_exists('./cache/')) return;
		$fmlog = 'cache/log_sql.log';
		$time = date('Y-m-d H:i:s, ', time());
		$dt = debug_backtrace();
		$dbl = '';
		if(array_key_exists(1, $dt)){
			$dbl = $dt[1]['file'].', line:'.$dt[1]['line'].', func:'.$dt[1]['function'];
		}
		file_a($fmlog, $time.' '.$_SERVER['REQUEST_URI'].EOL.$dbl.EOL.$sql.EOL.$me.EOL.EOL);
	}


//FILE
	function file_write($soubor,$data){
		if(!$file = FOpen($soubor, 'w')) return false;
		elseif(!FWrite($file, $data)) return false;
		FClose($file);
		return true;
	}

	function file_append($soubor,$data){
		if(!$file = FOpen($soubor, 'a')) return false;
		elseif(!FWrite($file,$data)) return false;
		else FClose($file);
		return true;
	}

//COMMON
	function comma($value){
		return number_format($value, 0, ',', '&nbsp;');
	}

	function padleft($value, $len = 2){
		return str_pad($value, $len, 0, STR_PAD_LEFT);
	}

	function vd($var){
		echo '<pre class="vardump">';
		var_dump($var);
		echo '</pre>';
	}

	function pre($var){
		echo '<pre class="vardump">'.$var.'</pre>';
	}

?>
