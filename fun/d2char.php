<?php

//mercenary class
class Mercenary {
	public $mercnameid;
	public $mercid;

	public $mercrace;
	public $merctype;
	public $mercnamef;

	public $mercname;
	public $mercexp;
	public $level;
}


//character class
class D2Character {

	private $bitreader;
	public $filename;
	public $golemItem;

	public $timestamp;
	public $mapseed;
	public $checksumSave;
	public $checksumCalc;

	public $charname;
	public $title;
	public $titlename;
	public $class;
	public $class_short;
	public $level;
	public $hardcore = 0;
	public $mercdead = 0;
	public $mercenary = null;
	public $curact;
	public $curdif;
	public $beltrows = 1;

	public $version;
	public $size;

	public $itemlist = array('c' => array(), 'm' => array());
	public $mercItems = array();
	public $corpseItems = array();

	public $quests = array();
	public $cowKingDead = array();
	public $waypoints = array();
	public $stats = array();
	public $skills = array();
	public $lastQuest = '';

	public $sets = array();


	//data positions
	private $Woo = 0; //quests pos
	private $WS = 0; //waypoinst pos
	private $W4 = 0; //NPC pos
	private $GF = 0; //stats pos
	private $IF = 0; //skills pos
	private $JF = 0; //classic char pos
	private $KF = 0; //golem item pos

	public function __construct($file) {
		$this->filename = $file;
		$data = file_get_contents($file);
		$this->bitreader = new myByteReader($data);

		$this->ReadCharacter();
		$this->bitreader = null;
	}

	public function ReadCharacter() {
		//first four bytes does not matter, always 55 AA 55 AA
		$this->bitreader->SkipBytes(4);

		$this->version = $this->bitreader->ReadUint32();

		if ($this->version != 0x60) {
			/*
			0x47  71  v1.00 through v1.06
			0x57  87  v1.07 or Expansion Set v1.08
			0x59  89  standard game v1.08
			0x5C  92  v1.09 (both the standard game and the Expansion Set.)
			0x60  96  v1.10+
			*/
			echo 'Bad character version<br />'; //precisely not 1.1+ version
			return;
		}

		$this->size = $this->bitreader->ReadUint32(); //size in bytes

		if ($this->bitreader->GetLength() != $this->size) {
			echo 'Wrong filesize<br />';
			return;
		}


		$this->checksumSave = $this->bitreader->ReadUint32(); //checksum
		//$this->bitreader->SkipBytes(4); //weapon set
		//$this->checksumCalc = $this->CalculateCheckSum();

		$this->bitreader->SetPos(20);

		//get char name
		for ($i = 0; $i < 16; $i++){
			$c = $this->bitreader->ReadUint8();
			if ($c != 0) {
				$this->charname .= chr($c);
			}
		}

		$this->bitreader->SetPos(36);
		$this->bitreader->SkipBits(2);
		$this->hardcore = ($this->bitreader->ReadBits(1) == 1);
		$this->bitreader->SetPos(37);

		//title for completing game
		$this->title = $this->bitreader->ReadUint8();

		$this->bitreader->SetPos(40);

		$charCode = $this->bitreader->ReadUint8();
		switch($charCode){
			case 0:	$this->class_short = 'ama'; $this->class = 'Amazon'; break;
			case 1: $this->class_short = 'sor'; $this->class = 'Sorceress'; break;
			case 2: $this->class_short = 'nec'; $this->class = 'Necromancer'; break;
			case 3: $this->class_short = 'pal'; $this->class = 'Paladin'; break;
			case 4: $this->class_short = 'bar'; $this->class = 'Barbarian'; break;
			case 5: $this->class_short = 'dru'; $this->class = 'Druid'; break;
			case 6: $this->class_short = 'ass'; $this->class = 'Assasin'; break;
		}

		$this->GetTitle();

		$this->bitreader->SetPos(43);

		$this->level = $this->bitreader->ReadUint8();

		if ($this->level < 1 || $this->level > 99 ) {
			echo 'Invalid level<br />';
			return false;
		}

		$this->bitreader->SetPos(48);
		$this->timestamp = $this->bitreader->ReadUint32(); //time when last saved

		//next are hotkeys which we skip
		//act and difficulty
		$this->bitreader->SetPos(168);

		//get act and dif , where char is
		$dif = array('N', 'NM', 'H');
		for($i = 0; $i < 3; $i++) {
			$location = $this->bitreader->ReadUint8();
			if($location > 0) {
				$this->curact = ($location & 0x07) + 1;
				$this->curdif = $dif[$i];
			}
		}

		$this->mapseed = dechex($this->bitreader->ReadUint32());

		$this->bitreader->SetPos(177);

		$this->mercdead = ($this->bitreader->ReadUint8() == 1); //merc alive/dead

		$this->bitreader->SkipBytes(1);

		if($this->bitreader->ReadUint32() != 0){
			$this->mercenary = new Mercenary();

			$this->mercenary->mercnameid = $this->bitreader->ReadUint16();
			$this->mercenary->mercid = $this->bitreader->ReadUint16();

			$mercinfo = $this->GetHIRELING($this->mercenary->mercid);

			$this->mercenary->mercrace = $mercinfo['Hireling'];
			$this->mercenary->merctype = $mercinfo['SubType'];
			$mercnamef = $mercinfo['NameFirst'];
			$this->mercenary->mercnamef = $mercnamef;

			$mercnamef = substr($mercnamef, 0, -2) . padleft((substr($mercnamef, -2) + $this->mercenary->mercnameid));
			$this->mercenary->mercname = $this->GetString($mercnamef);

			$this->mercenary->mercexp = $this->bitreader->ReadUint32();

			$this->SetMercLevel($mercinfo['Exp/Lvl']);
		}
		else{
			$this->bitreader->SkipBytes(8);
		}

		//find quest data position
		$this->Woo = $this->bitreader->FindPos('Woo!');
		if ( $this->Woo != 335 ) {
			echo 'Quest bad position<br />';
			return;
		}

		//find waypoint data position
		$this->WS = $this->bitreader->FindPos('WS', $this->Woo);

		//find and check npc data position
		$this->W4 = $this->bitreader->FindPos('w4', $this->Woo);
		if ($this->W4 != 714) {
			echo 'NPC state bad position<br />';
			return;
		}

		//find and check stats data position
		$this->GF = $this->bitreader->FindPos('gf', $this->W4);
		if ($this->GF != 765) {
			echo 'Stats bad position<br />';
			return;
		}

		//find and check skill data position
		$this->IF = $this->bitreader->FindPos('if', $this->GF);
		if ($this->IF === false) {
			echo 'Skills bad position<br />';
			return;
		}

		//find and check classic char position
		$this->JF = $this->bitreader->FindPos('jf', $this->IF);
		if ($this->JF === false) {
			echo 'Maybe classic character<br />';
			return;
		}

		//find and check golem item data position
		$this->KF = $this->bitreader->FindPos('kf', $this->IF);
		if ($this->KF !== false) {
			//$this->ReadGolem(); //TODO
		}

		if ($this->IF < $this->GF) {
			echo 'Stats or skills bad<br />';
			return;
		}

		$this->ReadQuests();
		$this->ReadWaypoints();
		$this->ReadStats();
		$this->ReadSkills();
		$this->ReadItemsList();
	}

