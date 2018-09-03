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

  if ($this->view_mode=='' || $this->view_mode=='search_btdevices') {
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



}  
 

 function search_devices(&$out) {

  $mhdevices=SQLSelect("SELECT * FROM magichome_devices");
  if ($mhdevices[0]['ID']) {
   $out['DEVICES']=$mhdevices;}

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

/*
do {
        $buf = null;
        if (($len = @socket_recvfrom($socket, $buf, 1024, 0, $ip, $port)) == -1) {
//            echo "socket_read() failed: " . socket_strerror(socket_last_error()) . "\n";
        }
        if(!is_null($buf)){
            $data = $data + $buf;
        }
    } while(!is_null($buf));
    socket_close($socket);

*/

//echo "Messagge : < $buf > , $ip : $port <br>";

//$msg = bytearray();
//$lead_byte = #0x51
//sg('test.magichome',$buf .":".$ip.":".$port);

if ($ip) {

$par=explode(",",$buf);

  $mhdevices=SQLSelect("SELECT * FROM magichome_devices where MAC='".$par[1]."'");
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



socket_close($sock);

}

 function edit_magichome_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/magichome_devices_edit.inc.php');
 }



function setcolor($ip,$port, $color) {

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

}



function delete_once($id) {
  SQLExec("DELETE FROM magichome_devices WHERE id=".$id);
  $this->redirect("?");
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
$par['parametr'] = 'command';
$par['value'] = "1";		 
SQLInsert('magichome_commands', $par);		 

$par['parametr'] = 'color';
$par['value'] = "2";		 
SQLInsert('magichome_commands', $par);		 

$par['parametr'] = 'level';
$par['value'] = "3";		 
SQLInsert('magichome_commands', $par);		 

$par['parametr'] = 'status';
$par['value'] = "4";		 
SQLInsert('magichome_commands', $par);		 



 }
}
// --------------------------------------------------------------------
	


/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDAzLCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/


