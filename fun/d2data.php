<?php

//class for getting diablo TXT data
class D2Data {

	//needed data arrays
	public $UNIQUE;
	public $SET;
	public $SETS;
	public $RUNEWORDS;
	public $ITEMALL;
	public $PROPERTIES;
	public $ICOST;
	public $AUTOMAGIC;
	public $MAGICPREFIX;
	public $MAGICSUFFIX;
	public $RAREPREFIX;
	public $RARESUFFIX;
	public $SKILLS;
	public $SKILLDESC;
	public $GEMS;
	public $HIRELING;

	//converted strings from all tbl files
	public $STRINGS = array();

	//file with converted strings
	private $stringfile = D2PATHFILES.'strings.txt';


	public function __construct() {
		$this->UNIQUE = $this->ReadItemsUnique();
		$this->ICOST = $this->ReadCosts();
		$this->PROPERTIES = $this->ReadProperies();
		$this->SETS = $this->ReadSets();
		$this->SET = $this->ReadSetItems();
		$this->RUNEWORDS = $this->ReadRuneWords();
		$this->AUTOMAGIC = $this->ReadAutoMagic();
		$this->MAGICPREFIX = $this->ReadMagicPrefix();
		$this->MAGICSUFFIX = $this->ReadMagicSuffix();
		$this->RAREPREFIX = $this->ReadRarePrefix();
		$this->RARESUFFIX = $this->ReadRareSuffix();
		$this->HIRELING = $this->ReadHireling();
		$this->SKILLDESC = $this->ReadSkillDesc();
		$this->SKILLS = $this->ReadSkills();
		$this->GEMS = $this->ReadGems();
		$this->ItemAll();
		$this->CountSetItemsInSet();

		//read string file, if not found, proccess tbl files
		if(!file_exists($this->stringfile)) {
			$this->ReadTblFile(D2PATHFILES.'string.tbl');
			$this->ReadTblFile(D2PATHFILES.'expansionstring.tbl');
			$this->ReadTblFile(D2PATHFILES.'patchstring.tbl');
			//$this->ShowStrings();
			$this->MakeStringTxtFile();
		}
		else {
			$this->LoadStringTxtFile();
		}
	}


	//there goes functions to get data from TXT files.
	//txtfile: name of txt without extension
	//select: data, we want to pick, rest is omitted

