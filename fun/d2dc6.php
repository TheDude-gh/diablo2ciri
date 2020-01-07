<?php
/*
	pallets and remaps

	InvTrans
	0  not recoloured
	2  grey2.dat
	5  greybrown.dat
	8  invgreybrown.dat

	invtransform
	1  whit  White
	2  lgry  Light Grey
	3  dgry  Dark Grey
	4  blac  Black
	5  lblu  Light Blue
	6  dblu  Dark Blue
	7  cblu  Crystal Blue
	8  lred  Light Red
	9  dred  Dark Red
	10  cred  Crystal Red
	11  lgrn  Light Green
	12  dgrn  Dark Green
	13  cgrn  Crystal Green
	14  lyel  Light Yellow
	15  dyel  Dark yellow
	16  lgld  Light Gold
	17  dgld  Dark Gold
	18  lpur  Light Purple
	19  dpur  Dark Purple
	20  oran  Orange
	21  bwht  Bright White
*/

//class for reading dc6 files and converting them to png, also with transform colours
class D2DC6 {

	private $image;
	private $bitreader;
	private $imagefile;
	private $imagepath;
	private $imageEX = false;
	private $imageOK = false;


	public function __construct($gfxfile, $newfile) {
		$this->imagefile = $gfxfile;

		$d2imagefile     = D2PATHGFX.$this->imagefile.'.dc6';
		$this->imagepath = D2PATHIMG.$newfile.'.png';

		//if file exists, we dont need to create it again
		if(file_exists($this->imagepath)) {
			$this->imageEX = true;
			$this->imageOK = true;
			return;
		}

		if(!file_exists($d2imagefile)) {
			echo 'Missing gfx '.$d2imagefile.'<br />';
			return;
		}

		if(!file_exists(D2PATHIMG)) {
			echo 'Image path does not exist ['.D2PATHIMG.']<br />';
			return;
		}

		$this->imageOK = true;
		$imagedata = file_get_contents($d2imagefile);
		$this->bitreader = new myByteReader($imagedata);
	}

	//baseTrans : index of remaping file
	//magicTrans : index of the remaping table from file
	public function GetSingleImage($baseTrans, $magicTrans) {
		if(!$this->imageOK) return '';
		if($this->imageEX) {
			return $this->imagepath;
		}

		$this->bitreader->SetPos(0);
		$this->bitreader->SkipBytes(16);
		$directions = $this->bitreader->ReadUint32();
		$frames = $this->bitreader->ReadUint32();
		$this->bitreader->SkipBytes(4 * $directions * $frames);

		$flip = $this->bitreader->ReadUint32();
		$width = $this->bitreader->ReadUint32();
		$height = $this->bitreader->ReadUint32();
		$offset_x = $this->bitreader->ReadUint32();
		$offset_y = $this->bitreader->ReadUint32();
		$this->bitreader->SkipBytes(8);
		$length = $this->bitreader->ReadUint32();

		$w = 0; //width/colums
		$h = $height - 1; //height/rows

		$im = imagecreatetruecolor($width, $height);
		$bg = imagecolorallocate($im, 0xff, 0xff, 0xff); //background color
		//$black = imagecolorallocate($im, 0, 0, 0);
		imagecolortransparent($im, $bg); //background color will be transparent
		imagefill($im, 0, 0, $bg);

		$tableIndex = $this->GetTableIndex($magicTrans);
		//check if fileindex is ok and we can remap
		$remap = (($baseTrans == 2 || $baseTrans == 5 || $baseTrans == 8) && $tableIndex >= 0);

		global $D2PALETTE;

		while ($h >= 0) {
			$current = $this->bitreader->ReadUint8();
			//new row
			if ($current == 0x80) {
				$w = 0;
				$h--;
			}
			//reset column counter
			else if ($current > 0x80) {
				$w += ($current - 0x80);
			}
			else {
				while ($current-- > 0) {
					$colorindex = $this->bitreader->ReadUint8();

					if($remap) {
						$colorindex = $D2PALETTE->GetRemapIndex($baseTrans, $tableIndex, $colorindex);
					}

					$colorRGB = $D2PALETTE->GetColor($colorindex);
					$color = imagecolorallocate($im, $colorRGB[0], $colorRGB[1], $colorRGB[2]);

					imagesetpixel($im, $w, $h, $color);
					$w++;
				}
			}
		}

		//save file
		imagepng($im, $this->imagepath);
		imagedestroy($im);
		//return path to the file
		return $this->imagepath;
	}

