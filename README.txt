Diablo II Character and Item Reader Interface - CIRI


Author: The Dude from novapolis.net
Licence: GNU GENERAL PUBLIC LICENSE Version 3


1. BRIEF DESCRIPTION
  Diablo II Character and Item Reader Interface is application for displaying Diablo II character stats and items properties on webpages.
  Application is written in PHP.

  You can see example here http://diablo.novapolis.net/ciri/

  Application is influenced by Atma and Gomule, but does not serve for muling itself.


2. REQUIREMENTS
  To run CIRI you will need web server with PHP installed.


3. INSTALLATION
  a) Copy all the files to chosen folder on your webserver.
  b) You wil need few Diablo II data to properly show properties and images.
     Those are some dc6 files and txt data. You can extract them from Diablo II MPQ files
     or download them from http://diablo.novapolis.net/ciri/Diablo2_114d.zip or find them on web elsewhere.
  c) In fun/config.php file set paths to the required data, mainly
     D2PATH  ... path to Diablo II data, you can extract the zip to d2ver folder.
     D2SAVE  ... path to your Diablo II character saves
     D2STASH ... path to your stashes


4. USAGE
  Open via browser the folder of the ciri and index.php.
  There you see several options like viewing characters, stashes or items.

  Copy save link can copy your saves from defined game folder to application folder.
  However this would only work when you are running it from your PC on localhost.
  On real webserver, copy saves to folder by yourself.

  Character option shows list of characters, you can then view details of picked character.
  By hovering mouse over items, the properties will display.
  Similarly for stashes.


5. NOTES
  If you study source code or the example website you can see there is also some mysql part and write module.

  Mysql part saves item count to database and those data are used by another web application, that is not ready to be released,
  but you can check what it is about here http://diablo.novapolis.net/WebList/

  Write module is a thing to write items with defined features. Basically any item from the game.
  It works but properties has to be added manually, there is no nice interface.
  Also such tool is maybe too powerful to see the ligth of the mortal world.


6. CREDITS
  Diablo II®
  BLIZZARD ENTERTAINMENT®

  Big thanks to these guys and authors of applications and guides
  Especially to gomule authors for releasing source code which was biggest help
  https://sourceforge.net/projects/gomule/
  https://github.com/sylecn/gomule

  https://user.xmission.com/~trevin/DiabloIIv1.09_File_Format.shtml - Diablo II Saved Game File Format
  https://d2mods.info/forum/viewtopic.php?p=248164#248164 - TXT files guide
  https://github.com/krisives/d2s-format - Diablo II Save File Format

  Thanks to
  ATMA
  
  And also some links with guides and useful information about Diablo II, thanks too
  https://d2mods.info/forum/downloadsystemcat?id=23
  http://paul.siramy.free.fr/_divers2/mtmptutcmap/
  https://www.diablofans.com/forums/site-related/diablowiki/3711-dr-tester-guide
  http://paul.siramy.free.fr/_divers2/tmptutcmap/
  http://paul.siramy.free.fr/
  https://d2mods.info/forum/viewtopic.php?f=8&t=9011
  https://github.com/Zutatensuppe/DiabloInterface
  



