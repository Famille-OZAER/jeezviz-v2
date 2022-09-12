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
require_once __DIR__  . '/../../3rdparty/jeezvizV2Camera.php';
require_once __DIR__  . '/../../3rdparty/JeezvizV2UserAgent.php';

class jeezvizv2 extends eqLogic {
   
    /*     * ***********************Methode static*************************** */

   public static function cron5() {
      log::add("jeezvizv2","debug","================ Debut cron ================");
      $allEqlogic = eqLogic::byType('jeezvizv2');
      foreach ($allEqlogic as $eqLogic){
         $refreshCmd = $eqLogic->getCmd('action', 'refresh');
         if (is_object($refreshCmd))
         {
            $refreshCmd->execute();
         }
      }
      log::add("jeezvizv2","debug","================ Fin cron ================");
   }
   

   public function postSave() {
      log::add('jeezvizv2', 'debug', '============ Début postSave ==========');
      $defaultActions=array("refresh" => "Rafraichir", 
                  "move_up" => "Déplacer vers le Haut", 
                  "move_down" => "Déplacer vers le Bas", 
                  "move_left" => "Déplacer vers la Gauche", 
                  "move_right" => "Déplacer vers la Droite", 
                  "move_center" => "Centrer la caméra", 
                  "privacy_On" => "Mode Privé On", 
                  "privacy_Off" => "Mode Privé Off", 
                  "audio_On" => "Activer le son", 
                  "audio_Off" => "Désactiver le son", 
                  "ir_On" => "Allumer les led infrarouges", 
                  "ir_Off" => "Eteindre led infrarouges", 
                  //"state_On" => "Allumer", --> Effet non connu
                  //"state_Off" => "Eteindre",  --> Effet non connu
                  "sleep_On" => "Mettre en veille", 
                  "sleep_Off" => "Sortir de veille", 
                  "follow_move_On" => "Activer le suivi de mouvement", 
                  "follow_move_Off" => "Désactiver le suivi de mouvement", 
                  "sound_alarm_On" => "Emettre un son d'alerte",
                  "sound_alarm_Off" => "Arrêter le son d'alerte",
                  "alarmNotify_On" => "Activer les notifications", 
                  "alarmNotify_Off" => "Désactiver les notifications",
                  "alarmNotify_Intense" => "Notifications : Intences",
                  "alarmNotify_Logiciel" => "Notifications : Rappels léger",
                  "alarmNotify_Silence" => "Notifications : Silence",
                  "home_defence_mode_HOME_MODE" => "Désactiver la détection pour toutes les caméras",
                  "home_defence_mode_AWAY_MODE" => "Activer la détection pour toutes les caméras");
                        
      foreach ($defaultActions as $key => $value) {
         $this->createCmd($value, $key, 'action', 'other');
      }     
      log::add('jeezvizv2', 'debug', '============ Fin postSave ==========');
   }
   public function createCmd($cmdName, $logicalID, $type, $subType)
   {
      $getDataCmd = $this->getCmd(null, $logicalID);
      if (!is_object($getDataCmd))
      {
         // Création de la commande
         $cmd = new jeezvizv2Cmd();
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
         return $cmd;
      }
      else{
         $getDataCmd;
      }
   }

   public static function dependancy_info() {
		$return = array();
		$return['progress_file'] = '/tmp/dependancy_jeezvizv2_in_progress';
		$return['state'] = 'ok';
		if (exec('sudo pip3 list | grep -E "pyezviz" | wc -l') < 1) {
			$return['state'] = 'nok';
		}
		return $return;
	}

	public static function dependancy_install() {
		log::remove(__CLASS__ . '_dependancy_install');
      
      log::add('jeezvizv2_dependancy_install', 'debug', '============ Début install dépendances ==========');
      log::add('jeezvizv2_dependancy_install', 'debug', 'script : '.dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('jeezvizv2') . '/dependance');
      log::add('jeezvizv2_dependancy_install', 'debug', 'log'.log::getPathToLog(__CLASS__ . '_dependancy_install'));
     
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('jeezvizv2') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '__dependancy_install'));
	}

}

class jeezvizv2Cmd extends cmd {

