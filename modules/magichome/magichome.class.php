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



 $this->search_devices($out);


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

  if ($this->view_mode=='' || $this->view_mode=='info') {
$this->search_devices($out);
  }



if ($this->view_mode=='scan') {

$this->scan();
//   $this->search_devices($out);
}  

if ($this->view_mode=='delete_devices') {
$this->delete_once($this->id);
}  

  if ($this->view_mode=='edit_devices') {
   $this->edit_devices($out, $this->id);
    }



  if ($this->view_mode=='turnon') {
   $this->turnon($this->id);
   $this->getinfo2($this->id);

    }


  if ($this->view_mode=='colorpicker') {
 global $colorpicker;
//sg('test.colorpicker',$colorpicker);
$colorhex=substr($colorpicker,0,6);
$cid=substr($colorpicker,7);


$cmd_rec = SQLSelect("SELECT * FROM magichome_devices");
$cid=$cmd_rec[0]['ID'];
//sg('test.cid', $cid);

$ar =(str_split($colorhex, 2));

   $this->set_colorhex($cid, $ar[0],$ar[1],$ar[2]);
   $this->getinfo2($cid);
    }



  if ($this->view_mode=='turnoff') {
   $this->turnoff($this->id);
   $this->getinfo2($this->id);
    }

  if (substr($this->view_mode,0,9)=='customdec') {
$color=substr($this->view_mode,10);

$ar=explode("@",$color);
   $this->set_color($this->id, $ar[0],$ar[1],$ar[2]);
   $this->getinfo2($this->id);
    }

//('test.br', substr($this->view_mode,0,9));
//////////////////////////
//////////////////////////
//sg('test.ccolor',substr($this->view_mode,0,11));
  if (substr($this->view_mode,0,11)=='setcolorhex') {
$color=substr($this->view_mode,12);
//sg('test.customhex',$color);

$ar =(str_split($color, 2));

   $this->set_colorhex($this->id, $ar[0],$ar[1],$ar[2]);
   $this->getinfo2($this->id);
    }
//////////////////////////
//////////////////////////
//sg('test.br', substr($this->view_mode,0,10));
  if (substr($this->view_mode,0,10)=='brightness') {

$brightness=substr($this->view_mode,10);
//sg('test.br', $brightness);

$this->brightness($this->id, $brightness);
   $this->getinfo2($this->id);
    }


  if ($this->view_mode=='getinfo') {
//sg('test.sql',$this->id.';'.$sql);
   $this->getinfo2($this->id);
    }

  if (substr($this->view_mode,0,11)=='set_favorit') {
$color=substr($this->view_mode,12);

$ar = hexdec(str_split($color, 2));

   $this->set_favorit($this->id, $color);
   $this->getinfo2($this->id);

    }


//sg('test.bra', $this->view_mode);
}  




 function propertySetHandle($object, $property, $value) {

$sql="SELECT magichome_commands.* FROM magichome_commands WHERE magichome_commands.LINKED_OBJECT LIKE '" . DBSafe($object) . "' AND magichome_commands.LINKED_PROPERTY LIKE '" . DBSafe($property) . "'";
//sg('test.sql',$sql);

     $properties = SQLSelect($sql);
     $total = count($properties);
     if ($total) {

         for ($i = 0; $i < $total; $i++) {
$sql="SELECT * FROM magichome_devices WHERE ID=".(int)$properties[$i]['DEVICE_ID'];
//sg('test.sql2',$sql);
             $device=SQLSelectOne($sql);
             $host=$device['IP'];

	     $deviceid=$device['ID'];
             $type=$device['MODEL']; //0 = white, 1 = rgb
             $command=$properties[$i]['TITLE'];
             $meth=$properties[$i]['LINKED_METHOD'];
             $state=$properties[$i]['VALUE'];             
             $magichomeObject = new magichome();
             $properties[$i]['VALUE']=$value;
             $properties[$i]['UPDATED']=date('Y-m-d H:i:s');

             SQLUpdate('magichome_commands',$properties[$i]);	

//sg('test.mycommand', "command:".$command." value:".$value." type:".$type); 
//             if ($type=='AK001-ZJ100') {
//             if ($type=='AK001-ZJ100') {
//sg('test.substr', substr($type,0,8));
           if (substr($type,0,8)=='AK001-ZJ') {


                     if ($meth=='turnOn') {
                         $magichomeObject->turnon($deviceid);
			 $magichomeObject->getinfo2($deviceid);
                     }

                     if ($meth=='turnOff') {
                         $magichomeObject->turnoff($deviceid);
			 $magichomeObject->getinfo2($deviceid);
                     }


                     if ($meth=='switch') {
		if ($state==0) { $magichomeObject->turnon($deviceid);} 
                       else { $magichomeObject->turnoff($deviceid);} 
			 $magichomeObject->getinfo2($deviceid);
                     }



  
                     if ($command=='status'&& $value=='1') {
                         $magichomeObject->turnon($deviceid);
			 $magichomeObject->getinfo2($deviceid);
                     }
                     if ($command=='status'&& $value=='0') {
                         $magichomeObject->turnoff($deviceid);
			 $magichomeObject->getinfo2($deviceid);
                     }
                       if ($command=='color') {
                        $colorhex=str_replace('#','',$value);
			$ar =(str_split($colorhex, 2));
//sg('test.newcolor',$colorhex);
			$magichomeObject->set_colorhex($deviceid, $ar[0],$ar[1],$ar[2]);
			$magichomeObject->getinfo2($deviceid, $debug);
				             }

                 }  //model
              } //РЎвЂ Р С‘Р С”Р В» Р Т‘Р ВµР Р†Р В°Р в„–РЎРѓР С•Р Р†
 }//if total



}


            

   
function edit_devices(&$out, $id) {
require(DIR_MODULES.$this->name . '/magichome_devices_edit.inc.php');
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
		socket_set_option($cs, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>1, 'usec'=>128));
		socket_bind($cs, 0, 0);

