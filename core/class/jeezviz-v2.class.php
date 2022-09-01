<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__  . '/../../3rdparty/client.php';
require_once __DIR__  . '/../../3rdparty/camera.php';
require_once __DIR__  . '/../../3rdparty/userAgent.php';

class jeezviz-v2 extends eqLogic {
   
    /*     * ***********************Methode static*************************** */

   public static function cron5() {
      log::add("jeezviz-v2","debug","================ Debut cron ================");
      $allEqlogic = eqLogic::byType('jeezviz-v2');
      foreach ($allEqlogic as $eqLogic){
         $refreshCmd = $eqLogic->getCmd('action', 'refresh');
         if (is_object($refreshCmd))
         {
            $refreshCmd->execute();
         }
      }
      log::add("jeezviz-v2","debug","================ Fin cron ================");
   }
   

   public function postSave() {
      log::add('jeezviz-v2', 'debug', '============ Début postSave ==========');
      $defaultActions=array("refresh" => "Rafraichir", 
                  "moveup" => "Haut", 
                  "movedown" => "Bas", 
                  "moveleft" => "Gauche", 
                  "moveright" => "Droite", 
                  "privacyOn" => "Mode Privé On", 
                  "privacyOff" => "Mode Privé Off", 
                  "alarmNotifyOn" => "Activer les notifications", 
                  "alarmNotifyOff" => "Désactiver les notifications",
                  "alarmNotifyIntense" => "Notifications : intences",
                  "alarmNotifyLogiciel" => "Notifications : Rappels léger",
                  "alarmNotifySilence" => "Notifications : Silence");
      $defaultBinariesInfos=array("hik" => "Hikvision",                              
                        "offlineNotify" => "Notification de déconnection",
                        "status" => "Etat");
      $defaultNumericInfos=array("casPort" => "Port CAS",
                        "offlineTimestamp" => "Déconnectée depuis (Timestamp)");
      $defaultOtherInfos=array("name" => "Nom",
                        "deviceSerial" => "Numéro de série",
                        "fullSerial" => "Numéro de série complet",
                        "deviceType" => "Type d'équipement",
                        "devicePicPrefix" => "Url de l'Image",
                        "version" => "Version",
                        "supportExt" => "Extension supportées",
                        "userDeviceCreateTime" => "Date de création",
                        "casIp" => "IP CAS",
                        "channelNumber" => "Canal",
                        "deviceCategory" => "Catégorie",
                        "deviceSubCategory" => "Sous Catégorie",
                        "ezDeviceCapability" => "EzDeviceCapability",
                        "customType" => "Custom Type",
                        "offlineTime" => "Déconnectée depuis",
                        "accessPlatform" => "Accès plateforme",
                        "deviceDomain" => "Domaine",
                        "instructionBook" => "Mode d'emploi");
                        
      foreach ($defaultActions as $key => $value) {
         $this->createCmd($value, $key, 'action', 'other');
      }
      foreach ($defaultBinariesInfos as $key => $value) {
         $this->createCmd($value, $key, 'info', 'binary');
      }
      foreach ($defaultNumericInfos as $key => $value) {
         $this->createCmd($value, $key, 'info', 'numeric');
      }
      foreach ($defaultOtherInfos as $key => $value) {
         $this->createCmd($value, $key, 'info', 'string');
      }
   }
   public function createCmd($cmdName, $logicalID, $type, $subType)
   {
      $getDataCmd = $this->getCmd(null, $logicalID);
      if (!is_object($getDataCmd))
      {
         // Création de la commande
         $cmd = new jeezvizCmd();
         // Nom affiché
         $cmd->setName($cmdName);
         // Identifiant de la commande
         $cmd->setLogicalId($logicalID);
         // Identifiant de l'équipement
         $cmd->setEqLogic_id($this->getId());
         // Type de la commande
         $cmd->setType($type);
         $cmd->setSubType($subType);
         // Visibilité de la commande
         $cmd->setIsVisible(1);
         // Sauvegarde de la commande
         $cmd->save();
      }
   }
}

class jeezviz-v2Cmd extends cmd {