   public function execute($_options = array()) {
      log::add('jeezvizv2', 'debug', '============ Début execute ==========');
      if ($this->getType() != 'action') {
         return;
      }
      
      log::add('jeezvizv2', 'debug', 'Fonction execute démarrée');
      $eqLogicId = $this->getEqlogic_id();
      log::add('jeezvizv2', 'debug', 'EqLogic_Id : '.$eqLogicId);
      log::add('jeezvizv2', 'debug', 'Name : '.$this->getName());

      $jeezvizObj = jeezvizv2::byId($this->getEqlogic_id());
      $serial=$jeezvizObj->getConfiguration('serial');
      
      log::add('jeezvizv2', 'debug', 'Serial : '.$serial);         

      $EzvizV2Camera = new EzvizV2Camera($serial, $eqLogicId);
      
      switch (strtoupper($this->getLogicalId()))
      {
         case "REFRESH":
            log::add('jeezvizv2', 'debug', "REFRESH");
            //$this->RefreshCamera($EzvizV2Camera);
            $EzvizV2Camera->refresh($this);
            break;
         case "PRIVACY_ON":
            log::add('jeezvizv2', 'debug', "PRIVACY_ON");
            $EzvizV2Camera->switch_privacy_mode(1);
            break;
         case "PRIVACY_OFF":
            log::add('jeezvizv2', 'debug', "PRIVACY_OFF");    
            $EzvizV2Camera->switch_privacy_mode(0);
            break;
         case "AUDIO_ON":
            log::add('jeezvizv2', 'debug', "AUDIO_ON");
            $EzvizV2Camera->switch_audio_mode(1);
            break;
         case "AUDIO_OFF":
            log::add('jeezvizv2', 'debug', "AUDIO_OFF");    
            $EzvizV2Camera->switch_audio_mode(0);
            break;
         case "IR_ON":
            log::add('jeezvizv2', 'debug', "IR_ON");
            $EzvizV2Camera->switch_ir_mode(1);
            break;
         case "IR_OFF":
            log::add('jeezvizv2', 'debug', "IR_OFF");    
            $EzvizV2Camera->switch_ir_mode(0);
            break;
         case "SLEEP_ON":
            log::add('jeezvizv2', 'debug', "SLEEP_ON");
            $EzvizV2Camera->switch_sleep_mode(1);
            break;
         case "SLEEP_OFF":
            log::add('jeezvizv2', 'debug', "SLEEP_OFF");    
            $EzvizV2Camera->switch_sleep_mode(0);
            break;
         case "FOLLOW_MOVE_ON":
            log::add('jeezvizv2', 'debug', "FOLLOW_MOVE_ON");
            $EzvizV2Camera->switch_follow_move_mode(1);
            break;
         case "FOLLOW_MOVE_OFF":
            log::add('jeezvizv2', 'debug', "FOLLOW_MOVE_OFF");    
            $EzvizV2Camera->switch_follow_move_mode(0);
            break;
         case "SOUND_ALARM_ON":
            log::add('jeezvizv2', 'debug', "SOUND_ALARM_ON");
            $EzvizV2Camera->switch_sound_alarm_mode(1);
            break;
         case "SOUND_ALARM_OFF":
            log::add('jeezvizv2', 'debug', "SOUND_ALARM_OFF");    
            $EzvizV2Camera->switch_sound_alarm_mode(0);
            break;
         case "STATE_ON":
            log::add('jeezvizv2', 'debug', "STATE_ON");
            $EzvizV2Camera->switch_state_mode(1);
            break;
         case "STATE_OFF":
            log::add('jeezvizv2', 'debug', "STATE_OFF");    
            $EzvizV2Camera->switch_state_mode(0);
            break;
         case "ALARMNOTIFY_ON":
            log::add('jeezvizv2', 'debug', "ALARMNOTIFY_ON");
            $EzvizV2Camera->alarm_notify(1);
            break;
         case "ALARMNOTIFY_OFF":
            log::add('jeezvizv2', 'debug', "ALARMNOTIFY_OFF");
            $EzvizV2Camera->alarm_notify(0);
            break;
         case "ALARMNOTIFY_INTENSE":
            log::add('jeezvizv2', 'debug', "ALARMNOTIFY_INTENSE");    
            $EzvizV2Camera->alarm_sound(1);
            break;          
         case "ALARMNOTIFY_LOGICIEL":
            log::add('jeezvizv2', 'debug', "ALARMNOTIFY_LOGICIEL");    
            $EzvizV2Camera->alarm_sound(0);
            break;         
         case "ALARMNOTIFY_SILENCE":
            log::add('jeezvizv2', 'debug', "ALARMNOTIFY_SILENCE");    
            $EzvizV2Camera->alarm_sound(2);
            break;
         case "MOVE_UP":
            log::add('jeezvizv2', 'debug', "MOVE_UP");
            $EzvizV2Camera->move("up");
            break;
         case "MOVE_DOWN":
            log::add('jeezvizv2', 'debug', "MOVE_DOWN");
            $EzvizV2Camera->move("down");
            break;
         case "MOVE_LEFT":
            log::add('jeezvizv2', 'debug', "MOVE_LEFT");
            $EzvizV2Camera->move("left");
            break;
         case "MOVE_RIGHT":
            log::add('jeezvizv2', 'debug', "MOVE_RIGHT");
            $EzvizV2Camera->move("right");
            break;            
         case "MOVE_CENTER":
            log::add('jeezvizv2', 'debug', "MOVE_CENTER");
            $EzvizV2Camera->move_coords(0.5,0.5);
            break;      
         case "HOME_DEFENCE_MODE_HOME_MODE":
            log::add('jeezvizv2', 'debug', "HOME_DEFENCE_MODE_HOME_MODE");
            $EzvizV2Camera->set_home_defence_mode("HOME_MODE");
            break;      
         case "HOME_DEFENCE_MODE_AWAY_MODE":
            log::add('jeezvizv2', 'debug', "HOME_DEFENCE_MODE_AWAY_MODE");
            $EzvizV2Camera->set_home_defence_mode("AWAY_MODE");
            break;
      }
      log::add('jeezvizv2', 'debug', '============ Fin execute ==========');

   }
   
