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
//$out['ONLINE']=1;


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

  if ($this->view_mode=='' || $this->view_mode=='info') {
   $this->search_devices($out);
  }



if ($this->view_mode=='scan') {
$this->search();
}  

if ($this->view_mode=='delete_devices') {
$this->delete_once($this->id);
}  

  if ($this->view_mode=='edit_magichome_devices') {
   $this->edit_magichome_devices($out, $this->id);
    }


  if ($this->view_mode=='turnon') {
   $this->turnon($this->id);
    }


  if ($this->view_mode=='turnoff') {
   $this->turnoff($this->id);
    }

  if ($this->view_mode=='changerandom') {
   $this->turnon($this->id);
    }


  if ($this->view_mode=='getinfo') {
   $this->turnon($this->getinfo2);
    }


}  
 

 function search_devices(&$out) {

$mhdevices=SQLSelect("SELECT * FROM magichome_devices");
$total = count($mhdevices);
for ($i = 0; $i < $total; $i++)
{ 

$ip=$mhdevices[$i]['IP'];
$lastping=$mhdevices[$i]['IP'];
if (time()-$lastping>300) {
$online=ping(processTitle($ip));
    if ($online) 
{SQLexec("update magichome_devices set ONLINE='1', LASTPING=now() where IP='$ip'");} 
else 
{SQLexec("update magichome_devices set ONLINE='0', LASTPING=now() where IP='$ip'");}
}}


  $mhdevices=SQLSelect("SELECT * FROM magichome_devices");
  if ($mhdevices[0]['ID']) {
   $out['DEVICES']=$mhdevices;

    }
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
* milur_devices search
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
 function search() {

$ip = "255.255.255.255";
$port = 48899;

$str  = 'HF-A11ASSISTHREAD';


$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1); 
socket_sendto($sock, $str, strlen($str), 0, $ip, $port);
socket_recvfrom($sock, $buf,100 , 0, $ip, $port);
socket_close($sock);

//        $buf = null;
//        $data = null;
//        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
//            socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1); 
//            socket_sendto($socket, $str, strlen($str), 0, $ip, $port);
//while (($buffer = socket_read($socket, 1024))!=false) {
//          $data = $data + $buffer;
//    echo("Data sent was: time\nResponse was:" . $buffer . "\n");

//}
socket_close($socket);


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
sg('test.magichome',$buf .":".$ip.":".$port);

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

 function edit_magichome_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/magichome_devices_edit.inc.php');
 }



function setcolor($ip,$port, $color) {
/*
//0x31	command of setting color and color temperature	
//Send	?0X31?+?8bit red data ?+?8bit green data?+?8bit blue data?+?8bit warm white data?+?8bit status sign?+?0xF0 remote,0x0F local?+?check digit?(length of command:8)

//Return	Local(0x0F):no return
//	Remote(0xF0):?0xF0 remote?+ ?0X31?+?0x00?+?check digit?                                                                Status sign:?0XF0? means changing RGB,?0X0F?means W	
//	Note:phone send commands which control static color.Range of static color value is 00-0xff.When value is 0,PWM is 0%;when value is 0XFF,PWM is 100%;	




$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1); 
socket_sendto($sock, $str, strlen($str), 0, $ip, $port);
socket_recvfrom($sock, $buf,100 , 0, $ip, $port);

sg('test.rgb', $buf);
*/

}



function delete_once($id) {
  SQLExec("DELETE FROM magichome_devices WHERE id=".$id);
  $this->redirect("?");
 }



function turnon($id) {
/*
// sudo tcpdump  ip dst 192.168.1.82 and  ip src 192.168.1.39 -w dump.cap
//1 1 1
//31:01:01:01:00:f0:0f:33

//РІРєР» 71:23:0f:a3
//РІС‹РєР» 71:24:0f:a4


//0x71	command of setting key's value(switcher command) command
//Send	?0X71?+?8bit value?+?0xF0remote,0x0F local?+?check digit?(length of command:4)	
//Reurn	?0xF0remote,0x0F local?+ ?0X71?+?switcher status value?+?check digit?	
//	Note:key value0x23 means "turn on",0x24 means "turn off"	
//		POWER OFF				0x24




$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];
$port=$cmd_rec['PORT'];


sg('test.rgb', $host.":".$port);

      $sendStr = '71:23:0f:a3'; 

   $socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));  // Create Socket
        if (socket_connect($socket, $host, $port)) {  //Connect

  
        $sendStrArray = str_split(str_replace(':', '', $sendStr), 2);  // The 16 binary data into a set of two arrays
 
                    for ($j = 0; $j <count ($sendStrArray); $j++) {
                           socket_write ($socket, Chr (hexdec ($sendStrArray[$j])));   // by group data transmission

            }
//        $command[] = 0x55; //last byte always 0x55, will appended to all commands
//        $command[] = 0x710x240xF00x0F; //last byte always 0x55, will appended to all commands
//        $message = vsprintf(str_repeat('%c', count($command)), $command);
//        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
//            socket_sendto($socket, $message, strlen($message), 0, $host, $port);
            socket_close($socket);
///            usleep($this->getDelay()); //wait 100ms before sending next command
 

 }

*/




 }