	private function ReadQuests() {
		$this->bitreader->SetPos($this->Woo);
		//Skip the Woo
		$this->bitreader->SkipBytes(4);
		$this->bitreader->SkipBytes(4);

		//N NM H loop
		$difficulty = array('N', 'NM', 'H');

		foreach($difficulty as $d) { //difficulty
			//Read in Act 1, Act 2, Act 3
			for ($a = 1; $a <= 3; $a++) { //acts
				$this->bitreader->SkipBytes(4);
				for ($q = 1; $q <= 6; $q++) { //quests
					$this->quests[$d][$a][$q] = $this->bitreader->ReadBits(1);

					//cow king quest is here
					if($a == 1 && $q == 4) {
						$this->bitreader->SkipBits(9);
						$this->cowKingDead[$d] = $this->bitreader->ReadBits(1);
						$this->bitreader->SkipBits(5);
					}
					else{
						$this->bitreader->SkipBits(15);
					}
				}
			}

			$this->bitreader->SkipBytes(4);

			//Read in act 4
			for ($q = 1; $q <= 3; $q++) { //quests
				$this->quests[$d][4][$q] = $this->bitreader->ReadBits(1);
				$this->bitreader->SkipBits(15);
			}

			$this->bitreader->SkipBytes(2);
			$this->bitreader->SkipBytes(6);
			$this->bitreader->SkipBytes(2);
			$this->bitreader->SkipBytes(4);

			//Read in Act 5
			for ($q = 1; $q <= 6; $q++) { //quests
				$this->quests[$d][5][$q] = $this->bitreader->ReadBits(1);
				$this->bitreader->SkipBits(15);
			}
			$this->bitreader->SkipBytes(12);
		}

		//reorder mixed up quest, some of them are not in right indexes (acts 1, 3, 4)
		foreach($difficulty as $d) { //difficulty
			$tempq1 = $this->quests[$d][1][3];
			$tempq2 = $this->quests[$d][1][5];
			$this->quests[$d][1][3] = $this->quests[$d][1][4];
			$this->quests[$d][1][4] = $tempq2;
			$this->quests[$d][1][5] = $tempq1;

			$tempq1 = $this->quests[$d][3][1];
			$this->quests[$d][3][1] = $this->quests[$d][3][4];
			$this->quests[$d][3][4] = $tempq1;
			$tempq1 = $this->quests[$d][3][2];
			$this->quests[$d][3][2] = $this->quests[$d][3][3];
			$this->quests[$d][3][3] = $tempq1;

			$tempq1 = $this->quests[$d][4][2];
			$this->quests[$d][4][2] = $this->quests[$d][4][3];
			$this->quests[$d][4][3] = $tempq1;
		}
	}

