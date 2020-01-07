<?php

//class for reading and writing bytestream
class myByteReader {

	private const MAXINT32  = 2147483648;
	private const MAXUINT32 = 4294967296;
	private const MAXINT64 = 1 << 63;

	private $debug;

	private $pos = 0;
	private $bitpos = 0;
	private $length = 0;
	private $data = '';

	public $flags = 0;


	public function __construct($data = null) {
		if($data) {
			$this->AddData($data);
		}
	}

	public function AddData($data) {
		$this->data = $data;
		$this->pos = 0;
		$this->length = strlen($data);
	}

	public function SetBitPos($bitpos) {
		$this->bitpos = $bitpos;
		$this->pos = (int)($bitpos / 8);
	}

	public function GetBitPos() {
		return $this->bitpos;
	}

	public function SetPos($pos) {
		$this->pos = $pos;
		$this->bitpos = $pos * 8;
	}

	public function GetPos() {
		return $this->pos;
	}

	public function GetLength() {
		return $this->length;
	}

	public function FindPos($find, $pos = -1) {
		if($pos == -1) {
			$pos = $this->pos;
		}
		return strpos($this->data, $find, $pos);
	}

	public function ReadUint8(){
		if($this->pos >= $this->length || $this->pos < 0){
			dbglog();
			die('Bad position '.$this->pos);
			return;
		}
		$this->bitpos += 8;
		return ord($this->data[$this->pos++]);
	}

	public function ReadUint16(){
		return $this->ReadUint8() + ($this->ReadUint8() << 8);
	}

	public function ReadUint32(){
		return $this->ReadUint16() + ($this->ReadUint16() << 16);
	}

	public function ReadInt32(){
		$res = $this->ReadUint32();
		if($res > myByteReader::MAXINT32) {
			$res -= myByteReader::MAXUINT32;
		}
		return $res;
	}

	public function ReadInt64(){
		return $this->ReadUint32() + ($this->ReadUint32() << 32);
	}

	public function ReadString($length = -1){
		$res = '';
		if($this->pos >= $this->length || $this->pos < 0) {
			dbglog();
			die('Bad string pos '.$this->pos);
			return;
		}

		//align to next bit, when not aligned
		if($this->bitpos % 8 != 0) {
			$this->pos++;
			$this->bitpos = $this->pos * 8;
		}

		if($length > 0){
			$res = substr($this->data, $this->pos, $length);
			$this->pos += $length;
			return $res;
		}

		while(ord($this->data[$this->pos]) != 0) {
			$res .= $this->data[$this->pos++];
		}
		$this->pos++; // advance pointer after finding the 0

		return $res;
	}

	public function FindOffsetByString($string) {
		$pos = strpos($this->data, $string, $this->pos);
		return $pos;
	}

	public function SkipBytes($bytes = 31){
		$this->pos += $bytes;
		$this->bitpos += 8 * $bytes;
	}

	public function SkipBits($bits){
		$this->bitpos += $bits;
		$this->pos = (int)($this->bitpos / 8);
	}

	//dbg function
	public function ppos(){
		vd(dechex($this->pos). ' '.$this->pos);
	}

	//dbg function
	public function ShowBits($num, $bytes = 4) {
		$str = decbin($num);

		$len = $bytes * 8;
		$bits = str_pad($str, $len, '0', STR_PAD_LEFT);

		$res = '';
		for($i = 0; $i < $len; $i++) {
			$res .= $bits[$i];
			if($i % 4 == 3) $res .= ' ';
		}
		return $res;
	}

	//dbg function
	public function ShowStringBits($bits) {
		$len = strlen($bits);
		$bities = '';
		for($i = 0; $i < $len; $i++) {
			$byte = $bits[$i];
			for($j = 7; $j = 0; $j++) {
				$bities .= ($byte >> $j) & 1;
			}
		}
		echo $bities;
	}

	public function GetData() {
		return $this->data;
	}

	public function GetBit($pos) {
		$pos--;
		return ($this->flags >> $pos) & 0x01;
	}