	//get remap table index based on color string
	private function GetTableIndex($table) {
		switch($table) {
			case 'whit': $tindex = 1; break;
			case 'lgry': $tindex = 2; break;
			case 'dgry': $tindex = 3; break;
			case 'blac': $tindex = 4; break;
			case 'lblu': $tindex = 5; break;
			case 'dblu': $tindex = 6; break;
			case 'cblu': $tindex = 7; break;
			case 'lred': $tindex = 8; break;
			case 'dred': $tindex = 9; break;
			case 'cred': $tindex = 10; break;
			case 'lgrn': $tindex = 11; break;
			case 'dgrn': $tindex = 12; break;
			case 'cgrn': $tindex = 13; break;
			case 'lyel': $tindex = 14; break;
			case 'dyel': $tindex = 15; break;
			case 'lgld': $tindex = 16; break;
			case 'dgld': $tindex = 17; break;
			case 'lpur': $tindex = 18; break;
			case 'dpur': $tindex = 19; break;
			case 'oran': $tindex = 20; break;
			case 'bwht': $tindex = 21; break;
			default: return -1;
		}
		$tindex--; //decrement, because array starts from zero
		return $tindex;
	}

	//dgb function
	public function ShowColor($w, $h, $c) {
		echo '<span style="background: rgb('.$c[0].', '.$c[1].', '.$c[2].'); width: 10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
	}

}

//palette class, get pallete and remap tables
class D2Palette {
	private $colors = array();
	private $remapcolors = array();

	public function __construct() {
		$this->D2Palette();     //palette
		$this->RemapPalette(2); //remap tables for items
		$this->RemapPalette(5);
		$this->RemapPalette(8);
	}

	public function D2Palette(){
		$paletteFile = D2PATHPAL.'palette.dat';
		$palette = file_get_contents($paletteFile);

		$this->bitreader = new myByteReader($palette);

		for ($i = 0; $i < 256; $i++){
			$blue = $this->bitreader->ReadUint8();
			$green = $this->bitreader->ReadUint8();
			$red = $this->bitreader->ReadUint8();
			$this->colors[$i] = array($red, $green, $blue);
		}
		//$this->ShowColors($this->colors);
	}

	//get color by index
	public function GetColor($code){
		return array_key_exists($code, $this->colors) ? $this->colors[$code] : 0;
	}

	public function RemapPalette($num) {
		$path = D2PATHPAL;
		$remapFile = '';
		switch($num) {
			case 2: $remapFile = 'grey2.dat'; break;
			case 5: $remapFile = 'greybrown.dat'; break;
			case 8: $remapFile = 'invgreybrown.dat'; break;
			case 0:
			default: return; //  not recoloured
		}

		$palette = file_get_contents($path.$remapFile);
		$this->bitreader = new myByteReader($palette);

		$rtables = $this->bitreader->GetLength() / 256; //remap tables count

		//remap tables each have 256 bytes
		for ($r = 0; $r < $rtables; $r++){
			for ($i = 0; $i < 256; $i++){
				$newindex = $this->bitreader->ReadUint8();
				$this->remapcolors[$num][$r][$i] = $newindex;
			}
		}
	}

	//num:   remap file index
	//table: remap table index
	//index: color index
	public function GetRemapIndex($num, $table, $index) {
		return $this->remapcolors[$num][$table][$index];
	}

	//dbg function
	/*public function ComparePalettes() {
		echo '<table><tr>';
		for ($i = 0; $i < 256; $i++){
			$col1 = $this->colors[$i];
			$col2 = $this->remapcolors[$i];
			$dif = '';
			if($col1[0] != $col2[0] || $col1[1] != $col2[1] || $col1[2] != $col2[2]) {
				$dif = 'X';
			}

			if($i % 16 == 0) echo '</tr><tr>';
			echo '<td style="width:20px; background: rgb('.$col1[0].', '.$col1[1].', '.$col1[2].');">'.$dif.'&nbsp;</td>'.EOL;
		}
		echo '</table>';
	}*/

	//dbg function
	/*public function ShowColors($colors) {
		echo '<table><tr>';
		foreach($colors as $k => $c) {
			if($k % 16 == 0) echo '</tr><tr>';
			//echo '<tr><td>'.$k.'</td><td style="width:20px; background: rgb('.$c[0].', '.$c[1].', '.$c[2].');">&nbsp;</td></tr>'.EOL;
			echo '<td style="width:20px; background: rgb('.$c[0].', '.$c[1].', '.$c[2].');">&nbsp;</td>'.EOL;
		}
		echo '</table>';
	}*/
}

?>