	private function ReadWaypoints() {
		$this->bitreader->SetPos($this->WS);
		$this->bitreader->SkipBytes(10);

		$difficulty = array('N', 'NM', 'H');
		foreach($difficulty as $d) { //difficulty
				//Read in Act 1, Act 2, Act 3
			for ($a = 1; $a <= 5; $a++) { //acts
				for ($w = 1; $w <= 9; $w++) { //waypoints
					if($a == 4 && $w > 3) continue; //act 4 only 3 waypoints
					$this->waypoints[$d][$a][$w] = $this->bitreader->ReadBits(1);
				}
			}

			$this->bitreader->SkipBits(1);
			$this->bitreader->SkipBytes(19);
		}
	}

	private function ReadStats() {
		$this->bitreader->SetPos($this->GF);
		$this->bitreader->ReadUint8(); //has to be 103
		$this->bitreader->ReadUint8(); //has to be 102

		while(true)	{
			// read the stats
			$id = $this->bitreader->ReadBits(9);
			if ($id == 0x1ff ){
				break;
			}
			$icost = $this->GetICOST($id);
			$bits = $icost['CSvBits'];
			$this->stats[$icost['Stat']] = $this->bitreader->ReadBits($bits);
		}
	}

	private function ReadSkills() {
		$row = $this->GetSKILLSclass($this->class_short);

		$this->bitreader->SetPos($this->IF);

		$this->bitreader->SkipBytes(2);
		$tree = 0;
		$skillnum = array(0, 0, 0);

		for($s =0; $s < 30; $s++){
			$skill = $this->GetSKILLS($row['Id'] + $s);
			$skilldesc = $this->GetSKILLDESC($skill['skilldesc']);
			$tree = $skilldesc['SkillPage'];

			$this->skills[$tree-1][$skillnum[$tree-1]] = $skill['skill'].'='.$this->bitreader->ReadUint8();
			$skillnum[$tree-1]++;
		}
	}

	//char has several item list (char, corpse, merc, golem)
	//type:  c:char, m:merc, d:corpse, g:goelm
	private function ReadItemsList() {
		$jf = $this->bitreader->FindPos('jf', $this->IF);
		$kf = $this->bitreader->FindPos('kf', $this->IF);
		$endchar = $jf - 4; //characters end 4 bytes before jf, but we assume corpse has no items

		$start = $this->bitreader->FindPos('JM', $this->IF);
		$this->ReadItems($start, $endchar, 'c');

		$start = $this->bitreader->GetPos();
		$this->ReadItems($start, 0, 'd');

		$jf = $this->bitreader->ReadString(2);
		if($jf != 'jf') {
			echo 'No jf found, something bad, or classic char<br />';
		}

		if($this->mercenary != null) {
			$start = $this->bitreader->GetPos();
			$this->ReadItems($start, $kf, 'm');
		}

		$kf = $this->bitreader->ReadString(2);
		if($kf != 'kf') {
			echo 'No kf found, something bad, or classic char<br />';
		}

		$isgolem = $this->bitreader->ReadUint8();
		if($isgolem == 1) {
			$start = $this->bitreader->GetPos();
			$this->ReadItems($start, 0, 'g');
		}
	}

