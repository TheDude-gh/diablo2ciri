<?php

class D2Item {

	private $bitreader;

	public $basename = '';  //name of the base item
	public $item_name = ''; //name string
	public $item_rank = ''; //normal/exceptional/elite
	public $item_type = ''; //basic type: armor/weapon/misc

	//flags
	public $identified = 0;
	public $sockets = 0;
	public $ear = 0;
	public $starter = 0;
	public $compact= 0;
	public $ethereal;
	public $personalized;
	public $runeword;
	public $version;

	public $runeword_name = '';
	public $runes_title = '';

	//placement
	public $location = 0;
	public $body = 0;
	public $col = 0;
	public $row = 0;
	public $container = 0;
	public $parent;
	public $storage;
	public $bodypart;
	public $invH = 0;
	public $invW = 0;
	public $beltrows = 1;

	//features
	public $item_code = ''; //3 letter code from txt
	public $SocketsFilled = 0;
	public $SocketsNum = 0;
	public $socketable = false; //gem, rune, jewel
	public $fingerprint = '';
	public $itemlvl = 0; //item level
	public $quality = 0;

	public $isCharm = false;
	public $isJewel = false;
	public $magic_rank = 'Normal'; //normal/magic/rare/crafted/set/unique
	public $set_id = 0;  //set item id from txt
	public $set_item = '';
	public $set_name = 0;  //set name, if it is set item,
	public $personname = '';
	public $questdif = -1;

	public $gold;
	public $GUID = '';
	public $defense = 0;
	public $mindam = 0;
	public $maxdam = 0;
	public $mindam2 = 0;
	public $maxdam2 = 0;
	public $mindammi = 0;
	public $maxdammi = 0;
	public $MaxDur = 0;
	public $CurDur = 0;
	public $reqlvl = 0;
	public $reqstr = 0;
	public $reqdex = 0;
	public $speed = 0;
	public $throwing = 0;
	public $stackable = 0;
	public $charName = ''; //ear's name

	public $gfx;  //graphic file name
	public $baseTrans = -1; //transform indexes for colour remap base item
	public $magicTrans = -1; //transform indexes for colour remap magic item
	public $type; //type column from txt
	public $spelldesc = ''; //desc for potions

	public $ditem; //link to item properties from txt

	//mods
	public $dammult = 100; //damage multiply
	public $damminadd = 0; //damage add
	public $dammaxadd = 0; //damage add
	public $defmult = 100; //defense multiply
	public $defadd = 0; //defense multiply
	public $resist = array(0, 0, 0, 0, 0, 0); //phy, mag, fire, light, cold, poison
	public $attributes = array(0, 0, 0, 0); //str, dex, vit, ene

	//arrays
	public $SocketItems = array(); //socketed items, collected in above funciont, because item has only data for itselt, and gems/runes/jewels are standalone
	public $properties = array(); //item variable properties
	public $propids = array(); //properties ids list
	//public

	public function __construct($data = null) {
		if(!$data) return;
		$this->bitreader = new myByteReader($data);

		$this->ReadItem();
		$this->Clear();
	}

	private function Clear() {
		unset($this->bitreader);
		$this->bitreader = null;
	}

