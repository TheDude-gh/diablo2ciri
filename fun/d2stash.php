<?php


//class for reading stast
class D2Stash {

	public const CHARLVL = 75;
	public const D2XVER  = 96;

	public const NONE      = 0;
	public const STASH     = 1;
	public const CHARACTER = 2;
	public const ITEM      = 3;

	public $filename;
	private $bitreader;
	private $itemlist = array();

	public function __construct($file) {
		$this->filename = $file;

		if(!file_exists($this->filename)) {
			echo $this->filename.' not found<br />';
			return;
		}

		$data = file_get_contents($file);
		$this->bitreader = new myByteReader($data);

		$this->ReadStash();

		$data = null;
		$this->bitreader = null;
	}

	public function CalcStashCheckSum() {
		$this->bitreader->SetPos(0);
		$checksum = 0; // unsigned integer checksum

		for ($i = 0; $i < $this->bitreader->GetLength(); $i++){
			$byte = $this->bitreader->ReadUint8();
			if ($i >= 7 && $i <= 10) { //skip checksum bytes
				$byte = 0;
			}
			$checksum = (($checksum << 1) & 0xffffffff) + $byte + (($checksum & 0x80000000) != 0 ? 1 : 0);
		}
		return $checksum;
	}

	public function ReadStash() {
		$type = $this->bitreader->ReadString(3);
		$numItems = $this->bitreader->ReadUint16();
		$version = $this->bitreader->ReadUint16();
		$checksum = $this->bitreader->ReadUint32();

		//$ccs = $this->CalcStashCheckSum();
		//echo dechex($checksum).'<br />'.dechex($ccs).'<br />';

		$this->bitreader->SetPos(11);

		if($type == 'D2X') {
			$this->filetype = $this::STASH;
		}

		if($version != $this::D2XVER) {
			echo 'Bad version '.$version.'<br />';
			return;
		}

		$this->itemlist = array();
		$collectSocketed = 0;
		$jumpOutSocket = false;

		$itemstart = 11; //item start position
		$this->bitreader->SetPos($itemstart);
		for($i = 0; $i < $numItems; $i++) {
			$start = $this->bitreader->GetPos();
			$next = $this->bitreader->FindPos('JM', $start + 2);

			if($next === false) {
				$next = $this->bitreader->GetLength();
			}

			$len = $next - $start;

			//read item
			$item = $this->bitreader->ReadString($len);

			$d2item = new D2Item($item);

			//we have to proccess socketed items
			if($collectSocketed == 0) { //if not socketed, add to list
				$this->itemlist[] = $d2item;
			}
			else { //otherwise add to previous item, until colected all socketed items
				$this->itemlist[count($this->itemlist) - 1]->SocketItems[] = $d2item;
			}

			//collected item, reduce number
			if($collectSocketed > 0) {
				$collectSocketed--;
				$i--; //socketed items are not counted in total item number
				if($collectSocketed == 0) {
					$this->itemlist[count($this->itemlist) - 1]->RuneWordApply();
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

	public function GetItemList() {
		return $this->itemlist;
	}
}
?>