	private function ReadItems($start, $end, $type) {
		$this->bitreader->SetPos($start + 2); //+2 to skip JM header
		$numItems = $this->bitreader->ReadUint16();

		$this->itemlist[$type] = array();
		$collectSocketed = 0;
		$jumpOutSocket = false;

		for($i = 0; $i < $numItems; $i++) {
			$start = $this->bitreader->GetPos();
			$next = $this->bitreader->FindPos('JM', $start + 2); //+2 to skip item header JM when looking up for next

			if($next === false) { //if no next item, we get the remainder of the last item
				$next = $this->bitreader->GetLength() - 3;
			}

			$len = $next - $start;

			//read item
			$item = $this->bitreader->ReadString($len);

			$d2item = new D2Item($item);

			//if active belt, get rows
			if($d2item->body == D2Inventory::BELT) {
				$this->beltrows = $d2item->beltrows;
			}

			//we have to proccess socketed items
			if($collectSocketed == 0) { //if not socketed, add to list
				$this->itemlist[$type][] = $d2item;
			}
			else { //otherwise add to previous item, until colected all socketed items
				$this->itemlist[$type][count($this->itemlist[$type]) - 1]->SocketItems[] = $d2item;
			}

			//collected item, reduce number
			if($collectSocketed > 0) {
				$collectSocketed--;
				$i--; //socketed items are not counted in total item number
				if($collectSocketed == 0) {
					$this->itemlist[$type][count($this->itemlist[$type]) - 1]->RuneWordApply();
					//last socket of last item (which was socketed) was read, so jump out
					if($jumpOutSocket) {
						break;
					}
				}
			}
			if($d2item->SocketsFilled > 0) {
				$collectSocketed = $d2item->SocketsFilled;
				//if we have bad luck, and last item is socketed, decrease i, so we can read all socket items
				if($i == $numItems - 1) {
					$jumpOutSocket = true;
					$i--;
				}
			}

			if($next !== false) {
				$this->bitreader->SetPos($next);
			}
		}

	}

	private function GetTitle() {
	/*
	Value Standard        Hardcore          Value Expansion            Hardcode
	0-3   (no title)                        0-3   (no title)
	4-7   Sir/Dame        Count / Countess  5-8   Slayer               Destroyer
	8-11  Lord/Lady       Duke / Duchess    10-13 Champion             Conqueror
	12    Baron/Baroness  King / Queen      15    Patriarch/Matriarch  Guardian
	*/
		$titles = array(
			'S' => array(
				4 =>  array('', ''),
				9 =>  array('Slayer', 'Slayer'),
				14 => array('Champion', 'Champion'),
				15 => array('Patriarch', 'Matriarch')
			),
			'H' => array(
				0 =>  array('', ''),
				5 =>  array('Destroyer', 'Destroyer'),
				10 => array('Conqueror', 'Conqueror'),
				15 => array('Guardian', 'Guardian')
			),
		);

		$key = $this->hardcore ? 'H' : 'S';
		$gender = in_array($this->class_short, array('sor', 'ama', 'ass')) ? 1 : 0;
		foreach($titles[$key] as $bp => $titles) {
			if($this->title <= $bp) {
				$this->titlename = $titles[$gender];
				break;
			}
		}
	}

	public function LastDoneQuest() {
		foreach($this->quests as $ds => $d) {
			$n = 0;
			foreach($d as $as => $a) {
				foreach($a as $qs => $q) {
					if($q) {
						$this->lastQuest = $ds.' A'.$as.' Q'.$qs;
					}
				}
			}
		}
	}

