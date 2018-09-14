<?php
/**
* milur 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 10:01:31 [Jan 03, 2018])
*/
//
//
//ini_set('max_execution_time', '600');
class magichome extends module {
/**
* milur
*
* Module class constructor
*
* @access private
*/
function magichome() {
  $this->name="magichome";
  $this->title="Magic Home";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;



$cmd_rec = SQLSelectOne("SELECT VALUE FROM magichome_config where parametr='DEBUG'");
$debug=$cmd_rec['VALUE'];

$out['MSG_DEBUG']=$debug;



// $this->search_devices($out);


  $this->data=$out;
//  $this->checkSettings();

  
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {

 $this->getConfig();
// $this->search_devices($out);
$this->search_devices2($out);
  if ($this->view_mode=='' || $this->view_mode=='info'  || $this->view_mode=='scan') {
$this->search_devices($out);
  }






if ($this->view_mode=='delete_devices') {
$this->delete_once($this->id);
}  

  if ($this->view_mode=='edit_magichome_devices') {
   $this->edit_magichome_devices($out, $this->id);
    }


  if ($this->view_mode=='turnon') {
   $this->turnon($this->id);
   $this->getinfo2($this->id, $debug);

    }


  if ($this->view_mode=='turnoff') {
   $this->turnoff($this->id);
   $this->getinfo2($this->id, $debug);
    }



  if ($this->view_mode=='changerandom') {
   $this->turnon($this->id);
   $this->getinfo2($this->id, $debug);
    }

  if ($this->view_mode=='cc_red') {

   $this->set_color($this->id, 255,0,0);
   $this->getinfo2($this->id, $debug);
//   $this->search_devices2($out);

      }

  if ($this->view_mode=='cc_green') {
   $this->set_color($this->id, 0,255,0);
   $this->getinfo2($this->id, $debug);
    }

  if ($this->view_mode=='cc_blue') {
   $this->set_color($this->id, 0,0,255);
   $this->getinfo2($this->id, $debug);
    }

  if ($this->view_mode=='cc_white') {
   $this->set_color($this->id, 255,255,255);
   $this->getinfo2($this->id, $debug);
    }

  if ($this->view_mode=='cc_yellow') {
   $this->set_color($this->id, 255,255,0);
   $this->getinfo2($this->id, $debug);
    }

  if ($this->view_mode=='cc_lightblue') {
   $this->set_color($this->id, 0,255,255);
   $this->getinfo2($this->id, $debug);
    }


  if (substr($this->view_mode,0,9)=='customdec') {
$color=substr($this->view_mode,10);

$ar=explode("@",$color);
   $this->set_color($this->id, $ar[0],$ar[1],$ar[2]);
   $this->getinfo2($this->id, $debug);
    }

//('test.br', substr($this->view_mode,0,9));
  if (substr($this->view_mode,0,9)=='customhex') {
$color=substr($this->view_mode,11);

$ar = hexdec(str_split($color, 2));

   $this->set_color($this->id, $ar[0],$ar[1],$ar[2]);
   $this->getinfo2($this->id, $debug);
    }

//sg('test.br', substr($this->view_mode,0,10));
  if (substr($this->view_mode,0,10)=='brightness') {

$brightness=substr($this->view_mode,10);
//sg('test.br', $brightness);

$this->brightness($this->id, $brightness);
   $this->getinfo2($this->id, $debug);
    }


  if ($this->view_mode=='getinfo') {
   $this->getinfo2($this->id, $debug);
    }




sg('test.bra', $this->view_mode);


}  
 

 function search_devices(&$out) {

$mhdevices=SQLSelect("SELECT * FROM magichome_devices");
$total = count($mhdevices);
for ($i = 0; $i < $total; $i++)
{ 
$ip=$mhdevices[$i]['IP'];
$lastping=$mhdevices[$i]['LASTPING'];
//echo time()-$lastping;
if (time()-$lastping>300) {
$online=ping(processTitle($ip));
    if ($online) 
{SQLexec("update magichome_devices set ONLINE='1', LASTPING=".time()." where IP='$ip'");} 
else 
{SQLexec("update magichome_devices set ONLINE='0', LASTPING=".time()." where IP='$ip'");}
}}


  $mhdevices=SQLSelect("SELECT *, substr(CURRENTCOLOR,13,6) CCOLOR, substr(CURRENTCOLOR,10,2) BR, substr(CURRENTCOLOR,5,2) TURN FROM magichome_devices");
  if ($mhdevices[0]['ID']) {
   $out['DEVICES']=$mhdevices;

    }

}   


 function search_devices2(&$out) {
  $mhdevices=SQLSelect("SELECT *, substr(CURRENTCOLOR,13,6) CCOLOR, substr(CURRENTCOLOR,10,2) BR, substr(CURRENTCOLOR,5,2) TURN FROM magichome_devices");
  $out['DEVICES']=$mhdevices;

}   
 

  
 

/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);

}
/**

*
* @access public
*/
 



