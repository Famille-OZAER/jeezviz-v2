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

/*include_file('3rdparty', 'client', 'php', 'jeezviz');
include_file('3rdparty', 'camera', 'php', 'jeezviz');
include_file('3rdparty', 'userAgent', 'php', 'jeezviz');*/
class jeezviz extends eqLogic {
    /*     * *************************Attributs****************************** */
    
  /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */
    
    /*     * ***********************Methode static*************************** */

    
     // Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {
         $EzvizClient = new EzvizClient();
         $retour=$EzvizClient->get_PAGE_LIST();
         log::add("jeezviz","debug",var_dump($retour));
      }
     

    /*     * *********************Méthodes d'instance************************* */
    
 // Fonction exécutée automatiquement avant la création de l'équipement 
    public function preInsert() {
        
    }

 // Fonction exécutée automatiquement après la création de l'équipement 
    public function postInsert() {
        
    }

 // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
    public function preUpdate() {
        
    }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement 
    public function postUpdate() {
      log::add('jeezviz', 'debug', '============ Début postUpdate ==========');
      $getDataCmd = $this->getCmd(null, 'state');
      if (!is_object($getDataCmd))
      {
         // Création de la commande
         $cmd = new jeezvizCmd();
         // Nom affiché
         $cmd->setName('Etat');
         // Identifiant de la commande
         $cmd->setLogicalId('state');
         // Identifiant de l'équipement
         $cmd->setEqLogic_id($this->getId());
         // Type de la commande
         $cmd->setType('info');
         $cmd->setIsHistorized(1);
         // Sous-type de la commande
         $cmd->setSubType('binary');
         // Visibilité de la commande
         $cmd->setIsVisible(1);
         // Sauvegarde de la commande
         $cmd->save();
      }
      $directions=array("refresh" => "Rafraichir", 
                  "moveup" => "Haut", 
                  "movedown" => "Bas", 
                  "moveleft" => "Gauche", 
                  "moveright" => "Droite", 
                  "privacyOn" => "Mode Privé On", 
                  "privacyOff" => "Mode Privé Off");

      foreach ($directions as $key => $value) {
         $getDataCmd = $this->getCmd(null, $key);
         if (!is_object($getDataCmd))
         {
            // Création de la commande
            $cmd = new jeezvizCmd();
            // Nom affiché
            $cmd->setName($value);
            // Identifiant de la commande
            $cmd->setLogicalId($key);
            // Identifiant de l'équipement
            $cmd->setEqLogic_id($this->getId());
            // Type de la commande
            $cmd->setType('action');
            $cmd->setSubType('other');
            // Visibilité de la commande
            $cmd->setIsVisible(1);
            // Sauvegarde de la commande
            $cmd->save();
         }
      }
    }

 // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
    public function preSave() {
        
    }

 // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
    public function postSave() {
        
    }

 // Fonction exécutée automatiquement avant la suppression de l'équipement 
    public function preRemove() {
        
    }

 // Fonction exécutée automatiquement après la suppression de l'équipement 
    public function postRemove() {
        
    }

    /*     * **********************Getteur Setteur*************************** */
}

class jeezvizCmd extends cmd {
    /*     * *************************Attributs****************************** */
    
    /*
      public static $_widgetPossibility = array();
    */
    
    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

  // Exécution d'une commande  
      public function execute($_options = array()) {
         log::add('jeezviz', 'debug', '============ Début execute ==========');
         if ($this->getType() != 'action') {
            return;
         }
         
         if (strtoupper($this->getLogicalId()) == "REFRESH") {
            return;
         }
         log::add('jeezviz', 'debug', 'Fonction execute démarrée');
         log::add('jeezviz', 'debug', 'EqLogic_Id : '.$this->getEqlogic_id());
         log::add('jeezviz', 'debug', 'Name : '.$this->getName());

         $jeezvizObj = jeezviz::byId($this->getEqlogic_id());
         $serial=$jeezvizObj->getConfiguration('serial');
         
         log::add('jeezviz', 'debug', 'Serial : '.$serial);         

         $EzvizClient = new EzvizClient();
         #$EzvizClient->get_PAGE_LIST();
         $EzvizCamera = new EzvizCamera($EzvizClient, $serial);
         
         switch (strtoupper($this->getLogicalId()))
         {
           case "REFRESH":
            break;
           case "PRIVACYON":
             log::add('jeezviz', 'debug', "PRIVACYON");
             $EzvizCamera->switch_privacy_mode(1);
             break;
           case "PRIVACYOFF":
             log::add('jeezviz', 'debug', "PRIVACYOFF");    
             $EzvizCamera->switch_privacy_mode(0);
             break;
           case "GETSTATUS":
             log::add('jeezviz', 'debug', "GETSTATUS");
             log::add('jeezviz', 'debug', var_dump($EzvizCamera->status()));
             break;
           case "MOVEUP":
             log::add('jeezviz', 'debug', "MOVEUP");
             $EzvizCamera->move("up");
             break;
           case "MOVEDOWN":
             log::add('jeezviz', 'debug', "MOVEDOWN");
             $EzvizCamera->move("down");
             break;
           case "MOVELEFT":
             log::add('jeezviz', 'debug', "MOVELEFT");
             $EzvizCamera->move("left");
             break;
           case "MOVERIGHT":
             log::add('jeezviz', 'debug', "MOVERIGHT");
             $EzvizCamera->move("right");
             break;
         }



         log::add('jeezviz', 'debug', '============ Fin execute ==========');

      }

      public function postSave() {
         $jeezvizObj = jeezviz::byId($this->getEqlogic_id());
         $refreshCmd = $jeezvizObj->getCmd('action', 'refresh');
         if (is_object($refreshCmd))
         {
            $refreshCmd->execute();
         }
      }
    /*     * **********************Getteur Setteur*************************** */
}