   public function execute($_options = array()) {
      log::add('jeezviz-v2', 'debug', '============ Début execute ==========');
      if ($this->getType() != 'action') {
         return;
      }
      
      log::add('jeezviz-v2', 'debug', 'Fonction execute démarrée');
      log::add('jeezviz-v2', 'debug', 'EqLogic_Id : '.$this->getEqlogic_id());
      log::add('jeezviz-v2', 'debug', 'Name : '.$this->getName());

      $jeezvizObj = jeezviz-v2::byId($this->getEqlogic_id());
      $serial=$jeezvizObj->getConfiguration('serial');
      
      log::add('jeezviz-v2', 'debug', 'Serial : '.$serial);         

      $EzvizClient = new EzvizClient();
      #$EzvizClient->get_PAGE_LIST();
      $EzvizCamera = new EzvizCamera($EzvizClient, $serial);
      
      switch (strtoupper($this->getLogicalId()))
      {
         case "REFRESH":
            $this->RefreshCamera($EzvizCamera);
            break;
         case "PRIVACYON":
            log::add('jeezviz-v2', 'debug', "PRIVACYON");
            $EzvizCamera->switch_privacy_mode(1);
            break;
         case "PRIVACYOFF":
            log::add('jeezviz-v2', 'debug', "PRIVACYOFF");    
            $EzvizCamera->switch_privacy_mode(0);
            break;
         case "ALARMNOTIFYON":
            log::add('jeezviz-v2', 'debug', "ALARMNOTIFYON");
            #$EzvizCamera->alarm_notify(1);    
            $EzvizCamera->alarm_notify(1);
            break;
         case "ALARMNOTIFYOFF":
            log::add('jeezviz-v2', 'debug', "ALARMNOTIFYOFF");    
            #$EzvizCamera->alarm_notify(0);
            $EzvizCamera->alarm_notify(0);
            break;
         case "ALARMNOTIFYINTENSE":
            log::add('jeezviz-v2', 'debug', "ALARMNOTIFYINTENSE");    
            $EzvizCamera->alarm_sound(1);
            break;          
         case "ALARMNOTIFYLOGICIEL":
            log::add('jeezviz-v2', 'debug', "ALARMNOTIFYLOGICIEL");    
            $EzvizCamera->alarm_sound(0);
            break;         
         case "ALARMNOTIFYSILENCE":
            log::add('jeezviz-v2', 'debug', "ALARMNOTIFYSILENCE");    
            $EzvizCamera->alarm_sound(2);
            break;
         case "GETSTATUS":
            log::add('jeezviz-v2', 'debug', "GETSTATUS");
            log::add('jeezviz-v2', 'debug', var_dump($EzvizCamera->status()));
            break;
         case "MOVEUP":
            log::add('jeezviz-v2', 'debug', "MOVEUP");
            $EzvizCamera->move("up");
            break;
         case "MOVEDOWN":
            log::add('jeezviz-v2', 'debug', "MOVEDOWN");
            $EzvizCamera->move("down");
            break;
         case "MOVELEFT":
            log::add('jeezviz-v2', 'debug', "MOVELEFT");
            $EzvizCamera->move("left");
            break;
         case "MOVERIGHT":
            log::add('jeezviz-v2', 'debug', "MOVERIGHT");
            $EzvizCamera->move("right");
            break;
      }
      log::add('jeezviz-v2', 'debug', '============ Fin execute ==========');

   }
   public function RefreshCamera($EzvizCamera)
   {
      log::add('jeezviz-v2', 'debug', '============ Début refresh ==========');
      $retour=$EzvizCamera->load();
      $jeezvizObj = jeezviz-v2::byId($this->getEqlogic_id());
      foreach($retour as $key => $value) {
         $this->SaveCmdInfo($jeezvizObj, $key, $value);
      }
      log::add('jeezviz-v2', 'debug', '============ Fin refresh ==========');
   }
   public function SaveCmdInfo($jeezvizObj, $key, $value){
      log::add('jeezviz-v2', 'debug', 'Vérification de la clef '.$key);
      if (is_array($value))
      {
         foreach($value as $key1 => $value1) {                    
      $this->SaveCmdInfo($jeezvizObj, $key1, $value1);
         }          
      }
      else
      {
         log::add('jeezviz-v2', 'debug', 'Recherche de la commande '.$key);
         $infoCmd = $jeezvizObj->getCmd('info', $key);
         if (is_object($infoCmd))
         { 
         log::add('jeezviz-v2', 'debug', 'Mise à jour de la commande '.$key.' à '.$value);
         $infoCmd->event($value);
         }
      }
   }
      
   public function postSave() {
      /*$jeezvizObj = jeezviz-v2::byId($this->getEqlogic_id());
      $refreshCmd = $jeezvizObj->getCmd('action', 'refresh');
      if (is_object($refreshCmd))
      {
         $refreshCmd->execute();
      }*/
   }
}