socket_sendto($cs, $str, strlen($str), 0, $ip, $port);
                    //socket_recvfrom($sock, $buf,100, 0, $ip, $port);
		while(@socket_recvfrom($cs, $buf, 1024, 0, $ip, $port)){

//sg('test.buf',$buf);



			if($buf != NULL){
if ($ip) {

$par=explode(",",$buf);

  $mhdevices=SQLSelect("SELECT * FROM magichome_devices where MAC='".$par[1]."' and IP='$ip'");
 if ($mhdevices[0]['ID']) {} else 

{ 
//$id=0;
// $mhdevices=SQLSelect("SELECT max(ID) ID FROM magichome_devices");
//  if ($mhdevices[0]['ID']) {
//   $id=$mhdevices[0]['ID']+1;} 

//$id=100;

$mac=$par[1];

$par1=array();
//$par1['ID'] = $id;
//$par['TITLE'] = 'RGB LED';

$par1['TITLE'] = $par[2];
$par1['IP'] = $ip;
$par1['PORT'] = $port;
$par1['MODEL'] = $par[2];
$par1['MAC'] = $mac;
$par1['FIND'] = date('m/d/Y H:i:s',time());		
SQLInsert('magichome_devices', $par1);		 

$sql="SELECT ID FROM magichome_devices where MAC='$mac' and  IP='$ip'";
//sg( 'test.sql', $sql);
$idd=SQLSelectOne($sql)['ID'];
//sg( 'test.sql', $sql);
//sg( 'test.id', $id.":".$idd);


$sql="SELECT max(ID) ID FROM magichome_commands where DEVICE_ID='$idd' ";
$cmd=SQLSelectOne($sql);
  if ( $cmd['ID']) { null;} else {


$commands=array('status','level', 'color', 'answer', 'command');
$total = count($commands);
     for ($i = 0; $i < $total; $i++) {

               $cmd_rec=array();
               $cmd_rec['DEVICE_ID']=$idd;
               $cmd_rec['TITLE']=$commands[$i];
//               $cmd_rec['MODEL']=$commands[$i];
               SQLInsert('magichome_commands',$cmd_rec);
           
}}}}}}

		@socket_shutdown($cs, 2);
		socket_close($cs);







}

 function edit_magichome_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/magichome_devices_edit.inc.php');
 }