function turnoff($id) {
/*
// sudo tcpdump  ip dst 192.168.1.82 and  ip src 192.168.1.39 -w dump.cap
//1 1 1
//31:01:01:01:00:f0:0f:33

//РІРєР» 71:23:0f:a3
//РІС‹РєР» 71:24:0f:a4


//0x71	command of setting key's value(switcher command) command
//Send	?0X71?+?8bit value?+?0xF0remote,0x0F local?+?check digit?(length of command:4)	
//Reurn	?0xF0remote,0x0F local?+ ?0X71?+?switcher status value?+?check digit?	
//	Note:key value0x23 means "turn on",0x24 means "turn off"	
//		POWER OFF				0x24


$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];
$port=$cmd_rec['PORT'];


sg('test.rgb', $host.":".$port);
 $sendStr = '71:24:0f:a4'; 


   $socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));  // Create Socket
        if (socket_connect($socket, $host, $port)) {  //Connect


        $sendStrArray = str_split(str_replace(':', '', $sendStr), 2);  // The 16 binary data into a set of two arrays
 
                    for ($j = 0; $j <count ($sendStrArray); $j++) {
                           socket_write ($socket, Chr (hexdec ($sendStrArray[$j])));   // by group data transmission

            }



//        $command[] = 0x55; //last byte always 0x55, will appended to all commands
//        $command[] = 0x710x240xF00x0F; //last byte always 0x55, will appended to all commands
//        $message = vsprintf(str_repeat('%c', count($command)), $command);
//        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
//            socket_sendto($socket, $message, strlen($message), 0, $host, $port);
//            socket_close($socket);
///            usleep($this->getDelay()); //wait 100ms before sending next command
 

 }
*/
}




function getinfo2($id) {
/*
//0x81	command of requesting devices'status	
//Send	?0X81?+?0X8A?+?0X8B?+?check digit?(length of comman:4)

//Return	?0X81?+?8bit device name?+?8bit turn on/off?+?8bit mode value?+?8bit run/pause?+ ?8bit speed value?+?8bit red value?+?8bit green data?+?8bit blue data?+  ?8bit warm white data?+?version NO?+?8bit cool white data?+?8bit status sign?+?check digit?(length of command:14)	
///	"Note:when module received command of checking devices's status,module will reply,
//	?8bit turn on/off?:0x23 means  turn on;0x24 means  turn off
//	?8bit run/pause status?:0x20 means  status in present,0x21 means  pause status,it is unuseful in this item
//	?8bit speed value?means speed value of dynamic model,range:0x01-0x1f,0x01 is the fast
//	Status sign:?0XF0?means RGB,?0X0F?means W"	
*/


$cmd_rec = SQLSelectOne("SELECT IP, PORT FROM magichome_devices WHERE id=".$id);
$host=$cmd_rec['IP'];
$port=$cmd_rec['PORT'];


//sg('test.rgb', $host.":".$port);
 $sendStr = '81:8a:8b:a4'; 


   $socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"));  // Create Socket
        if (socket_connect($socket, $host, $port)) {  //Connect


        $sendStrArray = str_split(str_replace(':', '', $sendStr), 2);  // The 16 binary data into a set of two arrays
 
                    for ($j = 0; $j <count ($sendStrArray); $j++) {
                           socket_write ($socket, Chr (hexdec ($sendStrArray[$j])));   // by group data transmission

            }



//        $command[] = 0x55; //last byte always 0x55, will appended to all commands
//        $command[] = 0x710x240xF00x0F; //last byte always 0x55, will appended to all commands
//        $message = vsprintf(str_repeat('%c', count($command)), $command);
//        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
//            socket_sendto($socket, $message, strlen($message), 0, $host, $port);
//            socket_close($socket);
///            usleep($this->getDelay()); //wait 100ms before sending next command
 

 }

SQLexec("update magichome_devices set CURRENTCOLOR='$msg' where id='$id'");

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
  SQLExec('DROP TABLE IF EXISTS magichome_devices');
  SQLExec('DROP TABLE IF EXISTS magichome_config');
  SQLExec('DROP TABLE IF EXISTS magichome_commands');
  SQLExec('delete from settings where NAME like "%MAGICHOME%"');




  parent::uninstall();
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
 magichome_config: parametr varchar(300)
 magichome_config: value varchar(10000)  
EOD;
   parent::dbInstall($data);

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



 }
}
// --------------------------------------------------------------------
	


/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDAzLCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/