	public function ReadItem() {
		$this->bitreader->SkipBytes(2);
		$this->bitreader->flags = $this->bitreader->ReadUint32();
		$this->version = $this->bitreader->ReadUint8();

		/* start bit=1
		i  5 identified
		s 12 socket
		l 14 pick since last save
		E 17 ear
		S 18 starter
		C 22 compact, simple item
		e 23 ethereal
		P 25 personalized
		r 27 runeword

					r P  eC    SE   l  s       i
		0000 0000 1000 0000 0000 1000 0001 0000
		*/

		$this->identified   = $this->bitreader->GetBit(5);
		$this->sockets      = $this->bitreader->GetBit(12);
		$this->ear          = $this->bitreader->GetBit(17);
		$this->compact      = $this->bitreader->GetBit(22);
		$this->ethereal     = $this->bitreader->GetBit(23);
		$this->personalized = $this->bitreader->GetBit(25);
		$this->runeword     = $this->bitreader->GetBit(27);

		$this->bitreader->SkipBits(2);

		$this->location = $this->bitreader->ReadBits(3); //parent
		$this->body = $this->bitreader->ReadBits(4); //equipped
		$this->col = $this->bitreader->ReadBits(4); //col
		$this->row = $this->bitreader->ReadBits(4); //row
		$this->container = $this->bitreader->ReadBits(3); //container

		switch($this->location) {
			case D2Inventory::STORED: $this->parent = 'Stored'; break;
			case D2Inventory::EQUIPPED: $this->parent = 'Equipped'; break;
			case D2Inventory::BELTI: $this->parent = 'Belt'; break;
			case D2Inventory::CURSOR: $this->parent = 'Cursor'; break;
			case D2Inventory::ITEM: $this->parent = 'Item'; break;
			default:  $this->parent = 'Unknown'; break;
		}
		switch($this->container) {
			case D2Inventory::INVENTORY: $this->storage = 'Inventory'; break;
			case D2Inventory::CUBE: $this->storage = 'Cube'; break;
			case D2Inventory::STASH: $this->storage = 'Stash'; break;
			default:  $this->storage = 'Unknown'; break;
		}
		//equipped
		if($this->location == 1) {
			switch($this->body) {
				case D2Inventory::HELMET: $this->bodypart = 'Helmet'; break;
				case D2Inventory::AMULET: $this->bodypart = 'Amulet'; break;
				case D2Inventory::ARMOR: $this->bodypart = 'Armor'; break;
				case D2Inventory::WEAPONR: $this->bodypart = 'Weapon R'; break;
				case D2Inventory::WEAPONL: $this->bodypart = 'Weapon L'; break;
				case D2Inventory::RINGR: $this->bodypart = 'Ring R'; break;
				case D2Inventory::RINGL: $this->bodypart = 'Ring L'; break;
				case D2Inventory::BELT: $this->bodypart = 'Belt'; break;
				case D2Inventory::BOOTS: $this->bodypart = 'Boots'; break;
				case D2Inventory::GLOVES: $this->bodypart = 'Gloves'; break;
				case D2Inventory::WEAPONR2: $this->bodypart = 'Weapon Alt R'; break;
				case D2Inventory::WEAPONL2: $this->bodypart = 'Weapon Alt L'; break;
				default:  $this->bodypart = 'Unknown'; break;
			}
		}

		if ($this->ear) {
			$this->item_code = 'ear';
			$this->basename = 'Ear';
			//read ear
			$eclass = $this->bitreader->ReadBits(3);
			$elevel = $this->bitreader->ReadBits(7);

			//read character name
			for ($i = 0; $i < 18; $i++) {
				$c = $this->bitreader->ReadBits(7);
				if ($c == 0) {
					$this->bitreader->SkipBits(7);
					break;
				}
				$this->charName .= chr($c);
			}

			$this->item_name = $this->charName.'\'s Ear';
			$this->properties[] = array(
				'qflag' => 0,
				'propid' => 185,
				'icost' => $this->GetICOST(185),
				'props' => array($eclass, $elevel),
			);
		}
		else {
			//read item code, always 32 bits
			for ($i = 0; $i < 4; $i++) {
				$c = $this->bitreader->ReadBits(8);
				if ($c != 32) {
					$this->item_code .= chr($c);
				}
			}

			//get base name
			$this->basename = $this->GetString($this->item_code);
			if($this->basename) {
				$this->item_name = $this->basename; //save base name to item name for cases, when there is no special item name
			}
		}


		//get basic item properties
		global $D2DATA;
		foreach($D2DATA->ITEMALL as $ditem) {
			if($ditem['code'] == $this->item_code) {
				$this->ditem = $ditem;
				//item tier
				if($ditem['normcode'] == $this->item_code) {
					$this->item_rank = 'Normal';
				}
				elseif($ditem['ubercode'] == $this->item_code) {
					$this->item_rank = 'Exceptional';
				}
				//else {
				elseif($ditem['ultracode'] == $this->item_code) {
					$this->item_rank = 'Elite';
				}

				$this->item_type = $ditem['itype'];
				$this->type = $ditem['type'];
				$this->gfx = $ditem['invfile'];
				$this->baseTrans = $ditem['InvTrans'];
				$this->invH = $ditem['invheight'];
				$this->invW = $ditem['invwidth'];
				$this->reqlvl = $ditem['levelreq'];
				$this->reqstr = $ditem['reqstr'] != '' ? $ditem['reqstr'] : 0;
				$this->reqdex = $ditem['reqdex'] != '' ? $ditem['reqdex'] : 0;

				if($this->item_type == 'weapon') {
					if($ditem['mindam'] != '') { //one hand damage
						$this->mindam = $ditem['mindam'];
						$this->maxdam = $ditem['maxdam'];
					}
					if($ditem['2handmindam'] != '') { //two hand damage
						$this->mindam2 = $ditem['2handmindam'];
						$this->maxdam2 = $ditem['2handmaxdam'];
					}
					if($ditem['minmisdam'] != '') { //missile damage
						$this->mindammi = $ditem['minmisdam'];
						$this->maxdammi = $ditem['maxmisdam'];
					}
					if($ditem['speed'] != '') {
						$this->speed = $ditem['speed'];
					}
				}
				elseif($this->type == 'belt') {
					switch($ditem['belt']) {
						case 0:
						case 5: $this->beltrows = 3; break;
						case 1:
						case 4: $this->beltrows = 2; break;
						case 3:
						case 6: $this->beltrows = 4; break;
					}
				}
				//extra description for potions mostly
				if($this->item_type == 'misc' && $ditem['spelldescstr'] != '') {
					$this->spelldesc = $this->GetString($ditem['spelldescstr']).' '.$ditem['spelldesccalc'];
				}

				//special treatmeant, because otherwise GetString translate this to too long string
				if($this->item_code == 'ass') { //book of skill
					$this->basename = $ditem['name'];
					$this->item_name = $ditem['name'];
				}
			}
		}

		if ($this->ear) {
			return;
		}

		// extended item
		if(!$this->compact) {
			$this->SocketsFilled = $this->bitreader->ReadBits(3);
			$this->fingerprint = '0x'.dechex($this->bitreader->ReadBits(32));
			$this->itemlvl = $this->bitreader->ReadBits(7);
			$this->quality = $this->bitreader->ReadBits(4);

			//gfx image version for jewelry and charms
			$gfx_num = -1;
			$gfx_option = $this->bitreader->ReadBits(1);
			if ($gfx_option == 1) {
				$gfx_num = $this->bitreader->ReadBits(3);
				if ($this->item_code == 'cm1') { //small
					$this->isCharm = true;
					$this->gfx = 'invch' . (($gfx_num) * 3 + 1);
				}
				elseif ($this->item_code == 'cm2') { //large
					$this->isCharm = true;
					$this->gfx = 'invch' . (($gfx_num) * 3 + 2);
				}
				elseif ($this->item_code == 'cm3') { //grand
					$this->isCharm = true;
					$this->gfx = 'invch' . (($gfx_num) * 3 + 3);
				}
				elseif ($this->item_code == 'jew') { //jewel
					$this->isJewel = true;
					$this->gfx = 'invjw' . ($gfx_num + 1);
					$this->socketable = true;
				}
				elseif($this->item_code != 'vip') { //rings, amulets, but not the viper amulet
					$this->gfx .= ($gfx_num + 1); //string was saved from basic options
				}
			}

			// check class info flag of automagic properties
			if ($this->bitreader->ReadBits(1) == 1) {
				$automod_id = $this->bitreader->ReadBits(11);
				$am = $this->GetAutoMagic($automod_id - 1);
				if($am) {
					$this->reqlvl = max($this->reqlvl, $am['levelreq']);
				}
			}

			$this->magic_rank = $this->quality;
			//get data based on item quality
			switch ($this->quality) {
				case D2MagicRank::LOWQUALITY: //1
					$low_quality = $this->bitreader->ReadBits(3);
					switch ($low_quality) {
						case 0: $this->item_name = 'Crude '.$this->item_name;       break;
						case 1: $this->item_name = 'Cracked '.$this->item_name;     break;
						case 2: $this->item_name = 'Damaged '.$this->item_name;     break;
						case 3: $this->item_name = 'Low Quality '.$this->item_name; break;
					}
					$this->basename = $this->item_name;
					break;

				case D2MagicRank::NORMQUALITY: //2
					// charms
					if ($this->isCharm) {
						$this->bitreader->SkipBits(1);
						$this->bitreader->SkipBits(11);
					}
					// books / scrolls
					if($this->item_code == 'tbk' || $this->item_code == 'ibk') {
						$this->bitreader->SkipBits(5);
					}
					if($this->item_code == 'tsc' || $this->item_code == 'isc') {
						$this->bitreader->SkipBits(5);
					}
					// body
					if($this->item_code == 'body') {
						$this->bitreader->SkipBits(10);
					}
					break;

				case D2MagicRank::HIGHQUALITY: //3
					$this->item_name = 'Superior ' . $this->item_name;
					$this->basename = $this->item_name;
					$this->bitreader->SkipBits(3); // 3bits, unknown
					break;

				case D2MagicRank::MAGIC: //4
					$magic_prefix = $this->bitreader->ReadBits(11);
					$magic_suffix = $this->bitreader->ReadBits(11);

					$prefix = $this->GetMprefix($magic_prefix);
					if($prefix['Name'] != '') {
						$this->item_name = $this->GetString($prefix['Name']).' '.$this->item_name;
						$this->reqlvl = max($this->reqlvl, $prefix['levelreq']);
					}

					if($magic_suffix > 0) {
						$suffix = $this->GetMsuffix($magic_suffix);
						if($suffix['Name'] != '') {
							$this->item_name .= ' '.$this->GetString($suffix['Name']);
							$this->reqlvl = max($this->reqlvl, $suffix['levelreq']);
						}
					}
					break;

				case D2MagicRank::SET: //5
					$this->set_id = $this->bitreader->ReadBits(12);
					$itemtxt = $this->GetSETITEM($this->set_id);
					$this->item_name = $this->GetString($itemtxt['index']);

					$this->set_name = $itemtxt['set'];
					$this->set_item = $itemtxt['index'];

					if($this->ditem['setinvfile'] != '') {
						$this->gfx = $this->ditem['setinvfile'];
					}

					if($itemtxt['invtransform'] != '') {
						$this->magicTrans = $itemtxt['invtransform'];
					}

					$this->reqlvl = max($this->reqlvl, $itemtxt['lvl req']);
					break;

				case D2MagicRank::UNIQUE: //7
					$unique_id = $this->bitreader->ReadBits(12);
					$itemtxt = $this->GetUNIQUE($unique_id);

					if($itemtxt) {
						$this->item_name = $this->GetString($itemtxt['index']);
						if($itemtxt['invfile'] != '') {
							$this->gfx = $itemtxt['invfile'];
						}
					}
					elseif($this->ditem['uniqueinvfile'] != '') {
						$this->gfx = $this->ditem['uniqueinvfile'];
					}
					if($itemtxt['invtransform'] != '') {
						$this->magicTrans = $itemtxt['invtransform'];
					}

					$this->reqlvl = max($this->reqlvl, $itemtxt['lvl req']);
					break;

				case D2MagicRank::RARE:  //6
				case D2MagicRank::CRAFT: //8
					$rare_prefix = $this->bitreader->ReadBits(8);
					$rare_suffix = $this->bitreader->ReadBits(8);
					//name
					$prefix = $this->GetRprefix($rare_prefix - 156); //156 is id offset, because there is 156 suffixes, pretty arbitrary
					$suffix = $this->GetRsuffix($rare_suffix - 1);   //1 is id offset

					$this->item_name = $this->GetString($prefix['name']).' '.$this->GetString($suffix['name']);

					$pre_count = 0;
					$suf_count = 0;
					//level from properties
					for ($i = 0; $i < 3; $i++) {
						if ($this->bitreader->ReadBits(1) == 1) {
							$prefix = $this->GetMprefix($this->bitreader->ReadBits(11));
							$this->reqlvl = max($this->reqlvl, $prefix['levelreq']);
							$pre_count++;
						}
						if ($this->bitreader->ReadBits(1) == 1) {
							$suffix = $this->GetMsuffix($this->bitreader->ReadBits(11));
							$this->reqlvl = max($this->reqlvl, $suffix['levelreq']);
							$suf_count++;
						}
					}

					if($this->magic_rank == D2MagicRank::CRAFT) {
						//crafted items have special level req calculation
						$this->reqlvl = $this->reqlvl + 10 + (3 * ($suf_count + $pre_count));
					}
					break;
			}
			//switch quality end

			// rune word
			if($this->runeword) {
				$this->magic_rank = D2MagicRank::RUNEWORD;
				$this->bitreader->SkipBits(12); //runeword unknown, seems unique number for each runeword, but works with zero
				$this->bitreader->SkipBits(4); //runeword unknown, seems all runewords have 5 here
			}

			// personalized name
			if($this->personalized) {
				for ($i = 0; $i < 15; $i++) {
					$c = $this->bitreader->ReadBits(7);
					if ($c == 0) {
						break;
					}
					$this->personname .= chr($c);
				}
				if ($i == 15) {
					$this->bitreader->SkipBits(7);
				}
				$this->item_name = $this->personname.'\'s '.$this->item_name;
			}

		}
		//extend1 end


		// gold
		if ($this->item_code == 'gold') {
			if ($this->bitreader->ReadBits(1) == 0) {
				$this->gold = $this->bitreader->ReadBits(12);
			}
			else {
				$this->gold = $this->bitreader->ReadBits(32);
			}
		}

		$hasGUID = $this->bitreader->ReadBits(1); //guid indicator or flag for misc quest items, it's part of quest difficulty
		if ($hasGUID == 1) { // GUID
			$sub3 = substr($this->type, 0, 3);
			$sub4 = substr($this->type, 0, 4);
			//guid only for some item types, quite all instead few misc
			if($sub4 == 'rune' || $sub3 == 'gem' || $sub3 == 'amu' || $sub3 == 'rin' || $this->isCharm || $this->item_type != 'misc') {
				$this->GUID = '0x'.dechex($this->bitreader->ReadBits(32))
					.' 0x'.dechex($this->bitreader->ReadBits(32))
					.' 0x'.dechex($this->bitreader->ReadBits(32));
			}
			else {
				$this->bitreader->SkipBits(3);
			}
		}
		//for misc and quest item, it's difficulty where item was found, only for some specific items
		if($this->item_type == 'misc' && $this->type == 'ques' && $this->compact) {
			//read 3 bits and shift by 1 to add previously read hasGUID bit
			$this->questdif = ($this->bitreader->ReadBits(3) << 1) + $hasGUID;
		}

		//socketable gems and runes properties
		if($this->item_type == 'misc' && ($this->type == 'rune' || substr($this->type, 0, 3) == 'gem')) {
			$this->socketable = true;
			$this->GemRuneProperties(); //get gem prop
			$this->MatchProperties();   //match props for alter display
		}


		//item extend2
		if(!$this->compact) {
			if ($this->item_type == 'armor') {
				$this->defense = ($this->bitreader->ReadBits(11) - 10); // -10 is probably offset from save add bits from itemcosts
				$this->MaxDur = $this->bitreader->ReadBits(8);

				if ($this->MaxDur != 0) {
					$this->CurDur = $this->bitreader->ReadBits(9);
				}
			}
			elseif ($this->item_type == 'weapon') {
				if ($this->item_type == 'tkni' || $this->item_type == 'taxe' || $this->item_type == 'jave' || $this->item_type == 'ajav') {
					$this->throwing = true;
				}
				$this->MaxDur = $this->bitreader->ReadBits(8);

				if ($this->MaxDur != 0) {
					$this->CurDur = $this->bitreader->ReadBits(9);
				}

				if ($this->ditem['stackable'] == '1') {
					$this->stackable = true;
					$this->CurDur = $this->bitreader->ReadBits(9);
				}
			}
			elseif ($this->item_type == 'misc') {
				if ($this->ditem['stackable'] == '1') {
					$this->stackable = true;
					$this->CurDur = $this->bitreader->ReadBits(9);
				}
			}

			//socket number
			if ($this->sockets) {
				$this->SocketsNum = $this->bitreader->ReadBits(4);
			}

			//set items, check if there are partial set item props
			$setpart = array();
			if ($this->quality == D2MagicRank::SET) {
				for ($i = 0; $i < 5; $i++) {
					$setpart[$i] = $this->bitreader->ReadBits(1);
				}
			}

			//item properties, read jewel with qflag 1, otherwise normal item
			if($this->isJewel){
				$this->ReadProperties(1);
			}
			else{
				$this->ReadProperties(0);
			}

			//set items partial props
			if ($this->quality == D2MagicRank::SET) {
				for ($i = 0; $i < 5; $i++) {
					if ($setpart[$i] == 1) {
						$this->ReadProperties($i + 2);
					}
				}
			}

			//runewords have extra set or props
			if ($this->runeword) {
				$this->ReadProperties(0);
			}

			//read socketed items, when data contains more items
			if($this->SocketsNum > 0) {
				for($i = 0; $i < $this->SocketsNum; $i++) {
					$sockposB = $this->bitreader->FindPos('JM');
					if($sockposB === false) break;
					$sockposE = $this->bitreader->FindPos('JM', $sockposB + 2);
					if($sockposE === false) {
						$sockposE = $this->bitreader->GetLength();
					}

					$socklen = $sockposE - $sockposB;

					$sockitemdata = $this->bitreader->ReadString($socklen);
					$this->SocketItems[] = new D2Item($sockitemdata);
				}
				$this->RuneWordApply();
			}

			//match and group props
			$this->MatchProperties();
		}
		//extend2 end
	}

