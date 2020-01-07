<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='cs'>
<head>
	<title>Diablo II CIRI</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="cs" />
	<link rel="icon" href="" />
	<style>
	* {background: #222; color: #ccc; font-family: calibri, arial, sans-serif; } /*ddc*/
	div { margin: 2em auto; width: 95%;}
	table {border-collapse:collapse; margin: 1em; border: solid 1px #000;}
	th { background: #555; color: #fff; } /*dd1*/
	th, td {border: solid 1px #000; min-width: 1em; padding: 1px 5px;}
	.ar { text-align:right; }
	.ac { text-align:center; }
	.al { text-align:left; }
	.vac { vertical-align:middle; }

	a, a:visited { color: #0cc; text-decoration: none; }
	a:hover { text-decoration: underline; }

	.smalltable {font-size: 14px;}
	.smalltable1 { width: 75%;  margin: 1em auto;}

	.inormal { color: #aaa; font-weight: bold; letter-spacing: 1px; }
	.isocketh { color: #788; font-weight: bold; letter-spacing: 1px; }
	.imagic { color: #4850B8; font-weight: bold; letter-spacing: 1px; }
	.irare { color: #FFFF00; font-weight: bold; letter-spacing: 1px; }
	.icraft { color: #FFA500; font-weight: bold; letter-spacing: 1px; }
	.iset { color: #00C400; font-weight: bold; letter-spacing: 1px; }
	.iunique { color: #908858; font-weight: bold; letter-spacing: 1px; }
	.iruneword { color: #990099; font-weight: bold; letter-spacing: 1px; }
	.inormaln { color: #eee; }
	.isetn { color: #00C400; }
	.isetnr { color: #C40000; }
	.mono { font-family: 'courier new', monospace; font-size: 12px;}
	.done { background: #474; }
	.skilldiv { border-left: solid 4px #000; }

	.magictext { color: #4850B8; background: #000; }
	.uniquetext { color: #908858; background: #000; }
	.exinfotext { color: #37EFA0; }


	.itemslay, .itemslay td { border: none;}

	.charbody {}
	.charbody td { text-align:center; border: solid 1px #888; }
	.tooltip { position: relative; }
	.tooltip p { visibility: hidden; position: absolute; background: #000; border: solid 2px red; left: 150px; top: 0px;  z-index: 1; width: 400px; }
	.tooltip:hover p { visibility:visible; text-align: center; }
	.tooltip p span { background: #000; }

	.storage td { text-align:center; min-width: 50px; min-height: 50px; border: solid 1px #888;}
	.wid50 { width: 50px; }
	.wid100 { width: 100px; }
	.wid150 { width: 150px; }
	.wid200 { width: 200px; }
	.hei50 { height: 50px; }
	.hei100 { height: 100px; }
	.hei150 { height: 150px; }
	.hei200 { height: 200px; }
	.hei300 { height: 300px; }
	.hei400 { height: 400px; }
	/*img { width: 256px; margin: 0px auto;}*/
	</style>
</head>
<body>
<div>
<p>
	<a href="index.php">Nothing</a>
	| <a href="?c=c">Characters</a>
	| <a href="?c=s">Stash</a>
	| <a href="?c=i">Items</a>
	| <a href="?cs=1">Copy Saves</a>
	| <a href="?c=c&amp;db=1">Save DB Char</a>
	| <a href="?c=s&amp;db=1">Save DB Stash</a>
</p>
<?php

	require_once './fun/config.php';
	require_once './fun/mi.php';

	require_once './fun/bytereader.php';
	require_once './fun/d2item.php';
	require_once './fun/d2stash.php';
	require_once './fun/d2char.php';
	require_once './fun/d2data.php';
	require_once './fun/d2dc6.php';


	//url parametres
	$c = exget('c'); //comand
	$v = exget('v'); //value
	$cs = exget('cs'); //copy saves
	$db = exget('db'); //save to db
	$dbc = exget('dbc'); //save to db

	//copy saves from another folder to working folder
	if($cs) {
		CopySaves();
	}
	//clear database table
	elseif($dbc) {
		$sql = "TRUNCATE ditem_place";
		mq($sql);
	}

	//decode value
	if($v) {
		$v = urldecode($v);
	}

	//if there is active command, we will need palette and data files
	if($c) {
		global $D2PALETTE;
		$D2PALETTE = new D2Palette();

		global $D2DATA;
		$D2DATA = new D2Data();
	}


	//command to show characters
	if($c == 'c') {
		$chars = scandirM(WORKSAVE, '/\.d2s$/i');

		$ditems = new D2items();
		$char = null;

		foreach($chars as $k => $charf) {
			//if specific char is selected, show only one
			if(!$v || $v == $charf) {
				$ditems->ReadCharacter($charf);
				$char = $ditems->GetLastChar();
				//save to db
				if($db) {
					$ditems->SaveDBplace($char->itemlist['c']);
					$ditems->SaveDBplace($char->itemlist['m'], 'mercenary');
				}
			}
		}

		//show character table
		$ditems->ShowCharacters();
		//if only one is selected, show more details
		if($v) {
			$char = $ditems->characters[0];
			//echo $char->ShowChecksum();
			echo $char->ShowQuestWaypoint();
			echo $char->ShowSkills();

			$ditems->char_current = 0;
			$ditems->CharSetCheck(0);

			echo '
				<table class="itemslay">
					<tr><td>';
			$ditems->ShowBodyChar(0); //body
			echo '</td><td>';
			$ditems->ShowInventory(0, D2Inventory::STASH); //stash
			echo '</td><td>';
			$ditems->ShowBodyMerc(0); //merc
			echo '</td></tr><tr><td>';
			$ditems->ShowInventory(0, D2Inventory::INVENTORY); //inventory
			echo '</td><td>';
			$ditems->ShowInventory(0, D2Inventory::CUBE); //cube
			echo '</td><td>';
			$ditems->ShowBelt(0); //belt
			echo '</td></tr></table>';
		}
	}
	//show stash
	elseif($c == 's') {
		$stashes = ScanDirM(WORKSTASH, '/\.d2x$/i');

		$ditems = new D2items();
		foreach($stashes as $stash) {
			if(!$v) {
				echo '<a href="?c=s&amp;v='.urlencode($stash).'">'.$stash.'</a><br />';
			}
			//only one is selected, show more details
			elseif($v == $stash) {
				$ditems->ReadStash($stash);
				$ditems->ShowItems($ditems->itemlist);
			}
			//save to db
			if($db) {
				$ditems->ReadStash($stash);
				$ditems->SaveDBplace();
			}
		}
	}
	//show item
	elseif($c == 'i') {
		$itemsf = ScanDirM(WORKITEMS, '/\.d2i$/i');
		$ditems = new D2items();
		foreach($itemsf as $itemf) {
			$item = file_get_contents($itemf);
			$d2item = new D2Item($item);
			$ditems->itemlist[] = $d2item;
		}
		$ditems->ShowItems($ditems->itemlist);
	}


	//class to show items from stast, character or single item
	class D2items {

		private $reader;

		private $numItem = 0;
		public $itemlist = array();

		private $filetype = 0;
		public $items = array();
		public $char_current = -1;

		public $place; //what file is opened
		public $placetype; //c=char, s=stash, i=item

		public $stashes = array();
		public $characters = array();
		public $icounts = array();
		public $setprops = array();

		public $dbitems = null;


		public function __construct() {

		}

		public function ReadCharacter($file) {
			if(!file_exists($file)) {
				echo $file.' not found<br />';
				return;
			}

			$pathinfo = pathinfo($file);
			$this->place = $pathinfo['filename'];
			$this->placetype = 'c';

			$d2char = new D2Character($file);

			$this->characters[] = $d2char;
		}

		public function GetLastChar() {
			return $this->characters[count($this->characters) - 1];
		}

		public function ReadStash($file) {
			$pathinfo = pathinfo($file);
			$this->place = $pathinfo['filename'];
			$this->placetype = 's';

			$stash = new D2Stash($file);
			$this->itemlist = $stash->GetItemList();
		}

		//save items to db, only unique or set or runeword
		public function SaveDBplace($itemlist = null, $location = '') {
			if($this->dbitems == null) {
				$this->dbitems = array();
				$sql = "SELECT id, name
					FROM ditem AS d
					WHERE name!='-'";
				$query = mq($sql);
				while($res = mfs($query)) {
					$this->dbitems[strtolower($res['name'])] = $res['id'];
				}
			}

			if($itemlist == null) {
				$itemlist = $this->itemlist;
			}

			$sql = "INSERT INTO ditem_place (id, place, fingerprint, `type`, `location`) VALUES ";
			foreach($itemlist as $n => $item) {
				//skip items of quality we dont want
				if($item->magic_rank != D2MagicRank::SET
					&& $item->magic_rank != D2MagicRank::UNIQUE
					&& $item->magic_rank != D2MagicRank::RUNEWORD)
				{
					continue;
				}

				//fix string of some silly
				if($item->item_name == "Ancients' Pledge") {
					$item->item_name = "Ancient's Pledge";
				}

				$key = strtolower($item->item_name);
				if(!array_key_exists($key, $this->dbitems)) {
					echo 'Not found '.$item->item_name.'<br />';
					continue;
				}

				$id = $this->dbitems[$key];

				//$sql = "INSERT INTO ditem_place (id, place, fingerprint, `type`, `location`)
				//	VALUES ($id, '".$this->place."', '".$item->fingerprint."', '".$this->placetype."', '$location')";
				if($n > 0) $sql .= ', '.EOL;
				$sql .= "($id, '".$this->place."', '".$item->fingerprint."', '".$this->placetype."', '$location')";
			}
			mq($sql);
		}

		//display character list table
		public function ShowCharacters() {
			echo '<table>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Saved</th>
					<th>Map</th>
					<th>Title</th>
					<th>Class</th>
					<th>Level</th>
					<th>Version</th>
					<th>Merc</th>
					<th>Str</th>
					<th>Dex</th>
					<th>Vit</th>
					<th>Ene</th>
					<th>HP</th>
					<th>Mana</th>
					<th>Skill</th>
					<th>Uskill</th>
					<th>Items</th>
					<th>Merc Items</th>
					<th>Location</th>
					<th>Quests</th>
					<th>Last Q</th>
					<th>CowKing</th>
					<th>Waypoints</th>
				</tr>';

			$n = 1;
			foreach($this->characters as $char) {

				$mercname = '';
				$mercitems = 0;
				//mercenary
				if($char->mercenary) {
					$mercname = $char->mercenary->mercname;
					$mlevel = $char->mercenary->level;
					$race = str_ireplace(' mercenary', '', $char->mercenary->mercrace);
					$mtype = str_replace('-Normal', ' N', $char->mercenary->merctype);
					$mtype = str_replace('-Nightmare', ' NM', $mtype);
					$mtype = str_replace('-Hell', ' H', $mtype);
					$mercname .= ' '.$mlevel.'<br />'.$race.' '.$mtype;
					$mercitems = count($char->itemlist['m']);
				}

				$items = count($char->itemlist['c']);

				//char attributes
				$str = $char->stats['strength'];
				$dex = $char->stats['dexterity'];
				$vit = $char->stats['vitality'];
				$ene = $char->stats['energy'];

				//unasigned skillpoints
				$uskills = isSet($char->stats['newskills']) ? $char->stats['newskills'] : 0;

				$hp = (int)($char->stats['hitpoints'] / 256);
				$mana = (int)($char->stats['mana'] / 256);

				$quests = $waypoints = $ck = '';

				foreach($char->cowKingDead as $k => $c) {
					$ck .= $c;
				}

				$skills = 0;
				foreach($char->skills as $tree) {
					foreach($tree as $s) {
						list($ns, $sp) = explode('=', $s);
						$skills += $sp;
					}
				}

				$char->LastDoneQuest();

				echo '
				<tr>
					<td>'.$n.'</td>
					<td class=""><a href="?c=c&amp;v='.urldecode($char->filename).'">'.$char->charname.'</a></td>
					<td class="mono">'.date('Y-m-d H:i:s', $char->timestamp).'</td>
					<td class="mono">'.$char->mapseed.'</td>
					<td>'.$char->titlename.'</td>
					<td>'.$char->class.'</td>
					<td class="ar">'.$char->level.'</td>
					<td class="ar">'.$char->version.'</td>
					<td>'.$mercname.'</td>
					<td class="ar">'.$str.'</td>
					<td class="ar">'.$dex.'</td>
					<td class="ar">'.$vit.'</td>
					<td class="ar">'.$ene.'</td>
					<td class="ar">'.$hp.'</td>
					<td class="ar">'.$mana.'</td>
					<td class="ar">'.$skills.'</td>
					<td class="ar">'.$uskills.'</td>
					<td class="ar">'.$items.'</td>
					<td class="ar">'.$mercitems.'</td>
					<td>'.$char->curdif.' Act '.$char->curact.'</td>
					<td class="mono">'.$quests.'</td>
					<td>'.$char->lastQuest.'</td>
					<td class="mono">'.$ck.'</td>
					<td class="mono">'.$waypoints.'</td>
				</tr>';
				$n++;
			}
			echo '</table>';
		}

		//get char set items
		public function CharSetCheck($charnum) {
			$char = $this->characters[$charnum];

			foreach($char->itemlist['c'] as $item) {
				if($item->magic_rank != D2MagicRank::SET) continue;
				$char->sets[$item->set_name][] = $item->set_item;
			}

			foreach($char->itemlist['m'] as $item) {
				$char->sets[$item->set_name][] = $item->set_item;
			}

			$this->ReadSetPropeties();
		}

		//find item for specific inventory location
		public function FindCharItemByPlace($location, $container, $body) {
			$char = $this->characters[0];
			foreach($char->itemlist['c'] as $item) {
				if($item->location != $location) continue;
				if($container != D2Inventory::NONE && $item->container != $container) continue;
				if($item->body != $body) continue;

				$icolor = $this->GetMagicRankColor($item->magic_rank, $item->runeword);
				$im = $this->GetImage($item->gfx, $item->baseTrans, $item->magicTrans);

				$tooltip = $this->ItemTooltip($item);

				return $im.'<br /><span class="'.$icolor.'">'.$item->item_name.'</span>'.$tooltip;
			}
			return '';
		}

		//display char body with items
		public function ShowBodyChar($charnum) {
			$char = $this->characters[$charnum];
			/* inventory types
				char   merc  belt  inventory     stash   cube
				xxhax  xhx   bbbb  iiiiiiiiii    s6      h3
				wwass  was   bbbb  iiiiiiiiii    8       4
				grbrb        bbbb  iiiiiiiiii
				                   iiiiiiiiii
			*/

			$helmet    = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::HELMET);
			$amulet    = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::AMULET);
			$weaponalt = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::WEAPONR2);
			$weapon    = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::WEAPONR);
			$armor     = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::ARMOR);
			$shield    = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::WEAPONL);
			$shieldalt = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::WEAPONL2);
			$gloves    = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::GLOVES);
			$boots     = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::BOOTS);
			$belt      = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::BELT);
			$ringR     = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::RINGR);
			$ringL     = $this->FindCharItemByPlace(D2Inventory::EQUIPPED, 0, D2Inventory::RINGL);

			echo '
				<table class="charbody">
					<tr>
						<td class="wid200 hei100" colspan="2"></td>
						<td class="tooltip wid100">'.$helmet.'</td>
						<td class="tooltip wid100">'.$amulet.'</td>
						<td class="wid100"></td>
					</tr>
						<td class="tooltip wid100 hei150">'.$weapon.'</td>
						<td class="tooltip wid100">'.$shield.'</td>
						<td class="tooltip">'.$armor.'</td>
						<td class="tooltip">'.$weaponalt.'</td>
						<td class="tooltip">'.$shieldalt.'</td>
					<tr>
					</tr>
					<tr>
						<td class="tooltip hei100">'.$gloves.'</td>
						<td class="tooltip">'.$ringR.'</td>
						<td class="tooltip">'.$belt.'</td>
						<td class="tooltip">'.$ringL.'</td>
						<td class="tooltip">'.$boots.'</td>
					</tr>
				</table>';

		}

		//display merc body with items
		public function ShowBodyMerc($charnum) {
			$items = $this->characters[$charnum]->itemlist['m'];

			$weapon = $helmet = $shield = $armor = '';
			foreach($items as $item) {

				$im = $this->GetImage($item->gfx, $item->baseTrans, $item->magicTrans);
				$icolor = $this->GetMagicRankColor($item->magic_rank, $item->runeword);
				$info = $im.'<br /><span class="'.$icolor.'">'.$item->item_name.'</span>' . $this->ItemTooltip($item);

				if($item->body == D2Inventory::HELMET) {
					$helmet = $info;
				}
				elseif($item->body == D2Inventory::WEAPONR) {
					$weapon = $info;
				}
				elseif($item->body == D2Inventory::ARMOR) {
					$armor = $info;
				}
				elseif($item->body == D2Inventory::WEAPONL) {
					$shield = $info;
				}
			}

			echo '
				<table class="charbody">
					<tr>
						<td class="wid100 hei100"></td>
						<td class="tooltip wid100">'.$helmet.'</td>
						<td class="wid100"></td>
					</tr>
						<td class="hei150 tooltip">'.$weapon.'</td>
						<td class="tooltip">'.$armor.'</td>
						<td class="tooltip">'.$shield.'</td>
					<tr>
					</tr>
				</table>';

		}

		//display belt content
		public function ShowBelt($charnum) {
			$char = $this->characters[$charnum];

			//belt dimension
			$beltW = 4;
			$beltH = $char->beltrows;

			$belt = array();
			for($r = 0; $r < $beltH; $r++) {
				for($c = 0; $c < $beltW; $c++) {
					$belt[$r][$c] = '&nbsp;';
				}
			}

			foreach($char->itemlist['c'] as $item) {
				if($item->location != D2Inventory::BELTI) continue;

				$c = $item->col % $beltW;
				$r = (int)($item->col / $beltH);

				$im = $this->GetImage($item->gfx, $item->baseTrans, $item->magicTrans);

				$tooltip = $this->ItemTooltip($item);
				$belt[$r][$c] = $im.$tooltip;
			}

			echo '<table class="charbody">';
			for($r = $beltH - 1; $r >= 0; $r--) {
				echo '<tr>';
				for($c = 0; $c < $beltW; $c++) {
					echo '<td class="tooltip">'.$belt[$r][$c].'</td>';
				}
				echo '</tr>';
			}
			echo '</table>';

		}

		//display inventory
		public function ShowInventory($charnum, $storetype) {
			$char = $this->characters[$charnum];

			//various inventory type dimensions
			//stash
			if($storetype == D2Inventory::STASH) {
				$storageH = 8;
				$storageW = 6;
			}
			//inventory
			elseif($storetype == D2Inventory::INVENTORY) {
				$storageH = 4;
				$storageW = 10;
			}
			//cube
			elseif($storetype == D2Inventory::CUBE) {
				$storageH = 4;
				$storageW = 3;
			}

			//get initial layout
			$storage = array();
			for($r = 0; $r < $storageH; $r++) {
				for($c = 0; $c < $storageW; $c++) {
					$storage[$r][$c] = 'x';
					$layout[$r][$c] = array();
				}
			}

			//fill items
			foreach($char->itemlist['c'] as $item) {
				if($item->container != $storetype) continue;

				$c = $item->col;
				$r = $item->row;

				$im = $this->GetImage($item->gfx, $item->baseTrans, $item->magicTrans);
				$h = $item->invH;
				$w = $item->invW;

				for($x = $r; $x < $r + $h; $x++) {
					for($y = $c; $y < $c + $w; $y++) {
						//the initial position of item, top left
						if($x == $r && $y == $c) {
							$layout[$x][$y] = array('h' => $h, 'w' => $w);
							$tooltip = $this->ItemTooltip($item);
							$storage[$x][$y] = $im.$tooltip;
						}
						//other fields of item, we add zero, for later table buildup
						else {
							$layout[$x][$y] = array(0);
						}
					}
				}
				$layout[$r][$c] = array('h' => $h, 'w' => $w);
			}

			//display table
			echo EOL.EOL;
			echo '<table class="storage">'.EOL;

			for($r = 0; $r < $storageH; $r++) {
				echo '<tr>';
				for($c = 0; $c < $storageW; $c++) {
					$cc = count($layout[$r][$c]);

					if($cc == 2) {
						$w = $layout[$r][$c]['w'];
						$h = $layout[$r][$c]['h'];

						$cls = ' wid'.(50 * $w);
						$cls .= ' hei'.(50 * $h);

						echo '<td colspan="'.$w.'" rowspan="'.$h.'" class="tooltip'.$cls.'">'.$storage[$r][$c].'</td>';
					}
					elseif($cc == 0) {
						echo '<td class="wid50 hei50">&nbsp;</td>';
					}
					//else skip
				}
				echo '</tr>'.EOL;
			}
			echo '</table>'.EOL;
		}

		//display items table
		public function ShowItems($itemlist) {
			echo '<table>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Base</th>
					<th>Type</th>
					<th>Rank</th>
					<th>Fingerprint</th>
					<th>Ethereal</th>
					<th>Runeword</th>
					<th>Sockets</th>
					<th>Socket Items</th>
					<th>Place</th>
					<th>Image</th>
				</tr>';

			$n = 1;

			usort($itemlist, 'ItemSort');


			foreach($itemlist as $item) {
				//get counts of duplicite items
				if(exget('cc')) {
					if(!array_key_exists($item->item_name, $this->icounts)) {
						$this->icounts[$item->item_name] = array(0, $item);
					}
					$this->icounts[$item->item_name][0]++;
				}

				//skip some types
				if($item->magic_rank != D2MagicRank::SET
					&& $item->magic_rank != D2MagicRank::UNIQUE
					&& $item->magic_rank != D2MagicRank::RUNEWORD
					)
				{
					//continue;
				}

				//runeword color title and name
				if($item->runeword) {
					$item->magic_rank = D2MagicRank::RUNEWORD;
					if($item->runeword_name != '') {
						$item->item_name = $item->runeword_name;
					}
				}

				//color of name title
				$icolor = $this->GetMagicRankColor($item->magic_rank, $item->runeword);

				//get image
				$im = $this->GetImage($item->gfx, $item->baseTrans, $item->magicTrans);

				//get and show socketed items
				$socket = '0';
				$socketitems = '';
				if($item->sockets) {
					$socket = $item->SocketsFilled.'/'.$item->SocketsNum;
					foreach($item->SocketItems as $k => $si) {
						if($k > 0) $socketitems .= '<br />';
						$ims = $this->GetImage($si->gfx, $si->baseTrans, $si->magicTrans);
						$tooltip = $this->ItemTooltip($si);
						$socketitems .= '<span class="tooltip">'.$ims.$tooltip.'</span> '.$si->item_name;
					}
				}

				//stored location
				$locinfo = $item->parent.' : '.($item->location == D2Inventory::EQUIPPED ? $item->bodypart : $item->storage).'<br />';

				//if guid, show it, just from curiosity
				$guid = ($item->GUID != '') ? '<br />'.$item->GUID : '';

				//item tooltip with all the properties
				$tooltip = $this->ItemTooltip($item);

				echo '
				<tr>
					<td>'.$n.'</td>
					<td class="'.$icolor.'">'.$item->item_name.'</td>
					<td>'.$item->basename.'</td>
					<td>'.$item->item_type.' '.$item->type.'</td>
					<td>'.$item->item_rank.'</td>
					<td class="mono">'.$item->fingerprint.$guid.'</td>
					<td class="ac">'.($item->ethereal ? '1' : '').'</td>
					<td class="ac">'.($item->runeword ? '1' : '').'</td>
					<td class="ac">'.$socket.'</td>
					<td class="al vac">'.$socketitems.'</td>
					<td class="ac">'.$locinfo.'</td>
					<td class="ac tooltip">'.$im.$tooltip.'</td>
				</tr>';
				$n++;
			}
			echo '</table>';

			if(exget('cc')) {
				$this->ShowItemCounts();
			}
		}

		//show item counts
		public function ShowItemCounts() {
			echo '<table>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Base</th>
					<th>Type</th>
					<th>Rank</th>
					<th>Image</th>
					<th>Count</th>
				</tr>';

			$n = 1;
			//usort($itemlist, 'ItemSort');
			foreach($this->icounts as $itemc) {
				$item = $itemc[1];
				$icolor = $this->GetMagicRankColor($item->magic_rank, $item->runeword);
				$im = $this->GetImage($item->gfx, $item->baseTrans, $item->magicTrans);


				echo '
				<tr>
					<td>'.$n.'</td>
					<td class="'.$icolor.'">'.$item->item_name.'</td>
					<td>'.$item->basename.'</td>
					<td>'.$item->item_type.' '.$item->type.'</td>
					<td>'.$item->item_rank.'</td>
					<td class="ac tooltip">'.$im.'</td>
					<td class="ar">'.$itemc[0].' ×</td>
				</tr>';
				$n++;
			}
			echo '</table>';
		}

		//color for title name
		public function GetMagicRankColor($magicrank, $isruneword) {
			if($isruneword) return 'iruneword';

			switch($magicrank) {
				case D2MagicRank::LOWQUALITY:
				case D2MagicRank::NORMQUALITY:
				case D2MagicRank::HIGHQUALITY: return 'inormal';
				case D2MagicRank::MAGIC: return 'imagic';
				case D2MagicRank::CRAFT: return 'icraft';
				case D2MagicRank::RARE: return 'irare';
				case D2MagicRank::SET: return 'iset';
				case D2MagicRank::UNIQUE: return 'iunique';
				case D2MagicRank::RUNEWORD: return 'iruneword';
				default: return '';
			}
		}

		//convert prop to itemcost
		public function PropToStat($pcode, $pmin, $pmax, $param, $qflag) {
			global $D2DATA;

			$props = array();
			$prop = array();
			$icost = null;
			foreach($D2DATA->PROPERTIES as $property) {
				if($property['code'] != $pcode) continue;
				$prop = $property;
				break;
			}

			if(empty($prop)) return;

			//get itemcost stat names
			for($i = 1; $i <= 7; $i++){
				$propx = $prop['stat'.$i];
				if($propx == ''){
					break;
				}

				$vals = array(0, 0, 0);

				if($pmin != '') {
					$vals[0] = (int)$pmin;
				}
				if($pmax != '') {
					$vals[1] = (int)$pmax;
				}
				if($param != '') {
					$vals[2] = $param;
				}

				//mostly val[0] is used only
				//if prop has 'max' substrign, we add change val[0] to max
				if(stripos($propx, 'max') !== false) {
					$vals[0] = $vals[1];
				}
				//when 'length', we add param to val[0]
				elseif(stripos($propx, 'length') !== false && $vals[2] != 0) {
					$vals[0] = $vals[2];
				}
				//always clear val[2]
				$vals[2] = 0;

				//for this prop, use val1 value from txt file. Param decides only class, this value is constant
				if($propx == 'item_addclassskills') {
					$vals[0] = $prop['val1'];
				}

				foreach($D2DATA->ICOST as $icosta) {
					if($icosta['Stat'] != $propx) continue;
					$icost = $icosta;
					break;
				}

				if(!$icost) continue; //icost not found, skip

				$props[] = array(
					'qflag' => $qflag,
					'propid' => $icost['ID'],
					'icost' => $icost,
					'props' => $vals,
				);
			}
			return $props;
		}

		//read whole set props
		public function ReadSetPropeties() {
			global $D2DATA;
			foreach($D2DATA->SETS as $k => $set) {
				for($i = 2; $i <= 5; $i++) {
					if($set['PCode'.$i.'a'] == '') continue;
					$code = $set['PCode'.$i.'a'];
					$param = $set['PParam'.$i.'a'];
					$min = $set['PMin'.$i.'a'];
					$max = $set['PMax'.$i.'a'];
					//qflag to differentiate partial props A: 10, 12, 14, 16
					$this->setprops[$set['index']][] = $this->PropToStat($code, $min, $max, $param, 10 + ($i - 2) * 2);

					if($set['PCode'.$i.'b'] == '') continue;
					$code = $set['PCode'.$i.'b'];
					$param = $set['PParam'.$i.'b'];
					$min = $set['PMin'.$i.'b'];
					$max = $set['PMax'.$i.'b'];
					//qflag to differentiate partial props B: 11, 13, 15, 17
					$this->setprops[$set['index']][] = $this->PropToStat($code, $min, $max, $param, 10 + ($i - 2) * 2 + 1);
				}

				for($i = 1; $i <= 8; $i++) {
					if($set['FCode'.$i] == '') continue;
					$code = $set['FCode'.$i];
					$param = $set['FParam'.$i];
					$min = $set['FMin'.$i];
					$max = $set['FMax'.$i];
					//qflag to differentiate full set props: 21-28
					$this->setprops[$set['index']][] = $this->PropToStat($code, $min, $max, $param, 20 + $i);
				}
			}
		}

		/*
		DescFunc - the function used for generating the description for this stat, this works like the descfuncs in SkillDesc.txt pretty much. See
		below for an explanation of individual functions, these are listed the way they would show up if DescVal is set to 1. This follows the
		syntax Char used in the old fileguide, but includes the functions he didn't cover.

		1 - +[value] [string1]
		2 - [value]% [string1]
		3 - [value] [string1]
		4 - +[value]% [string1]
		5 - [value*100/128]% [string1]
		6 - +[value] [string1] [string2]
		7 - [value]% [string1] [string2]
		8 - +[value]% [string1] [string2]
		9 - [value] [string1] [string2]
		10 - [value*100/128]% [string1] [string2]
		11 - Repairs 1 Durability In [100 / value] Seconds
		12 - +[value] [string1]
		13 - +[value] to [class] Skill Levels
		14 - +[value] to [skilltab] Skill Levels ([class] Only)
		15 - [chance]% to cast [slvl] [skill] on [event]
		16 - Level [sLvl] [skill] Aura When Equipped
		17 - [value] [string1] (Increases near [time])
		18 - [value]% [string1] (Increases near [time])
		19 - this is used by stats that use sprintf
		20 - [value * -1]% [string1]
		21 - [value * -1] [string1]
		22 - [value]% [string1] [montype]
		23 - [value]% [string1] [monster]
		24 - used for charged props
		25 - not used by vanilla, present in the code but I didn't test it yet
		26 - not used by vanilla, present in the code but I didn't test it yet
		27 - +[value] to [skill] ([class] Only)
		28 - +[value] to [skill]

		50 to 57 - specific, grouped from D2Item::MachProperties function

		DescVal - Controls whenever and if so in what way the stat value is shown, 0 = doesn't show the value of the stat, 1 = shows the value of
		the stat infront of the description, 2 = shows the value of the stat after the description.
		*/

		public function GetPropText($propid, $descfunc, $descval, $string, $string2, $val) {
			$text = '';
			switch($descfunc) {
				case 1:
					switch($descval) {
						case 1:
							if(strpos($string, '%d') !== false) {
								return sprintf($string, $val[0]);
							}
							elseif($val[0] >= 0) {
								return '+'.$val[0].' '.$string;
							}
							else {
								return $val[0].' '.$string;
							}
						case 2:
							if($val[0] >= 0) {
								return $string.' +'.$val[0];
							}
							else {
								return $string.' '.$val[0];
							}
						default: return $string;
					}
				case 2:
					switch($descval) {
						case 1: return $val[0].'% '.$string;
						case 2: return $string.' '.$val[0].'%';
						default: return $string;
					}
				case 3:
					if($propid == 253) {
						return $string.' [1 in '.(int)(100 / $val[0]).' sec.]';
					}
					switch($descval) {
						case 1: return $val[0].' '.$string;
						case 2: return $string.' '.$val[0];
						default: return $string;
					}
				case 4:
					switch($descval) {
						case 1: return '+'.$val[0].'% '.$string;
						case 2:
							if($val[0] >= 0) {
								return $string.' +'.$val[0].'%';
							}
							else {
								return $string.' '.$val[0].'%';
							}
						default: return $string;
					}
				case 5:
					switch($descval) {
						case 1: return ($val[0] * 100 / 128).'% '.$string;
						case 2: return $string.' '.($val[0] * 100 / 128).'%';
						default: return $string;
					}
				case 6: return '+'.$val[0].' '.$string.' '.$this->GetString($string2);
				case 7: return $val[0].'% '.$string.' '.$this->GetString($string2);
				case 8:
					switch($descval) {
						case 1: return '+'.$val[0].'% '.$string.' '.$this->GetString($string2);
						case 2: return $string.' '.$this->GetString($string2).' +'.$val[0].'%';
						default: return $string;
					}
				case 9:
					switch($descval) {
						case 1: return $val[0].' '.$string.' '.$this->GetString($string2);
						case 2: return $string.' '.$this->GetString($string2).' '.$val[0];
						default: return $string;
					}
				case 10: return ($val * 100 / 128).'% '.$string.' '.$this->GetString($string2);
				case 11: return 'Repairs 1 Durability In '.(int)(100 / $val[0]).' Seconds';
				case 12:
					switch($descval) {
						case 1: return '+'.$val[0].' '.$string;
						case 2: return $string.' +'.$val[0];
						default: return $string;
					}
				case 13: return '+'.$val[1].' to '.GetCharString($val[0]).' Skill Levels';
				case 14: return '+'.$val[1].' to '.GetSkillTree($val[0]);
				case 15:
					$item = new D2Item();
					$skill = $item->GetSKILLS($val[1]);
					$skillstr = $item->GetSKILLDESC($skill['skilldesc']);
					$string2 = $this->GetString($skillstr['str name']);
					return sprintf($string, $val[2], $val[0], $string2); //[chance]% to cast [slvl] [skill] on [event]
				case 16:
					$item = new D2Item();
					$skill = $item->GetSKILLS($val[0]);
					$skillstr = $item->GetSKILLDESC($skill['skilldesc']);
					$string2 = $this->GetString($skillstr['str name']);
					return sprintf($string, $val[1], $string2); //Level [sLvl] [skill] Aura When Equipped
				case 17: return $val[0].' '.$string1.' (Increases near '.$val[1].')';
				case 18:
					switch($descval) {
						case 1: return $val[0].'% '.$string1.' (Increases near '.$val[1].')';
						case 2: return $string1.' '.$val[0].'% (Increases near '.$val[1].')';
						default: return $string;
					}
				//case 19: not used in itemstat cost
				case 20: return (-$val[0]).'% '.$string;
				case 21: return (-$val[0]).' '.$string1;
				//case 22: [value]% [string1] [montype]
				//case 23: [value]% [string1] [monster]
				case 24:
					$item = new D2Item();
					$skill = $item->GetSKILLS($val[1]);
					$skillstr = $item->GetSKILLDESC($skill['skilldesc']);
					$string2 = $this->GetString($skillstr['str name']);
					return 'Level '.$val[0].' '.$string2.' '.sprintf($string, $val[2], $val[3]);
					//used for charged props
				//case 25: not used
				//case 26: not used
				case 27:
					$item = new D2Item();
					$skill = $item->GetSKILLS($val[0]);
					$skillstr = $item->GetSKILLDESC($skill['skilldesc']);
					$string2 = $this->GetString($skillstr['str name']);
					return '+'.$val[1].' to '.$string2.' '.$item->GetString(ucfirst($skill['charclass']).'Only');
				case 28:
					$item = new D2Item();
					$skill = $item->GetSKILLS($val[0]);
					$skillstr = $item->GetSKILLDESC($skill['skilldesc']);
					$string2 = $this->GetString($skillstr['str name']);
					return '+'.$val[1].' to '.$string2;
				case 50: return $val[0].'% Enhanced Damage';
				case 51: return 'Adds '.$val[0].' - '.$val[1].' Fire Damage';
				case 52: return 'Adds '.$val[0].' - '.$val[1].' Lightning Damage';
				case 53: return 'Adds '.$val[0].' - '.$val[1].' Magic Damage';
				case 54: return 'Adds '.$val[0].' - '.$val[1].' Cold Damage Over '.round($val[2] / 25).' Secs';
				case 55: return 'Adds '.$val[0].' - '.$val[1].' Poison Damage Over '.round($val[2] / 25).' Secs';
				case 56: return '+'.$val[0].' To All Attributes';
				case 57: return 'All Resistances +'.$val[0];
				default: return $string.' {'.$propid.'} ('.$descfunc.') ['.$descval.'] -> '.implode($val, ', ');
			}
		}


		//get tooltip of whole item props
		public function ItemTooltip($item) {
			$props = array();
			$moddam = $moddef = 1;
			$itemsetcount = 0;
			$quest = -1;

			//check if set item has more friends in inventory
			if($this->char_current >= 0 && array_key_exists($item->set_name, $this->characters[$this->char_current]->sets)) {
				$itemsetcount = count($this->characters[$this->char_current]->sets[$item->set_name]);
			}

			//go through props
			foreach($item->properties as $prop) {
				$qflag = $prop['qflag'];
				$propid = $prop['propid'];
				$order = $prop['icost']['descpriority'];

				$descfunc = $prop['icost']['descfunc'];
				//quest prop, save to flag
				if($propid == 356) {
					$quest = $prop['props'][0];
				}

				//no desc function, nothing to show, skip
				if($descfunc == '' || $descfunc >= 100) continue;

				$descval = $prop['icost']['descval'];
				$string = $item->GetString($prop['icost']['descstrpos']);
				$str = $this->GetPropText($propid, $descfunc, $descval, $string, $prop['icost']['descstr2'], $prop['props']).EOL;

				//socketable item have properties based on type of item they go in
				if($item->socketable && !$item->isJewel) {
					$gtype = $prop['gtype'];
					switch($prop['gtype']) {
						case 'w': $str = 'Weapons: '.$str; break;
						case 'a': $str = 'Helms, Armor: '.$str; break;
						case 's': $str = 'Shields: '.$str; break;
					}
				}

				//qflag for partial set props, only set items
				if($qflag >= 2) {
					$cls = ($itemsetcount >= $qflag) ? 'isetn' : 'isetnr';
					$str = '<span class="'.$cls.'">'.$str.'</span> <span class="inormaln">('.$qflag.' items)</span>'.EOL;
				}

				$props[] = array($order, $str, $qflag);
			}

			//dont sort for runes and gems, as it would change order of weapon/armor/shield properties
			if(!$item->socketable || $item->isJewel) {
				usort($props, 'PropSort');
			}

			//title name color
			$icolor = $this->GetMagicRankColor($item->magic_rank, $item->runeword);

			$tooltip = '<p><span class="'.$icolor.'">'.$item->item_name.'</span><br />'.EOL;

			//show socket and ethereal items in gray
			$basecl = '';
			if($item->sockets > 0 || $item->ethereal) {
				$basecl = ' class="isocketh"';
			}

			//change base, when there is spelldesc, mostly potions
			$base = $item->spelldesc != '' ? $item->spelldesc : $item->basename;
			$tooltip .= '<span'.$basecl.'>'.$base.'</span><br />'.EOL;

			//socketed runes
			if($item->runes_title != '') {
				$tooltip .= '<span>'.$item->runes_title.'</span><br />'.EOL;
			}

			//adjust defense, damage, and other item modificators
			if($item->item_type == 'armor') {
				$defmag = '';
				if($item->defmult != 100 || $item->defadd > 0) {
					$defmag = ' class="magictext"';
					$moddef = $item->defmult / 100;
				}
				if($item->ethereal) {
					$moddef *= 1.5;
				}

				$tooltip .= '<span>Defense: </span><span'.$defmag.'>'.(int)($item->defense * $moddef + $item->defadd).'</span><br />'.EOL;
			}
			elseif($item->item_type == 'weapon') {
				$dammag = '';
				if($item->dammult != 100 || $item->damminadd > 0 || $item->dammaxadd > 0) {
					$dammag = ' class="magictext"';
					$moddam = $item->dammult / 100;
				}
				if($item->ethereal) {
					$moddam *= 1.5;
				}

				if($item->mindam) {
					$tooltip .= '<span>One Hand Damage: </span><span'.$dammag.'>'.(int)($item->mindam * $moddam + $item->damminadd).' to '.(int)($item->maxdam * $moddam + $item->dammaxadd).'</span><br />'.EOL;
				}
				if($item->mindam2) {
					$tooltip .= '<span>Two Hand Damage: </span><span'.$dammag.'>'.(int)($item->mindam2 * $moddam + $item->damminadd).' to '.(int)($item->maxdam2 * $moddam + $item->dammaxadd).'</span><br />'.EOL;
				}
				if($item->mindammi) {
					$tooltip .= '<span>Throwing Damage: </span><span'.$dammag.'>'.(int)($item->mindammi * $moddam + $item->damminadd).' to '.(int)($item->maxdammi * $moddam + $item->dammaxadd).'</span><br />'.EOL;
				}
			}

			//some more common props
			if($item->MaxDur > 0) {
				$tooltip .= '<span>Durability: '.$item->CurDur.' of '.$item->MaxDur.'</span><br />'.EOL;
			}
			if($item->reqstr > 0) {
				$tooltip .= '<span>Required Strength: '.$item->reqstr.'</span><br />'.EOL;
			}
			if($item->reqdex > 0) {
				$tooltip .= '<span>Required Dexterity: '.$item->reqdex.'</span><br />'.EOL;
			}
			if($item->reqlvl) {
				$tooltip .= '<span>Required Level: '.$item->reqlvl.'</span><br />'.EOL;
			}
			if($item->item_type == 'weapon') {
				$tooltip .= '<span>Speed: '.$item->speed.'</span><br />'.EOL;
			}
			$tooltip .= '<span class="exinfotext">Item version: '.$item->version.'</span><br />'.EOL;
			if($item->itemlvl > 0) {
				$tooltip .= '<span class="exinfotext">Item Level: '.$item->itemlvl.'</span><br />'.EOL;
			}
			if($item->fingerprint != '') {
				$tooltip .= '<span class="exinfotext">Fingerprint: '.$item->fingerprint.'</span><br />'.EOL;
			}
			//show info, when item is quest item and is dependant on difficulty
			if($quest >= 0 || $item->questdif >= 0) {
				$quest = max($quest, $item->questdif);
				$qdif = array('Normal', 'Nightmare', 'Hell');
				$tooltip .= '<span class="exinfotext">Quest Item Difficulty: '.$qdif[$quest].'</span><br />'.EOL;
			}

			//show all props
			$tooltip .= '<span class="magictext">';
			foreach($props as $prop) {
				$tooltip .= $prop[1].'<br />';
			}
			$tooltip .= '</span>'.EOL;

			//if socketed or ethereal, show it
			if($item->sockets) {
				$tooltip .= '<span>Socketed ('.$item->SocketsNum.' : '.$item->SocketsFilled.' used)</span><br />'.EOL;
			}
			if($item->ethereal) {
				$tooltip .= '<span>Ethereal</span><br />'.EOL;
			}

			//make tooltip and show also socketed items
			foreach($item->SocketItems as $k => $sitem) {
				if(substr($item->item_code, 0, 1) == 'r') {
					$tooltip .= str_replace('Rune', '', $sitem->item_name);
				}
				$tooltip .= $this->GetImage($sitem->gfx, $sitem->baseTrans, $sitem->magicTrans).' ';
			}

			//display items of the set
			if($item->magic_rank == D2MagicRank::SET) {
				global $D2DATA;

				$char = $this->char_current >= 0 ? $this->characters[$this->char_current] : -1;

				$tooltip .= '<br /><span class="iset">'.$this->GetString($item->set_name).'</span><br />'.EOL;

				$setok = $this->char_current >= 0 ? array_key_exists($item->set_name, $char->sets) : true;
				//set items list
				foreach($D2DATA->SET as $setitem) {
					if($item->set_name != $setitem['set']) continue;

					$cls = 'isetn';
					if($setok && ($this->char_current >= 0) && !in_array($setitem['index'], $char->sets[$item->set_name])) {
						$cls = 'isetnr';
					}

					$tooltip .= '<span class="'.$cls.'">'.$this->GetString($setitem['index']).'</span><br />'.EOL;
				}

				//partial/full set props
				$tooltip .= '<span class="uniquetext">';
				if(array_key_exists($item->set_name, $this->setprops)) {
					$itemtmp = new D2Item();

					//get set props to temporary item
					foreach($this->setprops[$item->set_name] as $setprops) {
						foreach($setprops as $setprop) {
							$itemtmp->properties[] = $setprop;
							$itemtmp->propids[] = $setprop['propid'];
						}
					}
					//match props
					$itemtmp->MatchProperties();

					$setprops = array('p' => array(), 'f' => array());
					//get text for props, divide to partial/full, each will sort differently
					foreach($itemtmp->properties as $setprop) {
						$qflag = $setprop['qflag'];
						$propid = $setprop['propid'];
						$order = $setprop['icost']['descpriority'];
						$descfunc = $setprop['icost']['descfunc'];

						if($descfunc == '' || $descfunc >= 100) continue;

						$descval = $setprop['icost']['descval'];
						$string = $item->GetString($setprop['icost']['descstrpos']);
						$str = $this->GetPropText($propid, $descfunc, $descval, $string, $setprop['icost']['descstr2'], $setprop['props']).EOL;

						if($qflag < 20) { //partial
							$setprops['p'][] = array($order, $str, $qflag);
						}
						else {
							//$qflag = 0; //full props will be ordered by order, nor qflag
							$setprops['f'][] = array($order, $str, 0);
						}
					}
					
					usort($setprops['p'], 'PropSort');
					usort($setprops['f'], 'PropSort');

					//add to tooltip
					$tooltip .= '<br /><strong class="uniquetext">Partial Set Bonus</strong><br />'.EOL;
					foreach($setprops['p'] as $setprop) {
						$qflag = $setprop[2];
						$numi = ((($qflag - 10) - ($qflag % 2)) / 2 + 2); //num items
						$tooltip .= $setprop[1].' ('.$numi.' Items)<br />';
					}
					$tooltip .= '<br /><strong class="uniquetext">Complete Set Bonus</strong><br />'.EOL;
					foreach($setprops['f'] as $setprop) {
						$tooltip .= $setprop[1].'<br />';
					}
				}
				$tooltip .= '</span>'.EOL;
			}

			$tooltip .= '</p>'.EOL;
			return $tooltip;
		}

		//get image, transform, if needed
		public function GetImage($gfx, $baseTrans, $magicTrans) {
			//return '0'; //dbg to not show image

			//image name
			if($magicTrans >= 0) {
				$imagefile = $gfx.'_'.$baseTrans.$magicTrans;
			}
			else {
				$imagefile = $gfx;
			}
			$D2image = new D2dc6($gfx, $imagefile);
			$image = $D2image->GetSingleImage($baseTrans, $magicTrans);
			return '<img src="'.$image.'" />';
		}

		//get string from tbl
		public function GetString($index) {
			global $D2DATA;
			if(array_key_exists($index, $D2DATA->STRINGS)) {
				return $D2DATA->STRINGS[$index];
			}
			else {
				return null;
			}
		}

	}


//item sort to table
function ItemSort($a, $b) {
	//simple
//return strcasecmp($a->item_name, $b->item_name);

	//advanced
		//item_type s
		//item_rank s
		//type s
		//--magic_rank
	$itype = strcasecmp($a->item_type, $b->item_type);
	if($itype != 0) {
		if($a->item_type == 'weapon') return -1;
		elseif($b->item_type == 'weapon') return 1;
		elseif($a->item_type == 'armor') return -1;
		elseif($b->item_type == 'armor') return 1;
	}

	$rank = strcasecmp($a->item_rank, $b->item_rank);
	if($rank != 0) return $rank;

	$type = strcasecmp($a->type, $b->type);
	if($type != 0) return $type;

	return strcasecmp($a->item_name, $b->item_name);
}

//props sort for tooltip
function PropSort($a, $b) {
	//0:order, 1:text, 2:qflag
	if($a[2] > $b[2]) return 1;
	elseif($a[2] < $b[2]) return -1;
	elseif($a[0] > $b[0]) return -1;
	elseif($a[0] < $b[0]) return 1;
	return strcasecmp($a[1], $b[2]);
}

//copy saves from game/custom location
function CopySaves() {
	$psaves = D2SAVE;
	$pstash = D2STASH;
	$ssaves = WORKSAVE;
	$sstash = WORKSTASH;

	$sd = ScanDirM($psaves, '/\.d2s/i');
	foreach($sd as $sf) {
		$pi = pathinfo($sf);
		$save = $pi['basename'];
		echo $sf.' -> '.$ssaves.$save.'<br />';
		copy($sf, $ssaves.$save);
	}

	$sd = ScanDirM($pstash, '/\.d2x/i');
	foreach($sd as $sf) {
		$pi = pathinfo($sf);
		$save = $pi['basename'];
		echo $sf.' -> '.$ssaves.$save.'<br />';
		copy($sf, $sstash.$save);
	}

}

//scandir for files
function ScanDirM($dir, $mask = '') {
	$files = array();
	if(!file_exists($dir)) return $files;
	$sd = scandir($dir);
	foreach($sd as $f) {
		if(is_dir($f)) continue;
		if($mask != '' && !preg_match($mask, $f)) continue;
		$files[] = $dir.$f;
	}
	return $files;
}


?>
</div>
</body>
</html>
