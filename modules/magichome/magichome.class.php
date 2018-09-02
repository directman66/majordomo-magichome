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
  $this->checkSettings();

  
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
 $out['MODEL']=SETTINGS_APPMILUR_MODEL;		

 $out['TS']=date('m/d/Y H:i:s',gg(SETTINGS_APPMILUR_MODEL.".timestamp"));		
 $out['COUNTTS']=date('m/d/Y H:i:s',gg(SETTINGS_APPMILUR_MODEL.".countersts"));		

 $out['P']=gg(SETTINGS_APPMILUR_MODEL.".P");		
 $out['U']=gg(SETTINGS_APPMILUR_MODEL.".U");		
 $out['I']=gg(SETTINGS_APPMILUR_MODEL.".I");		



 $out['S0']=gg(SETTINGS_APPMILUR_MODEL.".S0");		
 $out['S1']=gg(SETTINGS_APPMILUR_MODEL.".S1");		
 $out['S2']=gg(SETTINGS_APPMILUR_MODEL.".S2");		

$now=date();

$out['MONTH_WATT']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w', $now-2629743 ,$now));
$out['MONTH_RUB']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w_rub', $now-2629743,$now));

$out['DAY_WATT']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w', $now-86400 ,$now));
$out['DAY_RUB']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w_rub', $now-86400 ,$now));

$out['WEEK_WATT']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w', $now-604800 ,$now));
$out['WEEK_RUB']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w_rub', $now-604800 ,$now));

$out['YEAR_WATT']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w', $now-31556926 ,$now));
$out['YEAR_RUB']=round(getHistorySum(SETTINGS_APPMILUR_MODEL.'.potrebleno_w_rub', $now-31556926 ,$now));


$cmd_rec = SQLSelectOne("SELECT VALUE FROM milur_config where parametr='DEBUG'");
$out['MSG_DEBUG']=$cmd_rec['VALUE'];



 if ($this->view_mode=='get') {
setGlobal('cycle_milurControl','start'); 
$this->getdata();
//echo "start"; 
}  

if ($this->view_mode=='getcounters') {
$this->getcounters();
}  

if ($this->view_mode=='getinfo') {
$this->getinfo2();
}  

if ($this->view_mode=='getipu') {
$this->getpu();
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

 function processCycle() {

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

socket_recvfrom($sock, $buf, 5, 0, $ip, $port);
echo "Messagge : < $buf > , $ip : $port <br>";

$msg = bytearray();
$lead_byte = #0x51


socket_close($sock);
}


/**
* milur_devices edit/add
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




/*
milur_devices - 
*/
  $data = <<<EOD
 magichome_devices: ID int(10) unsigned NOT NULL auto_increment
 magichome_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 magichome_devices: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 magichome_devices: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);

  $data = <<<EOD
 magichome_config: parametr varchar(300)
 magichome_config: value varchar(10000)  
EOD;
   parent::dbInstall($data);



 }
}
// --------------------------------------------------------------------
	

function strToHex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}


function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function hex2str($hex) {
    $str = '';
    for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
    return $str;
}	

/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDAzLCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/