	/*
		read all props
		$qFlag ... 0 to 6
	*/
	private function ReadProperties($qFlag) {

		$propid = $this->bitreader->ReadBits(9);

		//break on 511(end property) or -1(read past filelength)
		while ($propid != 511 && $propid != -1) {
			$this->ReadProp($propid, $qFlag);
			/*
			17,18 - enhanced damage
			48,49 - fire damage
			50,51 - lightning damage
			52,53 - magic damage
			54,55,56 - cold damage
			57,58,59 - posion damage
			*/

			//some properties have more parametres
			if ($propid == 17) {
				$this->ReadProp(18, $qFlag);
			}
			else if ($propid == 48) {
				$this->ReadProp(49, $qFlag);
			}
			else if ($propid == 50) {
				$this->ReadProp(51, $qFlag);
			}
			else if ($propid == 52) {
				$this->ReadProp(53, $qFlag);
			}
			else if ($propid == 54) {
				$this->ReadProp(55, $qFlag);
				$this->ReadProp(56, $qFlag);
			}
			else if ($propid == 57) {
				$this->ReadProp(58, $qFlag);
				$this->ReadProp(59, $qFlag);
			}

			$propid = $this->bitreader->ReadBits(9);
		}
	}

	//read single prop
	public function ReadProp($propid, $qFlag) {
		$icost = $this->GetICOST($propid);
		$readLength = $icost['Save Bits'] != '' ? $icost['Save Bits'] : 0;
		$saveAdd = $icost['Save Add'] != '' ? $icost['Save Add'] : 0;
		$paramBits = $icost['Save Param Bits'] != '' ? $icost['Save Param Bits'] : 0;

		$props = array(
			'qflag' => $qFlag,
			'propid' => $propid,
			'icost' => $icost,
			'props' => array(),
		);

		//skill on event 195, 196, 197, 198, 199, 201
		if (($propid >= 195 && $propid <= 199) || $propid == 201) {
			$props['props'] = array(
				($this->bitreader->ReadBits(6) - $saveAdd),          //skill level
				($this->bitreader->ReadBits(10) - $saveAdd),         //skill
				($this->bitreader->ReadBits($readLength) - $saveAdd) //chance
			);
		}
		//item charged skill
		elseif ($propid == 204) {
			$props['props'] = array(
				($this->bitreader->ReadBits(6) - $saveAdd),  //skill level
				($this->bitreader->ReadBits(10) - $saveAdd), //skill id
				($this->bitreader->ReadBits(8) - $saveAdd),  //curent charges
				($this->bitreader->ReadBits(8) - $saveAdd)   //maximum charges
			);
		}
		elseif($paramBits > 0){
			$props['props'] = array(
				($this->bitreader->ReadBits($paramBits) - $saveAdd),  //parametr, when apliable. Like id of class, skill, aura
				($this->bitreader->ReadBits($readLength) - $saveAdd)  //property value
			);
		}
		else {
			$props['props'] = array(
				($this->bitreader->ReadBits($readLength) - $saveAdd)  //property value
			);
		}

		$this->propids[] = $propid;
		$this->properties[] = $props;
	}

