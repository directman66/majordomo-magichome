<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='magichome_devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {
  //updating '<%LANG_TITLE%>' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'IP' (varchar)
   global $ip;
   $rec['IP']=$ip;
   if (!$rec['IP']) {
       $ok=0;
       $out['ERR_IP']=1;
   }

  //updating 'ZONE' (varchar)
   global $zone;
   $rec['ZONE']=(int)$zone;
  //updating 'DEVICE_TYPE' (varchar)
   global $device_type;
   $rec['DEVICE_TYPE']=(int)$device_type;
  }
  // step: data
  if ($this->tab=='data') {
  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;

       $commands=array('command','status','level');
       if ($rec['DEVICE_TYPE']=='1') {
           $commands[]='color';
       } else {
           //$commands[]='warm';
       }
       $total = count($commands);
       for ($i = 0; $i < $total; $i++) {
           $cmd_rec=SQLSelectOne("SELECT ID FROM magichome_commands WHERE DEVICE_ID=".$rec['ID']." AND TITLE LIKE '".$commands[$i]."'");
           if (!$cmd_rec['ID']) {
               $cmd_rec=array();
               $cmd_rec['DEVICE_ID']=$rec['ID'];
               $cmd_rec['TITLE']=$commands[$i];
               SQLInsert('milight_commands',$cmd_rec);
           }
       }

   } else {
    $out['ERR']=1;
   }
  }
  // step: default
  if ($this->tab=='') {
  }
  // step: data
  if ($this->tab=='data') {
  }
  if ($this->tab=='data') {
   //dataset2
   $new_id=0;
   global $delete_id;
   if ($delete_id) {
    SQLExec("DELETE FROM milight_commands WHERE ID='".(int)$delete_id."'");
   }
   $properties=SQLSelect("SELECT * FROM milight_commands WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ID");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    if ($properties[$i]['ID']==$new_id) continue;
    if ($this->mode=='update') {
        /*
      global ${'title'.$properties[$i]['ID']};
      $properties[$i]['TITLE']=trim(${'title'.$properties[$i]['ID']});
      global ${'value'.$properties[$i]['ID']};
      $properties[$i]['VALUE']=trim(${'value'.$properties[$i]['ID']});
        */
      global ${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      global ${'linked_property'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
      SQLUpdate('milight_commands', $properties[$i]);
      $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
     }

       if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
           addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
       }
       
     if ($properties[$i]['TITLE']=='status') {
         $properties[$i]['SDEVICE_TYPE']='relay';
     } elseif ($properties[$i]['TITLE']=='color') {
         $properties[$i]['SDEVICE_TYPE']='rgb';
     } elseif ($properties[$i]['TITLE']=='level') {
         $properties[$i]['SDEVICE_TYPE']='dimmer';
     }  
       
   }
   $out['PROPERTIES']=$properties;   
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);