function checkSettings() {

}


//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
 function scan() {

$ip = "255.255.255.255";
$port = 48899;

$str  = 'HF-A11ASSISTHREAD';


		$cs = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

		if(!$cs){
echo "error socket";
		}

		socket_set_option($cs, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_option($cs, SOL_SOCKET, SO_BROADCAST, 1);
		socket_set_option($cs, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>1, 'usec'=>0));
		socket_bind($cs, 0, 0);

socket_sendto($cs, $str, strlen($str), 0, $ip, $port);
                    //socket_recvfrom($sock, $buf,100, 0, $ip, $port);
		while(socket_recvfrom($cs, $buf, 2048, 0, $ip, $port)){

//sg('test.buf',$buf);



			if($buf != NULL){
if ($ip) {

$par=explode(",",$buf);

  $mhdevices=SQLSelect("SELECT * FROM magichome_devices where MAC='".$par[1]."' and IP='$ip'");
  if ($mhdevices[0]['ID']) {} else 

{  $mhdevices=SQLSelect("SELECT max(ID) ID FROM magichome_devices");
  if ($mhdevices[0]['ID']) {
   $id=$mhdevices[0]['ID']+1;} else $id=0;


$par['ID'] = $id;
//$par['TITLE'] = 'RGB LED';

$par['TITLE'] = $par[2];
$par['IP'] = $ip;
$par['PORT'] = $port;
$par['MAC'] = $par[1];
$par['FIND'] = date('m/d/Y H:i:s',time());		
SQLInsert('magichome_devices', $par);		 
}
}
 			}
		}

		@socket_shutdown($cs, 2);
		socket_close($cs);





//$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
//socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1); 
//socket_sendto($sock, $str, strlen($str), 0, $ip, $port);
//socket_recvfrom($sock, $buf,100 , 0, $ip, $port);
//usleep(100);
//socket_close($sock);

//        $buf = null;
//        $data = null;
//        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
//            socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1); 
//            socket_sendto($socket, $str, strlen($str), 0, $ip, $port);
//while (($buffer = socket_read($socket, 1024))!=false) {
//          $data = $data + $buffer;
//    echo("Data sent was: time\nResponse was:" . $buffer . "\n");

//}
	 



//}



//$data="";
//do {
//        $buf = null;
//        if (($len = @socket_recvfrom($sock, $buf, 1024, 0, $ip, $port)) == -1) {
//            echo "socket_read() failed: " . socket_strerror(socket_last_error()) . "\n";
//        }
//        if(!is_null($buf)){
//            $data = $data + $buf;
//        }
//    } while(!is_null($buf));
//    socket_close($sock);



//echo "Messagge : < $buf > , $ip : $port <br>";
//$buf=$data;

//$msg = bytearray();
//$lead_byte = #0x51
//sg('test.magichome',$buf .":".$ip.":".$port);

/*
if ($ip) {

$par=explode(",",$buf);

  $mhdevices=SQLSelect("SELECT * FROM magichome_devices where MAC='".$par[1]."' and IP='$ip'");
  if ($mhdevices[0]['ID']) {} else 

{  $mhdevices=SQLSelect("SELECT max(ID) ID FROM magichome_devices");
  if ($mhdevices[0]['ID']) {
   $id=$mhdevices[0]['ID']+1;} else $id=0;


$par['ID'] = $id;
//$par['TITLE'] = 'RGB LED';

$par['TITLE'] = $par[2];
$par['IP'] = $ip;
$par['PORT'] = $port;
$par['MAC'] = $par[1];
$par['FIND'] = date('m/d/Y H:i:s',time());		
SQLInsert('magichome_devices', $par);		 
}
}
 */




}

 function edit_magichome_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/magichome_devices_edit.inc.php');
 }