	//groups some properties together
	public function MatchProperties() {
		/*
		17,18 - enhanced damage
		48,49 - fire damage
		50,51 - lightning damage
		52,53 - magic damage
		54,55,56 - cold damage
		57,58,59 - posion damage
		39,41,43,45 - resists, all and same
		0,1,2,3 - attributes, all and same
		*/

		foreach($this->properties as $prop) {
			switch($prop['propid']) {
				case 0: $this->attributes[0] = $prop['props'][0]; break; //str
				case 1: $this->attributes[3] = $prop['props'][0]; break; //ene
				case 2: $this->attributes[1] = $prop['props'][0]; break; //dex
				case 3: $this->attributes[2] = $prop['props'][0]; break; //vit
				case 17: $this->dammult += $prop['props'][0]; break;
				case 21: $this->damminadd += $prop['props'][0]; break;
				case 22: $this->dammaxadd += $prop['props'][0]; break;
				case 16: $this->defmult += $prop['props'][0]; break;
				case 31: $this->defadd += $prop['props'][0]; break;
				case 36: $this->resist[0] = $prop['props'][0]; break; //phy
				case 37: $this->resist[1] = $prop['props'][0]; break; //mag
				case 39: $this->resist[2] = $prop['props'][0]; break; //fire
				case 41: $this->resist[3] = $prop['props'][0]; break; //light
				case 43: $this->resist[4] = $prop['props'][0]; break; //cold
				case 45: $this->resist[5] = $prop['props'][0]; break; //poison
				//req percent
				case 91:
					$this->reqstr = ceil($this->reqstr * (100 + $prop['props'][0]) / 100);
					$this->reqdex = ceil($this->reqdex * (100 + $prop['props'][0]) / 100);
					break;
				//item_levelreq
				case 92:
					$this->reqlvl += $prop['props'][0];
					break;
				//required level modificating properties
				case 97:  //item_nonclassskill
				case 107: //item_singleskill
					$skill = $this->GetSkills($prop['props'][0]);
					$this->reqlvl = max($this->reqlvl, $skill['reqlevel']);
					break;
				//indestructible
				case 152:
					$this->MaxDur = 0;
					break;
			}
		}

		$start = 0;
		//group attributes
		if(in_array(0, $this->propids)) {
			$key1 = $this->GetPropertyKey(0, $start);
			$key2 = $this->GetPropertyKey(1, $key1 + 1);
			$key3 = $this->GetPropertyKey(2, $key2 + 1);
			$key4 = $this->GetPropertyKey(3, $key3 + 1);
			if($key1 >= 0 && $key2 >= 0 && $key3 >= 0 && $key4 >= 0) {
				$v0 = $this->properties[$key1]['props'][0];
				$v1 = $this->properties[$key2]['props'][0];
				$v2 = $this->properties[$key3]['props'][0];
				$v3 = $this->properties[$key4]['props'][0];
				if($v0 == $v1 && $v1 == $v2 && $v2 == $v3) {
					$this->properties[$key1]['icost']['descfunc'] = 56; //special treatment
					$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
					$this->properties[$key3]['icost']['descfunc'] = 100; //cancel
					$this->properties[$key4]['icost']['descfunc'] = 100; //cancel
				}
			}
		}

		//group resists
		if(in_array(39, $this->propids)) {
			$key1 = $this->GetPropertyKey(39, $start);
			$key2 = $this->GetPropertyKey(41, $key1 + 1);
			$key3 = $this->GetPropertyKey(43, $key2 + 1);
			$key4 = $this->GetPropertyKey(45, $key3 + 1);
			if($key1 >= 0 && $key2 >= 0 && $key3 >= 0 && $key4 >= 0) {
				$v0 = $this->properties[$key1]['props'][0];
				$v1 = $this->properties[$key2]['props'][0];
				$v2 = $this->properties[$key3]['props'][0];
				$v3 = $this->properties[$key4]['props'][0];
				if($v0 == $v1 && $v1 == $v2 && $v2 == $v3) {
					$this->properties[$key1]['icost']['descfunc'] = 57; //special treatment
					$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
					$this->properties[$key3]['icost']['descfunc'] = 100; //cancel
					$this->properties[$key4]['icost']['descfunc'] = 100; //cancel
				}
			}
		}

		//damage multiply
		if(in_array(17, $this->propids)) {
			while(true) {
				$key1 = $this->GetPropertyKey(17, $start);
				if($key1 < 0) break;
				$key2 = $this->GetPropertyKey(18, $start);
				if($key2 < 0) break;
				$start = $key2 + 1;
				$this->properties[$key1]['icost']['descfunc'] = 50; //special treatment
				$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
			}
		}

		//fire damage
		if(in_array(48, $this->propids)) {
			$key1 = $this->GetPropertyKey(48, $start);
			if($key1 >= 0) {
				$key2 = $this->GetPropertyKey(49, $start);
				if($key2 >= 0) {
					$this->properties[$key1]['props'][1] = $this->properties[$key2]['props'][0];
					$this->properties[$key1]['icost']['descfunc'] = 51; //special treatment
					$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
				}
			}
		}
		//light damage
		if(in_array(50, $this->propids)) {
			$key1 = $this->GetPropertyKey(50, $start);
			if($key1 >= 0) {
				$key2 = $this->GetPropertyKey(51, $start);
				if($key2 >= 0) {
					$this->properties[$key1]['props'][1] = $this->properties[$key2]['props'][0];
					$this->properties[$key1]['icost']['descfunc'] = 52; //special treatment
					$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
				}
			}
		}
		//magic damage
		if(in_array(52, $this->propids)) {
			$key1 = $this->GetPropertyKey(52, $start);
			if($key1 >= 0) {
				$key2 = $this->GetPropertyKey(53, $start);
				if($key2 >= 0) {
					$this->properties[$key1]['props'][1] = $this->properties[$key2]['props'][0];
					$this->properties[$key1]['icost']['descfunc'] = 53; //special treatment
					$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
				}
			}
		}
		//cold damage
		if(in_array(54, $this->propids)) {
			$key1 = $this->GetPropertyKey(54, $start);
			if($key1 >= 0) {
				$key2 = $this->GetPropertyKey(55, $key1 + 1);
				if($key2 >= 0) {
					$key3 = $this->GetPropertyKey(56, $key2 + 1);
					if($key3 >= 0) {
						$this->properties[$key1]['props'][1] = $this->properties[$key2]['props'][0];
						$this->properties[$key1]['props'][2] = $this->properties[$key3]['props'][0];
						$this->properties[$key1]['icost']['descfunc'] = 54; //special treatment
						$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
						$this->properties[$key3]['icost']['descfunc'] = 100; //cancel
					}
				}
			}
		}
		//poison damage
		if(in_array(57, $this->propids)) {
			$key1 = $this->GetPropertyKey(57, $start);
			if($key1 >= 0) {
				$key2 = $this->GetPropertyKey(58, $key1 + 1);
				if($key2 >= 0) {
					$key3 = $this->GetPropertyKey(59, $key2 + 1);
					if($key3 >= 0) {
						$this->properties[$key1]['props'][1] = $this->properties[$key2]['props'][0];
						$this->properties[$key1]['props'][2] = $this->properties[$key3]['props'][0];
						$this->properties[$key1]['icost']['descfunc'] = 55; //special treatment
						$this->properties[$key2]['icost']['descfunc'] = 100; //cancel
						$this->properties[$key3]['icost']['descfunc'] = 100; //cancel
					}
				}
			}
		}
	}