	//display waypoints
	public function ShowQuestWaypoint() {
		$questnames = array(
			'Den of Evil', 'Sisters\' Burial Grounds', 'The Search for Cain', 'The Forgotten Tower', 'Tools of the Trade', 'Sisters to the Slaughter',
			'Radament\'s Lair', 'The Horadric Staff', 'The Tainted Sun', 'The Arcane Sanctuary', 'The Summoner', 'The Seven Tombs',
			'The Golden Bird', 'Blade of the Old Religion', 'Khalim\'s Will', 'Lam Esen\'s Tome', 'The Blackened Temple', 'The Guardian',
			'The Fallen Angel', 'The Hellforge', 'Terror\'s End',
			'Siege on Harrogath', 'Rescue on Mount Arreat', 'Prison of Ice', 'Betrayal of Harrogath', 'Rite of Passage', 'Eve of Destruction'
		);

		$waypoints = array(
			'Rogue Encampment', 'Cold Plains', 'Stony Field', 'Dark Wood', 'Black Marsh', 'Outer Cloister', 'Jail, level 1', 'Inner Cloister', 'Catacombs, level 2',
			'Lut Gholein', 'Sewers, level 2', 'Dry Hills', 'Halls of the Dead, level 2', 'Far Oasis', 'Lost City', 'Palace Cellar, level 1', 'Arcane Sanctuary', 'Canyon of the Magi',
			'Kurast Docks', 'Spider Forest', 'Great Marsh', 'Flayer Jungle', 'Lower Kurast', 'Kurast Bazaar', 'Upper Kurast', 'Travincal', 'Durance of Hate, level 2',
			'Pandemonium Fortress', 'City of the Damned', 'River of Flames',
			'Harrogath', 'Frigid Highlands', 'Arreat Plateau', 'Crystalline Passage', 'Halls of Pain', 'Glacial Trail', 'Frozen Tundra', 'The Ancients\' Way', 'Worldstone Keep, level 2'
		);

		$out = '<table>';
		foreach($this->quests as $kd => $d) {
			$n = 0;
			$out .= '<tr><td colspan="7">'.$kd.'</td></tr>';
			foreach($d as $k => $a) {
				$out .= '<tr><td>'.$k.'</td>';
				foreach($a as $q) {
					$done = $q ? ' class="done"' : '';
					$out .= '<td'.$done.'>'.$questnames[$n++].'</td>';
				}
				if($a == 4) $out .= '<td></td><td></td><td></td>';
				$out .= '</tr>';
			}
		}
		$out .= '</table><br /><br />';

		$out .= '<table>';
		foreach($this->waypoints as $kd => $d) {
			$n = 0;
			$out .= '<tr><td colspan="10">'.$kd.'</td></tr>';
			foreach($d as $k => $a) {
				$out .= '<tr><td>'.$k.'</td>';
				foreach($a as $w) {
					$done = $w ? ' class="done"' : '';
					$out .= '<td'.$done.'>'.$waypoints[$n++].'</td>';
				}
				if($a == 4) $out .= '<td></td><td></td><td></td><td></td><td></td><td></td>';
				$out .= '</tr>';
			}
		}
		$out .= '</table>';

		return $out;
	}

	//display skills
	public function ShowSkills() {
		$out = '<table>';
		foreach($this->skills as $tree => $skills) {
			$out .= '<tr><td>Tree '.($tree+1).'</td>';
			foreach($skills as $skill) {
				list($s, $sp) = explode('=', $skill);
				$out .= '<td class="skilldiv">'.$s.'</td><td>'.$sp.'</td>';
			}
			$out .= '</tr>';
		}
		$out .= '</table>';
		return $out;
	}

	//merc level calculation from exp
	private function SetMercLevel($explvl) {
		$xpOut = 0;
		$level = 0;
		while(true) {
			$xpOut = $explvl * $level * $level * ($level + 1);
			if($xpOut > $this->mercenary->mercexp) {
				$level--;
				break;
			}
			else{
				$level++;
			}
		}
		$this->mercenary->level = $level;
	}

	//char checksum
	private function CalculateCheckSum(){
		$this->bitreader->SetPos(0);
		$checksum = 0; // unsigned integer checksum
		for ($i = 0; $i < $this->bitreader->GetLength(); $i++){
			$byte = $this->bitreader->ReadUint8();
			if ($i >= 12 && $i <= 15) { //skip checksum bytes
				$byte = 0;
			}
			$checksum = (($checksum << 1) & 0xffffffff) + $byte + (($checksum & 0x80000000) != 0 ? 1 : 0);
		}
		return $checksum;
	}

	public function ShowChecksum() {
		return '0x'.dechex($this->checksumSave).' = 0x'.dechex($this->checksumCalc);
	}


	//get diablo TXT data
	public function GetString($index) {
		global $D2DATA;
		if(array_key_exists($index, $D2DATA->STRINGS)) {
			return $D2DATA->STRINGS[$index];
		}
		else {
			return null;
		}
	}

	public function GetICOST($id) {
		global $D2DATA;
		if(array_key_exists($id, $D2DATA->ICOST)) {
			return $D2DATA->ICOST[$id]; //['index'];
		}
		else {
			return null;
		}
	}

	private function GetHIRELING($mercid) {
		global $D2DATA;
		foreach($D2DATA->HIRELING as $hire) {
			if($hire['Id'] == $mercid) {
				return $hire;
			}
		}
		return null;
	}

	private function GetSKILLS($id) {
		global $D2DATA;
		if(array_key_exists($id, $D2DATA->SKILLS)) {
			return $D2DATA->SKILLS[$id];
		}
		return null;
	}

	private function GetSKILLSclass($class) {
		global $D2DATA;
		foreach($D2DATA->SKILLS as $row) {
			if($row['charclass'] == $class) {
				return $row;
			}
		}
		return null;
	}

	private function GetSKILLDESC($desc) {
		global $D2DATA;
		foreach($D2DATA->SKILLDESC as $row) {
			if($row['skilldesc'] == $desc) {
				return $row;
			}
		}
		return null;
	}

}

?>