	//can read up to 32 bits, but presumes 64 bit integers, so we dont check for sign of highest 63th/31st bit
	//32 bits are read from 4-5 byte block, they are not read as consequential bits from anywhere
	public function ReadBits($bitsize) {
		if($this->pos > $this->length) return -1;

		$newdata = 0;
		$bytefirst = (int)($this->bitpos / 8); //first byte we want
		$bitsend = $this->bitpos % 8;
		$bytecount = (int)(($bitsend + $bitsize) / 8) + 1;
		$byteblock = max($bytecount, 4);
		$bitshift = $byteblock * 8;

		//read 4 bytes
		for($j = 0; $j < $byteblock; $j++) {
			$newdata = $newdata << 8;
			if($bytefirst + $j < $this->length && $j < $bytecount) {
				$num = $this->flip(ord($this->data[$bytefirst + $j]));
				$newdata += $num & 0xff;
			}
		}

		$newdata = ($newdata << $bitsend) >> ($bitshift - $bitsize);

		$this->bitpos += $bitsize;
		$this->pos = (int)($this->bitpos / 8);

		return $this->unflip($newdata, $bitsize);
	}

	//write bits to stream
	//writes up to 32 bits in 4-5 byteblock
	public function WriteBits($data, $bitsize) {
		// get current position in bits and bytes

		$bytefirst = (int)($this->bitpos / 8); //first byte to write to
		$bitsend = $this->bitpos % 8; //offset
		$bytecount = (int)(($bitsend + $bitsize) / 8) + 1;
		//when added data aligns byte, we dont need to add the extra byte
		if(($bitsend + $bitsize) % 8 == 0) {
			$bytecount--;
		}
		$byteblock = $bytecount;
		$bitshift = $byteblock * 8;

		$lengthdif = $bytefirst + $byteblock - $this->length;

		if($lengthdif > 0) {
			for($i = 0; $i < $lengthdif; $i++) {
				$this->data .= chr(0);
			}
			$this->length += $lengthdif;
		}

		// get the current bits into a long
		$writeable_data = 0;
		for ($j = 0; $j < $byteblock; $j++) {
			$writeable_data = $writeable_data << 8;
			if ($bytefirst + $j < $this->length) {
				$writeable_data += $this->flip(ord($this->data[$bytefirst + $j]));
			}
			else {
				$writeable_data += 0;
			}
		}

		// generate a mask to clear the bits that are going to be written
		$mask = (1 << $bitsize) - 1;
		$mask = ~($mask << ($bitshift - $bitsize - $bitsend));

		// clear the bits
		$writeable_data = $writeable_data & $mask;

		// move the data bits to be written into the correct bit position
		$data = $this->unflip($data, $bitsize);
		$data = $data << ($bitshift - $bitsize - $bitsend);

		// set the bits to be written
		$writeable_data = $writeable_data | $data;

		// put the bytes back
		$blockwrite = '';
		for ($i = $byteblock - 1; $i >= 0; $i--) {
			$current_byte = $writeable_data & 0xff;
			$current_byte = $this->unflip($current_byte, 8);
			if ($bytefirst + $i < $this->length) {
				$this->data[$bytefirst + $i] = chr($current_byte);
				//$blockwrite = chr($current_byte).$blockwrite;
			}
			$writeable_data = $writeable_data >> 8;
		}

		$this->bitpos += $bitsize;
		$this->pos = (int)($this->bitpos / 8);
	}

	public function WriteUint8($byte) {
		$this->data .= chr(($byte & 0xff));
		$this->pos++;
		$this->bitpos += 8;
		$this->length++;
	}

	public function WriteUint16($word) {
		$this->WriteUint8($word & 0xff);
		$this->WriteUint8(($word >> 8) & 0xff);
	}

	public function WriteUint32($dword) {
		$this->WriteUint8($dword & 0xff);
		$this->WriteUint8(($dword >> 8) & 0xff);
		$this->WriteUint8(($dword >> 16) & 0xff);
		$this->WriteUint8(($dword >> 24) & 0xff);
	}

	public function WriteInt32($int) {
		if($int < 0) {
			$int += myByteReader::MAXUINT32;
		}
		$this->WriteUint32($int);
	}