	//get and add gem/rune properties
	private function GemRuneProperties() {
		$gem = $this->GetGEM($this->item_code);
		if(!$gem) return;

		$props = array();
		$gprops = array();

		//get props
		for($i = 1; $i <= 3; $i++) {
			if($gem['weaponMod'.$i.'Code'] != '') {
				$gprops[] = array('t' => 'w', 'c' => $gem['weaponMod'.$i.'Code'], 'p' => $gem['weaponMod'.$i.'Param'], 'n' => $gem['weaponMod'.$i.'Min'], 'x' => $gem['weaponMod'.$i.'Max']);
			}
			if($gem['helmMod'.$i.'Code'] != '') {
				$gprops[] = array('t' => 'a', 'c' => $gem['helmMod'.$i.'Code'], 'p' => $gem['helmMod'.$i.'Param'], 'n' => $gem['helmMod'.$i.'Min'], 'x' => $gem['helmMod'.$i.'Max']); }
			if($gem['shieldMod'.$i.'Code'] != '') {
				$gprops[] = array('t' => 's', 'c' => $gem['shieldMod'.$i.'Code'], 'p' => $gem['shieldMod'.$i.'Param'], 'n' => $gem['shieldMod'.$i.'Min'], 'x' => $gem['shieldMod'.$i.'Max']);
			}
		}

		//convert props to itemcosts, which is needed to display them properly
		foreach($gprops as $gprop) {
			$icode = array();
			$pcode = $gprop['c'];
			if($pcode == 'indestruct') {
				$icode[] = 152; //'item_indesctructible';
			}
			elseif($pcode == 'dmg-min') {
				$icode[] = 21; //'mindamage';
			}
			elseif($pcode == 'dmg-max') {
				$icode[] = 22; //'maxdamage';
			}
			elseif($pcode == 'dmg%') {
				$icode[] = 17; //'item_maxdamage_percent';
				$icode[] = 18; //'item_mindamage_percent';
			}
			else {
				$proplist = $this->GetPROP($pcode);
				for($i = 1; $i <= 7; $i++) {
					$stat = 'stat'.$i;
					if($proplist[$stat] == '') continue;
					$icost = $this->GetICOSTbyName($proplist[$stat]);
					$icode[] = $icost['ID'];
				}
			}

			if(empty($icode)) continue;

			foreach($icode as $k => $ic) {
				if($ic < 0) continue;
				$icost = $this->GetICOST($ic);
				if(!$icost) continue;

				//arbitrary decision, whether to use min or max value. May need some rework, not entirely tested
				$val = (count($icode) > 1 && $gprop['n'] != '' && $k < 1) ? $gprop['n'] : $gprop['x'];

				$props = array(
					'qflag' => 0,
					'propid' => $icost['ID'],
					'icost' => $icost,
					'props' => array($val),
					'gtype' => $gprop['t']
				);
				$this->propids[] = $icost['ID'];
				$this->properties[] = $props;
			}
		}
	}