function delete_once($id) {
  SQLExec("DELETE FROM magichome_devices WHERE id=".$id);
  SQLExec("DELETE FROM magichome_commands WHERE DEVICE_ID='$id'");
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


function set_colordec($id, $R,$G,$B) {
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


function set_colorhex($id, $HR,$HG,$HB) {
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
//$HR=str_pad(dechex($R),2,"0");
//$HG=str_pad(dechex($G),2,"0");
//$HB=str_pad(dechex($B),2,"0");

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




function getinfo2($id=0) {
$sql="SELECT IP, PORT FROM magichome_devices WHERE id=".$id;
$cmd_rec = SQLSelectOne($sql);
$host=$cmd_rec['IP'];
$globalid=$id;
$port=5577;


sg('test.sql',$id.';'.$sql);
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

            $receiveStr = "";
            $receiveStr = socket_read($sock, 1024, PHP_BINARY_READ);  // The 2 band data received 
                      $receiveStrHex = bin2hex ($receiveStr);   // the 2 hexadecimal data convert 16 hex




//	813323612105ff00000003000060 //R
//	81332361210500ff000003000060    //G
//	8133236121050000ff0003000060       //B

SQLexec("update magichome_config set value='$receiveStrHex' where parametr='DEBUG'");

socket_close($sock);

$buf= $receiveStrHex;

 


$tempid=$id;
//$tempid=8;
SQLexec("update magichome_devices set CURRENTCOLOR='$buf' where id='$id'");
//echo substr($buf,5,2);
//echo $buf;


$sql="select * from  magichome_commands where device_id='".$tempid."'";
     $properties = SQLSelect($sql);
     $total = count($properties);
     if ($total) {

         for ($i = 0; $i < $total; $i++) {
if ($properties[$i]['TITLE']=='status') {
if (substr($buf,4,2)=='23') {$newvalue=1;} else {$newvalue=0;}
} 
elseif ($properties[$i]['TITLE']=='color')  {$newvalue='#'.str_replace('#','',substr($buf,12,6));}
elseif ($properties[$i]['TITLE']=='level')  
{
$tempclolor=str_replace('#','',substr($buf,12,6));
$ar =(str_split($tempclolor, 2));
//$newvalue=((hexdec($ar[0]/255))+(hexdec($ar[1]/255))+(hexdec($ar[2]/255)))/3;
//$newvalue=round(hexdec((int)max($ar[0],$ar[1],$ar[2]))/255,2)*100;

$newvalue=round(max(hexdec($ar[0]),hexdec($ar[1]),hexdec($ar[2]))/255,2)*100;
}
else $newvalue=$buf; 




$title=$properties[$i]['TITLE'];
$sql="select * from  magichome_commands where device_id='".$tempid."' and title='$title'" ;
$myrec=SQLSelectOne($sql);
$myrec['VALUE']=$newvalue;
$myrec['UPDATED']=date('Y-m-d H:i:s');
SQLUpdate('magichome_commands', $myrec);

if ($myrec['LINKED_OBJECT']!='' && $myrec['LINKED_PROPERTY']!='') {
setGlobal($myrec['LINKED_OBJECT'].'.'.$myrec['LINKED_PROPERTY'], $newvalue,array($this->name => '0'));
}



     if ($myrec['LINKED_OBJECT'] && $myrec['LINKED_METHOD']) { // && $old_value!=$prop['CURRENT_VALUE_STRING']
      $params=array();
      $params=$_REQUEST;
      $params['TITLE']=$properties[$i]['TITLE'];
      $params['VALUE']=$newvalue;


      $methodRes=callMethod($myrec['LINKED_OBJECT'].'.'.$myrec['LINKED_METHOD'], $params);

      if (is_string($methodRes)) {
       $ecmd=$methodRes;
      }

     }



}}

      


}


function set_favorit($id, $color) {
  SQLExec("update magichome_devices set FAVORITCOLOR='$color' WHERE id=".$id);
  $this->redirect("?");
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
 magichome_devices: FAVORITCOLOR varchar(100) NOT NULL DEFAULT ''
 magichome_devices: CURRENTCOLOR varchar(100) NOT NULL DEFAULT ''
 magichome_devices: FIND varchar(100) NOT NULL DEFAULT ''
 magichome_devices: MODEL varchar(100) NOT NULL DEFAULT ''
 magichome_devices: ZONE varchar(100) NOT NULL DEFAULT ''
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
//Р В Р’В Р вЂ™Р’В Р В Р’В Р Р†Р вЂљР’В Р В Р’В Р вЂ™Р’В Р В Р Р‹Р Р†Р вЂљРЎСљР В Р’В Р вЂ™Р’В Р В РІР‚в„ўР вЂ™Р’В» 		71:23:0f:a3
//Р В Р’В Р вЂ™Р’В Р В Р’В Р Р†Р вЂљР’В Р В Р’В Р В Р вЂ№Р В Р вЂ Р В РІР‚С™Р Р†РІР‚С›РІР‚вЂњР В Р’В Р вЂ™Р’В Р В Р Р‹Р Р†Р вЂљРЎСљР В Р’В Р вЂ™Р’В Р В РІР‚в„ўР вЂ™Р’В» 		71:24:0f:a4
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