	public function WriteInt64($long){
		if($long < 0) {
			$long += myByteReader::MAXINT64;
		}
		$this->WriteUint32($long & 0xffffffff);
		$this->WriteUint32(($long >> 32) & 0xffffffff);
	}

	public function WriteString($string, $length = 0, $fill = "\0"){
		$strlen = strlen($string);
		if($length == 0) {
			$this->data .= $string;
			$this->pos += $strlen;
			$this->bitpos += (8 * $strlen);
			$this->length += $strlen;
			return;
		}
		if($strlen > $length) {
			$string = substr($string, 0, $length);
		}
		$this->data .= $string;
		for($i = 0; $i < $length - $strlen; $i++) {
			$this->data .= $fill;
		}

		$this->pos += $length;
		$this->bitpos += (8 * $length);
		$this->length += $length;
	}

	public function WriteFile($file) {
		file_put_contents($file, $this->data);
	}

	//can read up to 63 bits
	/*public function ReadBits64($bitsize) {
		$newdata = 0;
		$bytefirst = (int)($this->bitpos / 8); //first byte we want
		$bitsend = $this->bitpos % 8;
		$bytecount = (int)(($bitsend + $bitsize) / 8) + 1;
		//echo "BP ".$this->bitpos."  BB $bytefirst $bitsize $bitsend $bytecount<br />";

		for($j = 0; $j < 8; $j++) {
			$newdata = $newdata << 8;
			if($bytefirst + $j < $this->length && $j < $bytecount) {
				$char = $this->data[$bytefirst + $j];
				$num = $this->flip(ord($char));
				$newdata += $num & 0xff;
				//echo 'j'.$j.' '.decbin($newdata).'<br />';
			}
		}
		//echo decbin($newdata).' nd<br />';

		$newdata = $newdata << $bitsend;
		//echo decbin($newdata).' << '.$bitsend.'<br />';

		if(($newdata & (1 << 63)) != 0) {
			$newdata = $newdata >> 1;
			//echo decbin($newdata).' >> 1<br />';
			$newdata &= ~(1 << 63);
			//echo decbin($newdata).' & '.decbin(~(1 << 63)).')<br />';
			$newdata = $newdata >> (64 - $bitsize - 1);
			//echo decbin($newdata).' >> '.(64 - $bitsize - 1).'<br />';
		}
		else {
			$newdata = $newdata >> (64 - $bitsize);
			//echo decbin($newdata).' >> '.(64 - $bitsize).'<br />';
		}
		//echo $this->ShowBits($newdata, 8).'<br />';
		// 2  2,5 3

		$this->bitpos += $bitsize;
		$this->pos = (int)($this->bitpos / 8);
		//echo 'BUF '.decbin($newdata).'<br /><br />';
		$newdata = $this->unflip($newdata, $bitsize);
		return $newdata;
	}*/

	// Reverses the bits in a byte. Necessary for reading properly accross byte marks
	public function flip($byte) {
		$ret = 0;
		for ($i = 0; $i < 8; $i++) {
			$ret = ($ret << 1) + (($byte >> $i) & 0x01);
		}
		return $ret;
	}

	// Flips the last x bits of the long as specified by <bits>.
	// This changes the number represented by those bits back to the order expected, so they are evaluated to the proper number
	public function unflip($long, $bits) {
		$ret = 0;
		for ($i = 0; $i < $bits; $i++) {
			$ret = ($ret << 1) + (($long >> $i) & 0x01);
		}
		return $ret;
	}

}

//dbg functions
function dbglog(){
	static $justone = 0;
	if($justone > 0) return;
	$justone++;
	$time = date('Y-m-d H:i:s, ', time());
	$dt = debug_backtrace();
	$dbl = '';
	foreach($dt as $dbg){
		$dbl .= $dbg['file'].', line:'.$dbg['line'].', func:'.$dbg['function'].'<br />';
	}
	echo $dbl;
}

function showbytes($str){
	$len = strlen($str);
	echo $len.': ';
	for($i = 0; $i < $len; $i++){
		echo dechex(ord($str[$i])).' ';
	}
}

function ShowAsBitstream($data) {
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

?>