	private function ApplySocketProperties() {
		$gtype = '';
		//get item type to choose gem/rune properties to add, does not apply for jewels
		if($this->item_type == 'weapon') {
			$gtype = 'w';
		}
		elseif($this->item_type == 'armor') {
			if($this->type == 'shie') {
				$gtype = 's';
			}
			else {
				$gtype = 'a';
			}
		}
		else { //misc items have no sockets
			return;
		}


		foreach($this->SocketItems as $k => $item) {
			foreach($item->properties as $prop) {
				if($item->isJewel || $prop['gtype'] == $gtype) {
					$this->ApplySocketProperty($prop);
				}
			}
		}
	}

	private function ApplySocketProperty($prop) {
		$propid = $prop['propid'];

		//properties, that will not add, but are as new
		$skip = array(195, 196, 197, 198, 199, 201, 204);

		//get key of propid from propeties
		$key = $this->GetPropertyKey($propid, 0);
		//new property
		if($key == -1 || in_array($propid, $skip)
			|| $this->properties[$key]['icost']['Save Param Bits'] != '' //has param, so it cant be just added
			|| $this->properties[$key]['icost']['descfunc'] > 50 //cancel, because it was grouped before
		) {
			$this->properties[] = $prop;
		}
		//existing property, just add value
		else {
			$this->properties[$key]['props'][0] += $prop['props'][0];
		}
	}