   public function SaveCmdInfo($jeezvizObj, $key, $value, $parentKey=null){
      try {
         log::add('jeezvizv2', 'debug', 'Vérification de la clef '.$parentKey.$key);         
         log::add('jeezvizv2', 'debug', 'value : '.$value);
         if (is_array($value))
         {
            log::add('jeezvizv2', 'debug', 'La clef '.$parentKey.$key.' contient des sous clefs');
            $parentKey= $parentKey.$key.'#';
            foreach($value as $key1 => $value1) {                    
               $this->SaveCmdInfo($jeezvizObj, $key1, $value1, $parentKey);
            }          
         }
         else
         {
            if ($key!=null)
            {
               log::add('jeezvizv2', 'debug', 'Recherche de la commande '.$parentKey.$key);
               $infoCmd = $jeezvizObj->getCmd('info', $parentKey.$key);
               if (is_object($infoCmd))
               { 
                  log::add('jeezvizv2', 'debug', 'Mise à jour de la commande '.$parentKey.$key.' à '.$value);
                  $infoCmd->event($value);
               }
               else
               {
                  log::add('jeezvizv2', 'debug', 'Création de la commande '.$parentKey.$key);
                  // Création de la commande
                  if (is_numeric($value))
                  {
                     $infoCmd = $jeezvizObj->createCmd($parentKey.$key, $parentKey.$key, 'info', 'numeric');
                  }     
                  elseif (is_bool($value)){
                     $infoCmd = $jeezvizObj->createCmd($parentKey.$key, $parentKey.$key, 'info', 'binary');
                  }   
                  else{
                     $infoCmd = $jeezvizObj->createCmd($parentKey.$key, $parentKey.$key, 'info', 'string');
                  }             
                  log::add('jeezvizv2', 'debug', 'Mise à jour de la commande '.$parentKey.$key.' à '.$value);
                  $infoCmd->event($value);
               }
            }         
         }
      } catch (Exception $e) {
         log::add('jeezvizv2', 'debug', $e->getMessage());  
      }      
   }
      
   public function postSave() {
   }
}