function delete_once($id) {
  SQLExec("DELETE FROM magichome_devices WHERE id=".$id);
  $this->redirect("?");
 }



function turnon($id) {
$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];

$port=5577;


$debug="";


if(!($sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"))))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

//Connect socket to remote server
if(!socket_connect($sock , $host , $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);


}

//71:23:0f:a3
        $broadcast_string = chr(0x71).chr(0x23).chr(0x0f).chr(0xa3);

        socket_sendto($sock, $broadcast_string, strlen($broadcast_string), 0, $host, $port);




 }


function turnoff($id) {

$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];

$port=5577;


if(!($sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"))))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

//Connect socket to remote server
if(!socket_connect($sock , $host , $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);


}

//71:24:0f:a4

        $broadcast_string = chr(0x71).chr(0x24).chr(0x0f).chr(0xa4);

        socket_sendto($sock, $broadcast_string, strlen($broadcast_string), 0, $host, $port);
}


function set_color($id, $R,$G,$B) {
//color         1 1 1 	31:01:01:01:00:f0:0f:33

$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];

$port=5577;


if(!($sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"))))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

//Connect socket to remote server
if(!socket_connect($sock , $host , $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);


}

//71:24:0f:a4

//        $broadcast_string = chr(0x71).chr(0x24).chr(0x0f).chr(0xa4);

//color         1 1 1 	31:01:01:01:00:f0:0f:33
//str_pad (27, 5,"0",STR_PAD_LEFT); 
$HR=str_pad(dechex($R),2,"0");
$HG=str_pad(dechex($G),2,"0");
$HB=str_pad(dechex($B),2,"0");

//$HR=dechex($R);
//$HG=dechex($G);
//$HB=dechex($B);

//$message="31:01:01:01:00:f0:0f";
$message="31:$HR:$HG:$HB:00:f0:0f";
$message=str_replace(":","",$message);
$message=$message.$this->csum($message);
//sg('test.message', $message);
$hexmessage=hex2bin($message);

        socket_sendto($sock, $hexmessage, strlen($hexmessage), 0, $host, $port);
        usleep(100);
socket_close($sock);
}


function brightness($id, $brightness) {
//color         1 1 1 	31:01:01:01:00:f0:0f:33

$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];

$port=5577;


if(!($sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"))))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

//Connect socket to remote server
if(!socket_connect($sock , $host , $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);


}

//31:00:00:00:05:0f:0f:54 //1%
//31:00:00:00:7f:0f:0f:ce //50%
//31:00:00:00:ff:0f:0f:4e //100 %


//        $broadcast_string = chr(0x71).chr(0x24).chr(0x0f).chr(0xa4);

//color         1 1 1 	31:01:01:01:00:f0:0f:33
//str_pad (27, 5,"0",STR_PAD_LEFT); 
$BR=str_pad(dechex($brightness),2,"0");

//$HR=dechex($R);
//$HG=dechex($G);
//$HB=dechex($B);

//$message="31:01:01:01:00:f0:0f";
$message="31:00:00:00:".$BR.":f0:0f";
$message=str_replace(":","",$message);
$message=$message.$this->csum($message);
//sg('test.message', $message);
$hexmessage=hex2bin($message);

        socket_sendto($sock, $hexmessage, strlen($hexmessage), 0, $host, $port);
        usleep(100);
socket_close($sock);
}




function getinfo2($id) {
$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];

$port=5577;


if(!($sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"))))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

//Connect socket to remote server
if(!socket_connect($sock , $host , $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);


}
//81:8a:8b:96
$message="81:8a:8b";
$message=str_replace(":","",$message);
$message=$message.$this->csum($message);
//sg('test.message', $message);
$hexmessage=hex2bin($message);

        socket_sendto($sock, $hexmessage, strlen($hexmessage), 0, $host, $port);
//        usleep(100);
/*
        do
        {
                $pkt = fread($sock, 10);
                if ( !empty($pkt) && ord($pkt[0]) == 0xAA )
                echo ord($pkt[1]).".".ord($pkt[2]).".".ord($pkt[3]).".".ord($pkt[4])."\n";
        }
        while ( $pkt != false );
*/

            $receiveStr = "";
            $receiveStr = socket_read($sock, 1024, PHP_BINARY_READ);  // The 2 band data received 
                      $receiveStrHex = bin2hex ($receiveStr);   // the 2 hexadecimal data convert 16 hex




//$debug.="Message [$broadcast_string] send successfully <br>";

//$receiveStr = socket_read($socket, 1024, PHP_BINARY_READ);  // The 2 band data received 
//$receiveStrHex = bin2hex ($pkt);   // the 2 hexadecimal data convert 16 hex
//$receiveStrHex =  ($pkt);   // the 2 hexadecimal data convert 16 hex

//$receiveStrHex = ord($pkt[1]).".".ord($pkt[2]).".".ord($pkt[3]).".".ord($pkt[4]);

// $debug.= "Received message [$receiveStr] <br>";
//sg('test.answ',  $receiveStrHex);


//	813323612105ff00000003000060 //R
//	81332361210500ff000003000060    //G
//	8133236121050000ff0003000060       //B

SQLexec("update magichome_config set value='$receiveStrHex' where parametr='DEBUG'");

socket_close($sock);






// $sendStr = '81:8a:8b:96'; 
//        $sendStrArray = str_split(str_replace(':', '', $sendStr), 2);  // The 16 binary data into a set of two arrays
/*
//   $socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));  // Create Socket
        if (socket_connect($socket, $host, $port)) {  //Connect

        $sendStrArray = str_split(str_replace(':', '', $sendStr), 2);  // The 16 binary data into a set of two arrays
     
                      for ($j = 0; $j <count ($sendStrArray); $j++) {
                              socket_write ($socket, Chr (hexdec ($sendStrArray[$j])));   // by group data transmission
            }
            $receiveStr = "";
            $receiveStr = socket_read($socket, 1024, PHP_BINARY_READ);  // The 2 band data received 
                      $receiveStrHex = bin2hex ($receiveStr);   // the 2 hexadecimal data convert 16 hex

socket_close($sock);
}
*/

//$buf=$receiveStr;
$buf= $receiveStrHex;


//$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
//socket_sendto($sock,  $sendStr, strlen( $sendStr), 0, $host, $port);
//socket_recvfrom($sock, $buf,100 , 0, $host, $port);
//socket_close($sock);

//   $socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));  // Create Socket
//        if (socket_connect($socket, $host, $port)) {  //Connect


//        $sendStrArray = str_split(str_replace(':', '', $sendStr), 2);  // The 16 binary data into a set of two arrays
 
//                    for ($j = 0; $j <count ($sendStrArray); $j++) {
//                           socket_write ($socket, Chr (hexdec ($sendStrArray[$j])));   // by group data transmission

//            }
//socket_recvfrom($sock, $buf,100 , 0, $ip, $port);
//socket_close($sock);




//        $command[] = 0x55; //last byte always 0x55, will appended to all commands
//        $command[] = 0x710x240xF00x0F; //last byte always 0x55, will appended to all commands
//        $message = vsprintf(str_repeat('%c', count($command)), $command);
//        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
//            socket_sendto($socket, $message, strlen($message), 0, $host, $port);
//            socket_close($socket);
///            usleep($this->getDelay()); //wait 100ms before sending next command
 

// }
//sg('test.rgbbuf', $host.":".$port.":".$buf);
SQLexec("update magichome_devices set CURRENTCOLOR='$buf' where id='$id'");

}







function changerandom($id) {
 }



/**
*
* @access public
*/
 
/**
* milur_devices delete record
*
* @access public
*/
 
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
   parent::uninstall();
  SQLExec('DROP TABLE IF EXISTS magichome_devices');
  SQLExec('DROP TABLE IF EXISTS magichome_config');
  SQLExec('DROP TABLE IF EXISTS magichome_commands');
  SQLExec('delete from settings where NAME like "%MAGICHOME%"');

 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data = '') {

 $data = <<<EOD
 magichome_devices: ID int(10) unsigned NOT NULL auto_increment
 magichome_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 magichome_devices: IP varchar(100) NOT NULL DEFAULT ''
 magichome_devices: PORT varchar(100) NOT NULL DEFAULT ''
 magichome_devices: MAC varchar(100) NOT NULL DEFAULT ''
 magichome_devices: ONLINE varchar(100) NOT NULL DEFAULT ''
 magichome_devices: LASTPING varchar(100) NOT NULL DEFAULT ''
 magichome_devices: CURRENTCOLOR varchar(100) NOT NULL DEFAULT ''
 magichome_devices: FIND varchar(100) NOT NULL DEFAULT ''
 magichome_devices: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 magichome_devices: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);


 $data = <<<EOD
 magichome_commands: ID int(10) unsigned NOT NULL auto_increment
 magichome_commands: TITLE varchar(100) NOT NULL DEFAULT ''
 magichome_commands: VALUE varchar(255) NOT NULL DEFAULT ''
 magichome_commands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 magichome_commands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 magichome_commands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 magichome_commands: LINKED_METHOD varchar(100) NOT NULL DEFAULT '' 
 magichome_commands: UPDATED datetime
EOD;
  parent::dbInstall($data);

 $data = <<<EOD
 magichome_config: parametr  varchar(300) 
 magichome_config: value varchar(10000)  
EOD;
  parent::dbInstall($data);

  $mhdevices=SQLSelect("SELECT *  FROM magichome_commands");
  if ($mhdevices[0]['ID']) 

{}else{

$par=array();		 
$par['TITLE'] = 'command';
$par['ID'] = "1";		 
SQLInsert('magichome_commands', $par);		 

$par['TITLE'] = 'color';
$par['ID'] = "2";		 
SQLInsert('magichome_commands', $par);		 
                	
$par['TITLE'] = 'level';
$par['ID'] = "3";		 
SQLInsert('magichome_commands', $par);		 

$par['TITLE'] = 'status';
$par['ID'] = "4";		 
SQLInsert('magichome_commands', $par);		 


$par2=array();		 
$par2['parametr'] = 'DEBUG';
$par2['value'] = "";		 
SQLInsert('magichome_config', $par2);		 
}


 }

function csum($str)
{
$ar=str_split ($str,2);

 $csum=0;
 for ($j = 0; $j <count ($ar); $j++) {
 $csum=$csum+hexdec($ar[$j]);
 }
return substr(dechex($csum),-2);
}





}
// --------------------------------------------------------------------
	


/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDAzLCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/


//info          81:8a:8b:96
//РІРєР» 		71:23:0f:a3
//РІС‹РєР» 		71:24:0f:a4
//color         1 1 1 	31:01:01:01:00:f0:0f:33
//3100:00:00:00:f0:0f:30
//3100ff0000f00f2f
//  3100ff0000f00f2f
//  3100ff0000f00f2f
//31ff:ff:ff:00:f0:0f:2d
//31ff:00:ff:00:f0:0f:2e
//31ff:ff:00:00:f0:0f:2e
	

//level
//31:00:00:00:05:0f:0f:54 //1%
//31:00:00:00:7f:0f:0f:ce //50%
//31:00:00:00:ff:0f:0f:4e //100 %


// sudo tcpdump  ip dst 192.168.1.82 and  ip src 192.168.1.39 -w dump.cap