	//get prop key from array
	public function GetPropertyKey($id, $start = 0) {
		for($i = $start, $len = count($this->properties); $i < $len; $i++) {
			if($this->properties[$i]['propid'] == $id) return $i;
		}
		return -1;
	}

	//check for runes and apply runeword if they form one
	public function RuneWordApply() {
		if($this->runeword != 1) return;
		$runes = array_fill(0, 6, '');
		foreach($this->SocketItems as $k => $item) {
			$this->reqlvl = max($this->reqlvl, $item->reqlvl);
			if(substr($item->item_code, 0, 1) != 'r') continue; //not a rune
			$runes[$k] = $item->item_code;
		}

		global $D2DATA;
		foreach($D2DATA->RUNEWORDS as $runewords) {
			$match = true;
			$this->runes_title = '';
			for($i = 0; $i < 6; $i++) {
				if($runes[$i] != $runewords['Rune'.($i+1)]) {
					$match = false;
					$this->runes_title = '';
					break;
				}
				$this->runes_title .= $this->GetString($runes[$i].'L').' ';
			}

			if($match) {
				$this->runeword_name = $this->GetString($runewords['Name']);
				$this->item_name = $this->runeword_name;
				break;
			}
		}
		$this->ApplySocketProperties();
	}

	//debug function to display data stream as hex and binary
	public function ShowAsBitstream($data) {
		$length = strlen($data);
		$outraw = '';
		$outhex = '';
		$outbin = '';
		for($i = 0; $i < $length; $i++) {
			//$outraw = $data[$i];
			$outhex .= padleft(dechex(ord($data[$i])), 2).' ';
			$outbin .= padleft(decbin(ord($data[$i])), 8).' ';
		}
		echo $outhex.'<br />';
		echo $outbin.'<br />';
	}