	public function ReadAutoMagic() {
		$txtfile = 'automagic';
		$select = array('Name', 'levelreq');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadMagicPrefix() {
		$txtfile = 'MagicPrefix';
		$select = array('Name', 'levelreq');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadMagicSuffix() {
		$txtfile = 'MagicSuffix';
		$select = array('Name', 'levelreq');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadRarePrefix() {
		$txtfile = 'rareprefix';
		$select = array('name');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadRareSuffix() {
		$txtfile = 'raresuffix';
		$select = array('name');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadHireling() {
		$txtfile = 'hireling';
		$select = array('Hireling', 'SubType', 'Id', 'NameFirst', 'Exp/Lvl');
		return $this->ReadTxtFile($txtfile, $select);
	}

	//ItemAll groups all item data from weapon/armor/misc files
	public function ItemAll() {
		$txtfile = 'armor';
		$select = array('name', 'code', 'block', 'alternategfx', 'normcode', 'ubercode', 'ultracode', 'invfile', 'stackable', 'gemsockets', 'type', 'uniqueinvfile',
			'setinvfile', 'InvTrans', 'reqstr', 'durability', 'levelreq', 'invwidth', 'invheight', 'mindam', 'maxdam', 'maxac', 'belt');
		$armor = $this->ReadTxtFile($txtfile, $select);
		foreach($armor as &$row) {
			$row['itype'] = 'armor';
			$row['reqdex'] = 0;
		}

		$txtfile = 'weapons';
		$select = array('name', 'type', 'code', 'alternateGfx', 'normcode', 'ubercode', 'ultracode', 'stackable', 'maxstack', 'gemsockets', 'invfile', 'uniqueinvfile',
			'setinvfile', 'InvTrans', 'mindam', 'maxdam', '1or2handed', '2handed', '2handmindam', '2handmaxdam', 'minmisdam', 'maxmisdam', 'speed', 'reqstr', 'reqdex',
			'durability', 'levelreq', 'invwidth', 'invheight', 'questdiffcheck');
		$weapon = $this->ReadTxtFile($txtfile, $select);
		foreach($weapon as &$row) {
			$row['itype'] = 'weapon';
		}

		$txtfile = 'misc';
		$select = array('name', 'code', 'alternategfx', 'invfile', 'stackable', 'maxstack', 'type', 'InvTrans', 'levelreq', 'invwidth', 'invheight', 'spelldescstr',
			'spelldesccalc', 'questdiffcheck');
		$misc = $this->ReadTxtFile($txtfile, $select);
		foreach($misc as &$row) {
			$row['itype'] = 'misc';
			$row['normcode'] = $row['code'];
			$row['ubercode'] = '';
			$row['ultracode'] = '';
			$row['uniqueinvfile'] = '';
			$row['setinvfile'] = '';
			$row['reqstr'] = 0;
			$row['reqdex'] = 0;
		}

		$this->ITEMALL = array_merge($armor, $weapon, $misc);
	}

	public function ReadRuneWords() {
		$txtfile = 'Runes';
		$select = array('Name', 'Rune Name', 'itype1', 'itype2', 'itype3', 'Rune1', 'Rune2', 'Rune3', 'Rune4', 'Rune5', 'Rune6',
			'T1Code1', 'T1Param1', 'T1Min1', 'T1Max1', 'T1Code2', 'T1Param2', 'T1Min2', 'T1Max2', 'T1Code3', 'T1Param3', 'T1Min3', 'T1Max3', 'T1Code4', 'T1Param4', 'T1Min4', 'T1Max4',
			'T1Code5', 'T1Param5', 'T1Min5', 'T1Max5', 'T1Code6', 'T1Param6', 'T1Min6', 'T1Max6', 'T1Code7', 'T1Param7', 'T1Min7', 'T1Max7');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadProperies() {
		$txtfile = 'Properties';
		$select = array('code', 'val1', 'stat1', 'stat2', 'stat3', 'stat4', 'stat5', 'stat6', 'stat7');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadCosts() {
		$txtfile = 'ItemStatCost';
		$select = array('Stat', 'ID', 'CSvBits', 'Save Bits', 'Save Add', 'Save Param Bits', 'descpriority', 'descfunc', 'descval',
			'descstrpos', 'descstr2', 'dgrp', 'dgrpfunc', 'dgrpval', 'dgrpstrpos');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadSkills() {
		$txtfile = 'skills';
		$select = array('skill', 'Id', 'charclass', 'skilldesc', 'reqlevel');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadSkillDesc() {
		$txtfile = 'skilldesc';
		$select = array('skilldesc', 'SkillPage', 'str name');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadItemsUnique() {
		$txtfile = 'UniqueItems';
		$select = array('index', 'lvl', 'lvl req', 'code', '*type', 'chrtransform', 'invtransform', 'invfile',
			'prop1', 'par1', 'min1', 'max1', 'prop2', 'par2', 'min2', 'max2', 'prop3', 'par3', 'min3', 'max3', 'prop4', 'par4', 'min4', 'max4',
			'prop5', 'par5', 'min5', 'max5', 'prop6', 'par6', 'min6', 'max6', 'prop7', 'par7', 'min7', 'max7', 'prop8', 'par8', 'min8', 'max8',
			'prop9', 'par9', 'min9', 'max9', 'prop10', 'par10', 'min10', 'max10', 'prop11', 'par11', 'min11', 'max11', 'prop12', 'par12', 'min12', 'max12'
		);
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadSetItems() {
		$txtfile = 'SetItems';
		$select = array('index', 'set', 'item', '*item', 'lvl req', 'invtransform',
			'prop1', 'par1', 'min1', 'max1', 'prop2', 'par2', 'min2', 'max2', 'prop3', 'par3', 'min3', 'max3', 'prop4', 'par4', 'min4', 'max4',
			'prop5', 'par5', 'min5', 'max5', 'prop6', 'par6', 'min6', 'max6', 'prop7', 'par7', 'min7', 'max7', 'prop8', 'par8', 'min8', 'max8',
			'prop9', 'par9', 'min9', 'max9',
			'aprop1a', 'apar1a', 'amin1a', 'amax1a', 'aprop2a', 'apar2a', 'amin2a', 'amax2a', 'aprop3a', 'apar3a', 'amin3a', 'amax3a', 'aprop4a', 'apar4a', 'amin4a', 'amax4a',
			'aprop5a', 'apar5a', 'amin5a', 'amax5a');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadSets() {
		$txtfile = 'Sets';
		$select = array('index', 'name', 'PCode2a', 'PParam2a', 'PMin2a', 'PMax2a', 'PCode2b', 'PParam2b', 'PMin2b', 'PMax2b',
			'PCode3a', 'PParam3a', 'PMin3a', 'PMax3a', 'PCode3b', 'PParam3b', 'PMin3b', 'PMax3b', 'PCode4a', 'PParam4a', 'PMin4a', 'PMax4a',
			'PCode4b', 'PParam4b', 'PMin4b', 'PMax4b', 'PCode5a', 'PParam5a', 'PMin5a', 'PMax5a', 'PCode5b', 'PParam5b', 'PMin5b', 'PMax5b',
			'FCode1', 'FParam1', 'FMin1', 'FMax1', 'FCode2', 'FParam2', 'FMin2', 'FMax2', 'FCode3', 'FParam3', 'FMin3', 'FMax3',
			'FCode4', 'FParam4', 'FMin4', 'FMax4', 'FCode5', 'FParam5', 'FMin5', 'FMax5', 'FCode6', 'FParam6', 'FMin6', 'FMax6',
			'FCode7', 'FParam7', 'FMin7', 'FMax7', 'FCode8', 'FParam8', 'FMin8', 'FMax8');
		return $this->ReadTxtFile($txtfile, $select);
	}

	public function ReadGems() {
		$txtfile = 'gems';
		$select = array('code', 'weaponMod1Code', 'weaponMod1Param', 'weaponMod1Min', 'weaponMod1Max',
			'weaponMod2Code', 'weaponMod2Param', 'weaponMod2Min', 'weaponMod2Max', 'weaponMod3Code', 'weaponMod3Param', 'weaponMod3Min', 'weaponMod3Max',
			'helmMod1Code', 'helmMod1Param', 'helmMod1Min', 'helmMod1Max', 'helmMod2Code', 'helmMod2Param', 'helmMod2Min', 'helmMod2Max', 'helmMod3Code', 'helmMod3Param', 'helmMod3Min', 'helmMod3Max',
			'shieldMod1Code', 'shieldMod1Param', 'shieldMod1Min', 'shieldMod1Max', 'shieldMod2Code', 'shieldMod2Param', 'shieldMod2Min', 'shieldMod2Max', 'shieldMod3Code',
			'shieldMod3Param', 'shieldMod3Min', 'shieldMod3Max');
		return $this->ReadTxtFile($txtfile, $select);
	}

	//saving item counts of each set, for character display of set items
	public function CountSetItemsInSet() {
		foreach($this->SET as $setitem) {
			$setname = $setitem['set'];
			foreach($this->SETS as $k => $set) {
				if($set['index'] == $setname) {
					if(!array_key_exists('count', $set)) {
						$this->SETS[$k]['count'] = 0;
						$this->SETS[$k]['strname'] = $this->GetString($set['name']);
					}
					$this->SETS[$k]['count']++;
					break;
				}
			}
		}
	}

	//reading of txt files
	public function ReadTxtFile($txtfile, $select) {
		$path = D2PATHTXT;
		$ext = '.txt';
		if(!file_exists($path.$txtfile.$ext)) {
			echo 'File ['.$path.$txtfile.$ext.'] not found<br />';
			return;
		}
		$filedata = file($path.$txtfile.$ext);
		$txtdata = array();
		$header = array();

		//in some files skip Expansion index, because it does not count
		$skipExp = ($txtfile == 'UniqueItems' || $txtfile == 'SetItems') ? true : false;
		$indexExp = 'Expansion';

		foreach($filedata as $k => $data) {
			$data = explode(TAB, $data);
			if($k == 0) {
				//get indexes of data we want to gather them
				foreach($data as $k => $title) {
					$title = trim($title);
					if(!in_array($title, $select)) continue;
					$header[$title] = $k;
				}
				continue;
			}

			if($skipExp && $data[0] == $indexExp) continue; //skipping Expansion text for some files, otherwise it breaks indexing from various item props

			$row = array();
			foreach($select as $title) {
				$row[$title] = trim($data[$header[$title]]);
			}
			$txtdata[] = $row;
		}
		$filedata = null;
		return $txtdata;
	}

	//dbg function
	public function ShowTxt($txtdata) {
		foreach($txtdata as $n => $d) {
			echo "$n ** ";
			foreach($d as $k => $v) {
				echo "$k: $v, ";
			}
			echo '<br />';
		}
	}

	//get string from string array
	public function GetString($index) {
		if(array_key_exists($index, $this->STRINGS)) {
			return $this->STRINGS[$index];
		}
		else {
			return '???';
		}
	}

	//dbg function
	public function ShowStrings() {
		echo '<table>';
		$n = 1;
		$empty = 0;
		foreach($this->STRINGS as $strid => $strname) {
			echo "<tr><td>$n</td><td>$strid</td><td>[$strname]</td></tr>";
			if(trim($strname) == '') $empty++;
			$n++;
		}
		echo '</table>';
		echo $empty.'<br /><br />';
	}

	//read tbl file and save to string array and file
	public function ReadTblFile($tblfile) {

		if(!file_exists($tblfile)) {
			echo 'File ['.$tblfile.'] not found<br />';
			return;
		}

		$data = file_get_contents($tblfile);

		$bit = new myByteReader($data);

		$bit->SkipBytes(2);

		$numstrID = $bit->ReadUint16();
		$numstr = $bit->ReadUint16();

		//$bit->SkipBytes(3);
		//$first = $bit->ReadUint32(); //first string pos
		//$bit->SkipBytes(4);
		//$last = $bit->ReadUint32(); //last string pos

		$bit->SkipBytes(15); //we dont need the data, so we just skip it 3+4+4+4

		/*for($i = 0; $i < $numstrID; $i++) {
			$id = $bit->ReadUint16();
		}*/
		$bit->SkipBytes($numstrID * 2); //we dont need the data, so we just skip it 2*$numstrID


		/*for($i = 0; $i < $numstr; $i++) {
			$bit->SkipBytes(1);
			$id = $bit->ReadUint16();
			$bit->SkipBytes(4);
			$ofsname = $bit->ReadUint32();
			$ofsstr = $bit->ReadUint32();
			$strlen = $bit->ReadUint16();
		}*/
		$bit->SkipBytes($numstr * 17); //we dont need the data, so we just skip it (1+2+4+4+4+2)*$numstr

		$even = 0;
		for($i = 0; $i < $numstrID; $i++) {
			$strid = trim($bit->ReadString());
			$strname = $bit->ReadString();
			$this->STRINGS[$strid] = $strname;
		}
	}

	//make string file from string array, so we dont have to read tbl files everytime
	//also skip string with EOL in them, because it's not needed for our purposes and it's mostly NPC chat anyway
	public function MakeStringTxtFile() {
		$pairs = '';
		foreach($this->STRINGS as $strid => $strname) {
			if(strpos($strname, EOL) !== false) continue; //skip string with EOL
			$pairs .= $strid.TAB.str_replace(EOL, '\\n', $strname).EOL;
		}
		file_write($this->stringfile, $pairs);
	}

	//load string file
	public function LoadStringTxtFile() {
		$data = file($this->stringfile);
		foreach($data as $str) {
			$str = str_replace(EOL, '', $str);
			list($strid, $strname) = explode(TAB, $str);
			$this->STRINGS[$strid] = $strname;
		}
	}

}

//get char and skilltree based on number. For some item props. It's easier as defined arrays, then getting from string array

function GetCharString($charnum) {
	switch($charnum){
		case 0:	return 'Amazon';
		case 1: return 'Sorceress';
		case 2: return 'Necromancer';
		case 3: return 'Paladin';
		case 4: return 'Barbarian';
		case 5: return 'Druid';
		case 6: return 'Assasin';
	}
}

function GetSkillTree($num) {
	switch ($num) {
		//read                                                       //write  num - 5*classnum
		case 0:  return 'Bow and Crossbow Skills (Amazon Only)';     //0
		case 1:  return 'Passive and Magic Skills (Amazon Only)';    //1
		case 2:  return 'Javelin and Spear Skills (Amazon Only)';    //2
		case 8:  return 'Fire Skills (Sorceress Only)';              //3
		case 9:  return 'Lightning Skills (Sorceress Only)';         //4
		case 10: return 'Cold Skills (Sorceress Only)';              //5
		case 16: return 'Curses (Necromancer only)';                 //6
		case 17: return 'Poison and Bone Skills (Necromancer Only)'; //7
		case 18: return 'Summoning Skills (Necromancer Only)';       //8
		case 24: return 'Combat Skills (Paladin Only)';              //9
		case 25: return 'Offensive Aura Skills (Paladin Only)';      //10
		case 26: return 'Defensive Aura Skills (Paladin Only)';      //11
		case 32: return 'Combat Skills (Barbarian Only)';            //12
		case 33: return 'Masteries Skills (Barbarian Only)';         //13
		case 34: return 'Warcry Skills (Barbarian Only)';            //14
		case 40: return 'Summoning Skills (Druid Only)';             //15
		case 41: return 'Shape-Shifting Skills (Druid Only)';        //16
		case 42: return 'Elemental Skills (Druid Only)';             //17
		case 48: return 'Trap Skills (Assassin Only)';               //18
		case 49: return 'Shadow Discipline Skills (Assassin Only)';  //19
		case 50: return 'Martial Art Skills (Assassin Only)';        //20
	}
	return 'Unknown Tree ('.$num.')';
}
?>
