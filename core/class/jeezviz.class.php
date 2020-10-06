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

class jeezviz extends eqLogic {
    /*     * *************************Attributs****************************** */
    
  /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */
    
    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {
      }
     */

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
    public static function addNewEquipment($_equipmentName, $_iPAddress) {
		log::add('jeezviz', 'debug', '============ Début addNewEquipement ==========');
		log::add('jeezviz', 'debug', 'Fonction addNewEquipment démarrée');
		$getEqlogic = self::byLogicalId($_iPAddress,'jeezviz');
		if (!is_object($getEqlogic))
		{
			try{
				// Création de l'équipement
				$eqLogic = new jeezviz();
				// Nom affiché
				$eqLogic->setName($_equipmentName);
				// Identifiant de l'objet
				$eqLogic->setObject_id(null);
				// Identifiant de l'équipement
				$eqLogic->setLogicalId($_iPAddress);
				// Type de l'équipement
				$eqLogic->setEqType_name('jeezviz');
				// Visibilité de l'équipement
				$eqLogic->setIsVisible(1);
				// Accessibilité de l'équipement
				$eqLogic->setIsEnable(1);
				//Ajout du time out
				$eqLogic->setConfiguration('timeOutStateChange', '1');
				// Sauvegarde de l'équipement
				$eqLogic->save();
				$eqLogic->postUpdate();
				return "L'équipement a été créé. Si la page ne se rafraichi pas dans 5 secondes, faites F5";
			} catch(Exception $e){
				return $e->getMessage();
			}
		}
		else
		{
			throw new Exception("L'équipement existe déjà sous le nom \"" . $getEqlogic->getName() . "\"");
		}
      	log::add('jeezviz', 'debug', '============ Fin addNewEquipement ==========');
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
        
     }

    /*     * **********************Getteur Setteur*************************** */
}