	//get string by id
	public function GetString($index) {
		global $D2DATA;
		if(array_key_exists($index, $D2DATA->STRINGS)) {
			return $D2DATA->STRINGS[$index];
		}
		else {
			return null;
		}
	}

	//get string by id and offset
	public function GetStringByOffset($index, $offset) {
		global $D2DATA;
		$start = 0;
		foreach($D2DATA->STRINGS as $strid => $string) {
			if($start == 0 && $index != $strid) continue;
			$start++;
			if($offset == $start) {
				return $string;
			}
		}
		return null;
	}

	//function to get props from txt files
	public function GetICOST($id) {
		global $D2DATA;
		if(array_key_exists($id, $D2DATA->ICOST)) {
			return $D2DATA->ICOST[$id]; //['index'];
		}
		else {
			return null;
		}
	}

	public function GetICOSTbyName($name) {
		global $D2DATA;
		foreach($D2DATA->ICOST as $irow) {
			if($irow['Stat'] != $name) continue;
			return $irow;
		}
		return null;
	}

	public function GetPROP($code) {
		global $D2DATA;
		foreach($D2DATA->PROPERTIES as $row) {
			if($row['code'] != $code) continue;
			return $row;
		}
		return array();
	}

	public function GetICOSTIDbyPROP($code) {
		global $D2DATA;
		foreach($D2DATA->PROPERTIES as $row) {
			if($row['code'] != $code) continue;
			$istat = $row['stat1'];
			foreach($D2DATA->ICOST as $irow) {
				if($irow['Stat'] != $istat) continue;
				return $irow['ID'];
			}
		}
		return -1;
	}

	public function GetSKILLS($id) {
		global $D2DATA;
		if(array_key_exists($id, $D2DATA->SKILLS)) {
			return $D2DATA->SKILLS[$id];
		}
		return null;
	}

	public function GetSKILLDESC($desc) {
		global $D2DATA;
		foreach($D2DATA->SKILLDESC as $row) {
			if($row['skilldesc'] == $desc) {
				return $row;
			}
		}
		return null;
	}

	public function GetUNIQUE($id) {
		global $D2DATA;
		if(array_key_exists($id, $D2DATA->UNIQUE)) {
			return $D2DATA->UNIQUE[$id]; //['index'];
		}
		else {
			return null;
		}
	}

	public function GetSETITEM($id) {
		global $D2DATA;
		return $D2DATA->SET[$id]; //['index'];
	}

	public function GetSET($id) {
		//global $D2DATA;
	}

	public function GetGEM($code) {
		global $D2DATA;
		foreach($D2DATA->GEMS as $row) {
			if($row['code'] == $code) {
				return $row;
			}
		}
		return null;

	}

	public function GetAutoMagic($id) {
		global $D2DATA;
		if(!array_key_exists($id, $D2DATA->AUTOMAGIC)) {
			return null;
		}
		return $D2DATA->AUTOMAGIC[$id];
	}

	public function GetMprefix($id) {
		global $D2DATA;
		return $D2DATA->MAGICPREFIX[$id];
	}

	public function GetMsuffix($id) {
		global $D2DATA;
		return $D2DATA->MAGICSUFFIX[$id];
	}

	public function GetRprefix($id) {
		global $D2DATA;
		return $D2DATA->RAREPREFIX[$id];
	}

	public function GetRsuffix($id) {
		global $D2DATA;
		return $D2DATA->RARESUFFIX[$id];
	}

}

//item quality indexes
class D2MagicRank {
	public const LOWQUALITY  = 1; // low quality item
	public const NORMQUALITY = 2; // high quality item
	public const HIGHQUALITY = 3; // high quality item
	public const MAGIC       = 4; // magic item
	public const SET         = 5; // set item
	public const UNIQUE      = 7; // unique item
	public const RARE        = 6; // rare item
	public const CRAFT       = 8; // craft item
	public const RUNEWORD    = 11; // runeword item
}

//item location indexes
class D2Inventory {
	//location
	public const STORED   = 0;
	public const EQUIPPED = 1;
	public const BELTI    = 2;
	public const CURSOR   = 4;
	public const ITEM     = 6;
	//storage
	public const NONE      = 0;
	public const INVENTORY = 1;
	public const CUBE      = 4;
	public const STASH     = 5;

	//body parts
	public const HELMET   = 1;
	public const AMULET   = 2;
	public const ARMOR    = 3;
	public const WEAPONR  = 4;
	public const WEAPONL  = 5;
	public const RINGR    = 6;
	public const RINGL    = 7;
	public const BELT     = 8;
	public const BOOTS    = 9;
	public const GLOVES   = 10;
	public const WEAPONR2 = 11;
	public const WEAPONL2 = 12;
}

?>
