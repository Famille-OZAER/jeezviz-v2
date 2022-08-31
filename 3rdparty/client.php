<?php
require_once __DIR__."/userAgent.php";
require_once __DIR__."/camera.php";

class EzvizClient
{
    public $account;
    public $password;
    public $sessionID;
    public $lastLogon;
    # public _user_id;
    # public _user_reference;
    public $_session;
    public $sessionId;
    public $_timeout;
    public $_CLOUD;
    public $_CONNECTION;
    public $_UserAgent;
    public $COOKIE_NAME = "sessionId";
    public $CAMERA_DEVICE_CATEGORY = "IPC";
    public $DOORBELL_DEVICE_CATEGORY = "BDoorBell";


    public $EU_API_DOMAIN = "apiieu";
    public $API_BASE_TLD = "ezvizlife.com";
    public $API_ENDPOINT_LOGIN = "/v3/users/login";
    public $API_ENDPOINT_CLOUDDEVICES = "/api/cloud/v2/cloudDevices/getAll";
    public $API_ENDPOINT_PAGELIST = "/v3/userdevices/v1/devices/pagelist";
    public $API_ENDPOINT_DEVICES = "/v3/devices/";
    public $API_ENDPOINT_SWITCH_STATUS = "/api/device/switchStatus";
    public $API_ENDPOINT_PTZCONTROL = "/ptzControl";
    public $API_ENDPOINT_ALARM_SOUND = "/alarm/sound";
    public $API_ENDPOINT_DATA_REPORT = "/api/other/data/report";
    public $API_ENDPOINT_DETECTION_SENSIBILITY = "/api/device/configAlgorithm";
    public $API_ENDPOINT_DETECTION_SENSIBILITY_GET = "/api/device/queryAlgorithmConfig";

    
    public $API_BASE_URI;
    public $LOGIN_URL;
    public $CLOUDDEVICES_URL;
    public $DEVICES_URL;
    public $PAGELIST_URL;
    public $DATA_REPORT_URL;

    public $SWITCH_STATUS_URL;
    public $DETECTION_SENSIBILITY_URL;
    public $DETECTION_SENSIBILITY_GET_URL;



    public $DEFAULT_TIMEOUT = 10;
    public $MAX_RETRIES = 3;

    function get_JsonLastError()
    {
        $lastError=json_last_error();
        switch ($lastError) {
            case JSON_ERROR_NONE:
                #log::add('jeezviz', 'debug', " - Aucune erreur\r\n";
            break;
            case JSON_ERROR_DEPTH:
                log::add('jeezviz', 'debug', " - Profondeur maximale atteinte\r\n");
            break;
            case JSON_ERROR_STATE_MISMATCH:
                log::add('jeezviz', 'debug', " - Inadéquation des modes ou underflow\r\n");
            break;
            case JSON_ERROR_CTRL_CHAR:
                log::add('jeezviz', 'debug', " - Erreur lors du contrôle des caractères\r\n");
            break;
            case JSON_ERROR_SYNTAX:
                log::add('jeezviz', 'debug', " - Erreur de syntaxe ; JSON malformé\r\n");
            break;
            case JSON_ERROR_UTF8:
                log::add('jeezviz', 'debug', " - Caractères UTF-8 malformés, probablement une erreur d\'encodage\r\n");
            break;
            default:
                log::add('jeezviz', 'debug', " - Erreur inconnue\r\n");
            break;
        }
    }
    function get_EZVIZ_Result_Message($response_json)
    {
        if (array_key_exists("meta", $response_json))
        {
            $code=$response_json["meta"]["code"];
        }
        else
        {
            $code=$response_json["resultCode"];
        }
        switch (intval($code)) {
            case 1310735:
                return "La rotation PTZ de l'équipement atteint la limite supérieure";
                break;
            case 2009:
                return "Le réseau de l'appareil est anormal, veuillez vérifier le réseau de l'appareil ou réessayer";
                break;
            case 1310731:
                return "L'appareil est dans un état de protection de la vie privée (fermez l'objectif, puis allez faire fonctionner le PTZ)";
                break;
            case -6:
                return "Erreur de paramètre de demande";
                break;
            case 0:
                return "Operation completed";
                break;
            case 200:
                return "Operation completed";
                break;
            case 400:
                return "Password cannot be empty; login account cannot be empty";
                break;
            case 405:
                return "The method in the client request is forbidden";
            case 1001:
                return "Invalid user name";
                break;
            case 1002:
                return "The user name is occupied";
                break;
            case 1003:
                return "Invalid password";
                break;
            case 1004:
                return "Duplicated password";
                break;
            case 1005:
                return "No more incorrect password attempts are allowed";
                break;
            case 1006:
                return "The phone number is registered";
                break;
            case 1007:
                return "Unregistered phone number";
                break;
            case 1008:
                return "Invalid phone number";
                break;
            case 1009:
                return "The user name and phone does not match";
                break;
            case 1010:
                return "Getting verification code failed";
                break;
            case 1011:
                return "Incorrect verification code";
                break;
            case 1012:
                return "Invalid verification code";
                break;
            case 1013:
                return "The user does not exist";
                break;
            case 1014:
                return "Incorrect password or appKey";
                break;
            case 1015:
                return "The user is locked";
                break;
            case 1021:
                return "Verification parameters exception";
                break;
            case 1026:
                return "The email is registered";
                break;
            case 1031:
                return "Unregistered email";
                break;
            case 1032:
                return "Invalid email";
                break;
            case 1041:
                return "No more attempts are allowed to get verification code";
                break;
            case 1043:
                return "No more incorrect verification code attempts are allowed";
                break;
            case 2000:
                return "The device does not exist";
                break;
            case 2001:
                return "The camera does not existThe camera is not registered to Ezviz Cloud. Check the camera network configuration";
                break;
            case 2003:
                return "The device is offlineRefer to Service Center Trouble Shooting Method";
                break;
            case 2004:
                return "Device exception";
                break;
            case 2007:
                return "Incorrect device serial No.";
                break;
            case 2009:
                return "The device request timeout";
                break;
            case 2030:
                return "The device does not support Ezviz CloudCheck whether the device support Ezviz Cloud. You can also contact our supports: 4007005998";
                break;
            case 5000:
                return "The device is added by yourself";
                break;
            case 5001:
                return "The device is added by others";
                break;
            case 5002:
                return "Incorrect device verification code";
                break;
            case 7001:
                return "The invitation does not exist";
                break;
            case 7002:
                return "Verifying the invitation failed";
                break;
            case 7003:
                return "The invited user does not match";
                break;
            case 7004:
                return "Canceling invitation failed";
                break;
            case 7005:
                return "Deleting invitation failed";
                break;
            case 7006:
                return "You cannot invite yourself";
                break;
            case 7007:
                return "Duplicated invitationYou should call the interface for sharing or deleting the sharing. Troubleshooting: Clear all the sharing data in Ezviz Client and add the device again by calling related interface";
                break;
            case 10001:
                return "Parameters errorParameter is empty or the format is incorrect";
                break;
            case 10002:
                return "accessToken exception or expiredThe accessToken is valid for seven days. It is recommended that you can get the accessToken when the accessToken will be expired or Error Code 10002 appears";
                break;
            case 10004:
                return "The user does not exist";
                break;
            case 10005:
                return "appKey exceptionReturn the error code when appKey is incorrect or appKey status is frozen";
                break;
            case 10006:
                return "The IP is limited";
                break;
            case 10007:
                return "No more calling attempts are allowed";
                break;
            case 10008:
                return "Signature error① For getting the signature type, refer to apidemo and Manual of the Last Version ② The encoding format is UTF-8.";
                break;
            case 10009:
                return "Signature parameters error";
                break;
            case 10010:
                return "Signature timeoutSynchronize the time by calling the Interface of Synchronizing Server Time .";
                break;
            case 10011:
                return "Cloud P2P service is not allowedRefer to Binding Procedure";
                break;
            case 10012:
                return "The third-party account is bound with the Ezviz account";
                break;
            case 10013:
                return "The APP has no permission to call this interface";
                break;
            case 10014:
                return "The APPKEY corresponding third-party userId is not bound with the phoneThe appKey for getting AccessToken is different from the one set in SDK";
                break;
            case 10017:
                return "appKey does not existFill in the App key applied in the official website";
                break;
            case 10018:
                return "AccessToken does not match with AppkeyCheck whether the appKey for getting AccessToken is the same with the one set in SDK.";
                break;
            case 10019:
                return "Password error";
                break;
            case 10020:
                return "The requesting method is required";
                break;
            case 10029:
                return "The call frequency exceeds the upper-limit";
                break;
            case 10030:
                return "appKey and appSecret mismatch.";
                break;
            case 10031:
                return "The sub-account or the EZVIZ user has no permission";
                break;
            case 10032:
                return "Sub-account not exist";
                break;
            case 10034:
                return "Sub-account name already exist";
                break;
            case 10035:
                return "Getting sub-account AccessToken error";
                break;
            case 10036:
                return "The sub-account is frozen.";
                break;
            case 20001:
                return "The channel does not existCheck whether the camera is added again and the channel parameters are updated";
                break;
            case 20002:
                return "The device does not exist①The device does not register to Ezviz. Check the network is connected. ②The device serial No. does not exist.";
                break;
            case 20003:
                return "Parameters exception and you need to upgrade the SDK version";
                break;
            case 20004:
                return "Parameters exception and you need to upgrade the SDK version";
                break;
            case 20005:
                return "You need to perform SDK security authenticationSecurity authentication is deleted";
                break;
            case 20006:
                return "Network exception";
                break;
            case 20007:
                return "The device is offlineRefer to Service Center Check Method";
                break;
            case 20008:
                return "The device response timeoutThe device response timeout. Check the network is connected and try again";
                break;
            case 20009:
                return "The device cannot be added to child account";
                break;
            case 20010:
                return "The device verification code errorThe verification code is on the device tag. It contains six upper-cases";
                break;
            case 20011:
                return "Adding device failed.Check whether the network is connected.";
                break;
            case 20012:
                return "Adding the device failed.";
                break;
            case 20013:
                return "The device has been added by other users.";
                break;
            case 20014:
                return "Incorrect device serial No..";
                break;
            case 20015:
                return "The device does not support the function.";
                break;
            case 20016:
                return "The current device is formatting.";
                break;
            case 20017:
                return "The device has been added by yourself.";
                break;
            case 20018:
                return "The user does not have this device.Check whether the device belongs to the user.";
                break;
            case 20019:
                return "The device does not support cloud storage service.";
                break;
            case 20020:
                return "The device is online and is added by yourself.";
                break;
            case 20021:
                return "The device is online and is not added by the user.";
                break;
            case 20022:
                return "The device is online and is added by other users.";
                break;
            case 20023:
                return "The device is offline and is not added by the user.";
                break;
            case 20024:
                return "The device is offline and is added by the user.";
                break;
            case 20025:
                return "Duplicated sharing.Check whether the sharing exists in the account that added the device.";
                break;
            case 20026:
                return "The video does not exist in Video Gallery.";
                break;
            case 20029:
                return "The device is offline and is added by yourself.";
                break;
            case 20030:
                return "The user does not have the video in this video gallery.";
                break;
            case 20031:
                return "The terminal binding enabled, and failed to verify device code.Disable the terminal binding,refer to this procedure.";
                break;
            case 20032:
                return "The channel does not exist for this user.";
                break;
            case 20033:
                return "The video shared by yourself cannot be added to favorites.";
                break;
            case 20101:
                return "Share the video to yourself.";
                break;
            case 20102:
                return "No corresponding invitation information.";
                break;
            case 20103:
                return "The friend already exists.";
                break;
            case 20104:
                return "The friend does not exist.";
                break;
            case 20105:
                return "The friend status error.";
                break;
            case 20106:
                return "The corresponding group does not exist.";
                break;
            case 20107:
                return "You cannot add yourself as friend.";
                break;
            case 20108:
                return "The current user is not the friend of the added user.";
                break;
            case 20109:
                return "The corresponding sharing does not exist.";
                break;
            case 20110:
                return "The friend group does not belong to the current user.";
                break;
            case 20111:
                return "The friend is not in the status of waiting verification.";
                break;
            case 20112:
                return "Adding the user in application as friend failed.";
                break;
            case 20201:
                return "Handling the alarm information failed.";
                break;
            case 20202:
                return "Handling the leaved message failed.";
                break;
            case 20301:
                return "The alarm message searched via UUID does not exist.";
                break;
            case 20302:
                return "The picture searched via UUID does not exist.";
                break;
            case 20303:
                return "The picture searched via FID does not exist.";
                break;
            case 30001:
                return "The user doesn't exist";
                break;
            case 49999:
                return "Data exception.";
                break;
            case 50000:
                return "The server exception.";
                break;
            case 60000:
                return "The device does not support PTZ control.";
                break;
            case 60001:
                return "The user has no PTZ control permission.";
                break;
            case 60002:
                return "The device PTZ has reached the top limit.";
                break;
            case 60003:
                return "The device PTZ has reached the bottom limit.";
                break;
            case 60004:
                return "The device PTZ has reached the left limit.";
                break;
            case 60005:
                return "The device PTZ has reached the right limit.";
                break;
            case 60006:
                return "PTZ control failed.";
                break;
            case 60007:
                return "No more preset can be added.";
                break;
            case 60008:
                return "The preset number of C6 has reached the limit. You cannot add more preset.";
                break;
            case 60009:
                return "The preset is calling.";
                break;
            case 60010:
                return "The preset is the current position.";
                break;
            case 60011:
                return "The preset does not exist.";
                break;
            case 60012:
                return "Unknown error.";
                break;
            case 60013:
                return "The version is the latest one.";
                break;
            case 60014:
                return "The device is upgrading.";
                break;
            case 60015:
                return "The device is rebooting.";
                break;
            case 60016:
                return "The encryption is disabled.";
                break;
            case 60017:
                return "Capturing failed.";
                break;
            case 60018:
                return "Upgrading device failed.";
                break;
            case 60019:
                return "The encryption is enabled.";
                break;
            case 60020:
                return "The command is not supported.Check whether the device support the command.";
                break;
            case 60021:
                return "It is current arming/disarming status.";
                break;
            case 60022:
                return "It is current status.It is current open or closed status.";
                break;
            case 60023:
                return "Subscription failed.";
                break;
            case 60024:
                return "Canceling subscription failed.";
                break;
            case 60025:
                return "Setting people counting failed.";
                break;
            case 60026:
                return "The device is in privacy mask status.";
                break;
            case 60027:
                return "The device is mirroring.";
                break;
            case 60028:
                return "The device is controlling PTZ.";
                break;
            case 60029:
                return "The device is in two-way audio status.";
                break;
            case 60030:
                return "No more incorrect card password attempts are allowed. Try again after 24 hours.";
                break;
            case 60031:
                return "Card password information does not exist.";
                break;
            case 60032:
                return "Incorrect card password status or the password is expired.";
                break;
            case 60033:
                return "The card password is not for sale. You can only buy the corresponding device.";
                break;
            case 60035:
                return "Buying cloud storage server failed.";
                break;
            case 60040:
                return "The added devices are not in the same LAN with the parent device.";
                break;
            case 60041:
                return "The added devices are not in the same LAN with the parent device.";
                break;
            case 60042:
                return "Incorrect password for added device.";
                break;
            case 60043:
                return "No more devices can be added.";
                break;
            case 60044:
                return "Network connection for the added device timeout.";
                break;
            case 60045:
                return "The added device IP conflicts with the one of other channel.";
                break;
            case 60046:
                return "The added device IP conflicts with the one of parent device.";
                break;
            case 60047:
                return "The stream type is not supported.";
                break;
            case 60048:
                return "The bandwidth exceeds the system accessing bandwidth.";
                break;
            case 60049:
                return "Invalid IP or port.";
                break;
            case 60050:
                return "The added device is not supported. You should upgrade the device.";
                break;
            case 60051:
                return "The added device is not supported.";
                break;
            case 60052:
                return "Incorrect channel No. for added device.";
                break;
            case 60053:
                return "The resolution of added device is not supported.";
                break;
            case 60054:
                return "The account for added device is locked.";
                break;
            case 60055:
                return "Getting stream for the added device error.";
                break;
            case 60056:
                return "Deleting device failed.";
                break;
            case 60057:
                return "The deleted device has no linkage.Check whether there's linkage between IPC and NVR.";
                break;
            case 60060:
                return "The device does not bind.";
                break;
            default:
                return "Error code ".$code." not found";
        }
        return $Error;
    }

    function QueryAPIPost($URL, $postData=null, $timeout=DEFAULT_TIMEOUT, $headers=null)
    {
        try {
            // Setup cURL
            log::add('jeezviz', 'debug', $URL."\r\n");
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            if ($postData!=null)
            {
                $postData1 = http_build_query($postData);  
            }
            else
            {
                $postData1="";
            }
            curl_setopt_array($ch, array(
                CURLOPT_URL => $URL,
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_POSTFIELDS => $postData1,
                CURLOPT_USERAGENT => $this->_UserAgent
            ));           
            // Send the request
            $response = curl_exec($ch);
            // Check for errors
            if ($response === false) {
                log::add('jeezviz', 'debug', "Echec de la requête");
                log::add('jeezviz', 'debug', curl_error($ch));
                log::add('jeezviz', 'debug', curl_errno($ch));
                //echo "Une erreur est survenue, vérifiez les logs";
            }
            curl_close($ch);
            // Decode the response
            $response_json = json_decode($response, TRUE);    
            log::add('jeezviz', 'debug', $response_json);
            $this->get_JsonLastError();
            $msgRetour=$this->get_EZVIZ_Result_Message($response_json);
            if($msgRetour != "Operation completed")
            {
                echo $msgRetour;
            }           
            return $response_json;
			//return true;
        } catch(Exception $e) {
            log::add('jeezviz', 'debug', $e);
            trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        }
    }
    function QueryAPIGet($URL, $postData, $timeout=DEFAULT_TIMEOUT, $headers)
    {
        try {
            // Setup cURL
            log::add('jeezviz', 'debug', $URL."\r\n");
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            $postData1 = http_build_query($postData);
            
            #CURLOPT_URL => $URL."?".$postData1,
            $uri=$URL."?".$postData1;
            log::add('jeezviz', 'debug', $uri."\r\n");
            curl_setopt_array($ch, array(
                CURLOPT_URL=>$uri,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => $this->_UserAgent
            ));

            // Send the request
            $response = curl_exec($ch);
            // Check for errors
            if ($response === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            // Decode the response
            $response_json = json_decode($response, TRUE);    
            $this->get_JsonLastError();                    
            $msgRetour=$this->get_EZVIZ_Result_Message($response_json);
            if($msgRetour != "Operation completed")
            {
                echo $msgRetour;
            }  
            return $response_json;
        } catch(Exception $e) {
            log::add('jeezviz', 'debug', $e);
            trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        }
    }
    function QueryAPIPut($URL, $postData, $timeout=DEFAULT_TIMEOUT, $headers)
    {
        try {
            // Setup cURL
            log::add('jeezviz', 'debug', $URL."\r\n");
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }

            $postData1 = http_build_query($postData);
            
            #CURLOPT_URL => $URL."?".$postData1,
            $uri=$URL."?".$postData1;
            log::add('jeezviz', 'debug', $uri."\r\n");
            curl_setopt_array($ch, array(
                CURLOPT_URL=>$uri,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => $this->_UserAgent
            ));
            
            // Send the request
            $response = curl_exec($ch);
            // Check for errors
            if ($response === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            // Decode the response
            $response_json = json_decode($response, TRUE);    
            $this->get_JsonLastError();        
            $msgRetour=$this->get_EZVIZ_Result_Message($response_json);
            if($msgRetour != "Operation completed")
            {
                echo $msgRetour;
            }  
            return $response_json;
        } catch(Exception $e) {
            log::add('jeezviz', 'debug', $e);
            trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        }
    }
    function __construct($timeout=DEFAULT_TIMEOUT, $cloud=null, $connection=null)
    {
        #"""Initialize the client object."""
        
        $this->account =config::byKey('identifiant', 'jeezviz');
        $this->password = config::byKey('motdepasse', 'jeezviz');
        log::add('jeezviz', 'debug', 'identifiant : '.$this->account);
        //log::add('jeezviz', 'debug', 'motdepasse : '.$this->password);        
        $this->_sessionId = config::byKey('sessionId', 'jeezviz');
        $this->lastLogin = config::byKey('lastLogin', 'jeezviz');
        log::add('jeezviz', 'debug', '_sessionId : '.$this->_sessionId);
        log::add('jeezviz', 'debug', 'lastLogin : '.$this->lastLogin);
        
        $this->_timeout = $timeout;
        $this->_CLOUD = $cloud;
        $this->_CONNECTION = $connection;
        $this->login();
    }
        

    function _login($force=false, $apiDomain="")
    {        
        if ($apiDomain=="")
        {
            $apiDomain=$this->EU_API_DOMAIN;
        }
        $this->API_BASE_URI = "https://".$apiDomain.".".$this->API_BASE_TLD;
		
        $this->LOGIN_URL = $this->API_BASE_URI.$this->API_ENDPOINT_LOGIN;
        $this->CLOUDDEVICES_URL = $this->API_BASE_URI.$this->API_ENDPOINT_CLOUDDEVICES;
        $this->DEVICES_URL = $this->API_BASE_URI.$this->API_ENDPOINT_DEVICES;
        $this->PAGELIST_URL = $this->API_BASE_URI.$this->API_ENDPOINT_PAGELIST;
        $this->DATA_REPORT_URL = $this->API_BASE_URI.$this->API_ENDPOINT_DATA_REPORT;
    
        $this->SWITCH_STATUS_URL = $this->API_BASE_URI.$this->API_ENDPOINT_SWITCH_STATUS;
        $this->DETECTION_SENSIBILITY_URL = $this->API_BASE_URI.$this->API_ENDPOINT_DETECTION_SENSIBILITY;
        $this->DETECTION_SENSIBILITY_GET_URL = $this->API_BASE_URI.$this->API_ENDPOINT_DETECTION_SENSIBILITY_GET;


        if ($this->_sessionId === null)
        {
            log::add('jeezviz', 'debug', 'Pas de token connu, authentification');   
            # setting fake user-agent header
            $this->_UserAgent = (new userAgent) ->generate();
        }       
        #On ne se réauthentifie que si la dernière authent est supérieure à 10 minutes
        log::add('jeezviz', 'debug', '$this->_sessionId : '.$this->_sessionId);   
        log::add('jeezviz', 'debug', '$this->lastLogin : '.$this->lastLogin );   
        log::add('jeezviz', 'debug', '(time() - (60*10)) : '.(time() - (60*10)));   
        if  ($force==true || $this->lastLogin === null || $this->lastLogin < (time() - (60*10)))
        {
            log::add('jeezviz', 'debug', 'Token périmé, authentification');        
        }
        else
        {            
            log::add('jeezviz', 'debug', 'Token encore valide');   
            return True;     
        }

        log::add('jeezviz', 'debug', "Login to Ezviz' API at ".$this->LOGIN_URL);
        # Ezviz API sends md5 of password
        $md5pass = md5(utf8_encode($this->password));
        log::add('jeezviz', 'debug', 'md5pass : '.$md5pass);
        $postData = array("account"=>$this->account, 
                        "password"=>$md5pass, 
                        "featureCode"=>"92c579faa0902cbfcfcc4fc004ef67e7"
                    );
        $headers=array("Content-Type: application/x-www-form-urlencoded", 
                    "clientType: 1", 
                    "customNo: 1000001");
        try
        {
            $response_json = $this->QueryAPIPost($this->LOGIN_URL, $postData, $this->_timeout, $headers);
        }
        catch (Exception $e)
        {
            log::add('jeezviz', 'debug', "Can not login to API\r\n");
        }
        
        # if the apidomain is not proper
        if ($response_json["meta"]["code"] == 1100)
        {
            return $this->_login($response_json["loginArea"]["apiDomain"]);
        }
        
        if ($response_json["meta"]["code"] != 200)
        {
			log::add('jeezviz', 'debug', var_dump($response_json));
            return false;
        }
        # let's parse the answer, session is in {.."loginSession":{"sessionId":"xxx...}
        try
        {
            $sessionId = $response_json["loginSession"]["sessionId"];
            $this->_sessionId = $sessionId;
            config::save("sessionId", $sessionId, 'jeezviz');
            config::save("lastLogin", time(), 'jeezviz');
            log::add('jeezviz', 'debug', "Login successfull sessionId =".$sessionId."\r\n");
        }
        catch (Exception $e)
        {          
            log::add('jeezviz', 'debug', $e);
        }
        return True;
    }

    function _get_pagelist($filter=null, $json_key=null, $max_retries=0)
    {
        log::add('jeezviz', 'debug', "Get data from pagelist API.\r\n");

        if ($max_retries > $this->MAX_RETRIES)
        {
            log::add('jeezviz', 'debug', "Can't gather proper data. Max retries exceeded.");
        }

        if ($filter === null)
        {
            log::add('jeezviz', 'debug', "Trying to call get_pagelist without filter");
        }
        $postData=array('filter'=>$filter);
        $headers=array('sessionId:'.$this->_sessionId);
        try
        {
            $response_json = $this->QueryAPIGet($this->PAGELIST_URL, $postData, $this->_timeout, $headers);
        }
        catch (Exception $e)
        {
            log::add('jeezviz', 'debug', "Could not access Ezviz' API: " + $e);
        }
        if (array_key_exists("meta",$response_json))
        { 
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to relogin
                $this->login(true);
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: ".$max_retries.")");
                return $this->_get_pagelist($max_retries+1);
            }
        }
        if ($json_key === null)
        {
            $json_result = $response_json;
        }
        else
        {
            $json_result = $response_json[$json_key];
        }

        if (!$json_result)
        {
            log::add('jeezviz', 'debug', "Impossible to load the devices, here is the returned response: ".$response_json);
        }
        
        return $json_result;
    }

    function _switch_status($serial, $status_type, $enable, $max_retries=0)
    {
        #"""Switch status on a device"""

        try
        {
            $data=array('sessionId'=>$this->_sessionId,
                'enable'=>$enable, 
                'serial'=>$serial, 
                'channel'=>'0', 
                'netType'=>'WIFI', 
                'clientType'=>'1', 
                'type'=>$status_type);
            $headers=array("Content-Type: application/x-www-form-urlencoded", 
                "clientType: 1", 
                "customNo: 1000001");
            $response_json = $this->QueryAPIPost($this->SWITCH_STATUS_URL, $data, $this->_timeout,$headers);

            if (array_key_exists("meta",$response_json))
            {
                if ($response_json["meta"]["code"] == 401)
                {
                    # session is wrong, need to relogin
                    $this->login(true);
                    log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                    return $this->_switch_status($serial, $type, $enable, $max_retries+1);
                }
            }
        }            
        catch (Exception $e)
        {
            log::add('jeezviz', 'debug', "Could not access Ezviz' API: ".$e);
        }
        return True;
    }
       

    function _switch_devices_privacy($enable=0)
    {
        log::add('jeezviz', 'debug', "Switch privacy status on ALL devices (batch)\r\n");

        #  enable=1 means privacy is ON

        # get all devices
        $devices = $this->_get_devices();

        # foreach, launch a switchstatus for the proper serial
        foreach ($devices as $device)
        {
            $serial = $device['serial'];
            $this->_switch_status($serial, $TYPE_PRIVACY_MODE, $enable);
        }

        return True;
    }
    function load_cameras()
    {
        #"""Load and return all cameras objects"""

        # get all devices
        $devices = $this->get_DEVICE();
        $cameras = [];

        # foreach, launch a switchstatus for the proper serial
        foreach ($devices as $device)
        {
            if ($device['deviceCategory'] == $CAMERA_DEVICE_CATEGORY)
            {
                $camera = EzvizCamera($this, $device['deviceSerial']);
                $camera.load();
                $cameras.append(camera.status());
            }
            if (device['deviceCategory'] == $DOORBELL_DEVICE_CATEGORY)
            {
                $camera = EzvizCamera($this, $device['deviceSerial']);
                $camera.load();
                $cameras.append($camera.status());
            }
        }
        return $cameras;
    }
        
    # soundtype: 0 = normal, 1 = intensive, 2 = disabled ... don't ask me why...

    function detection_sensibility($serial, $sensibility=3, $max_retries=0)
    {
        log::add('jeezviz', 'debug', "Enable alarm notifications.\r\n");
        if ($max_retries > $this->MAX_RETRIES)
        {
            log::add('jeezviz', 'debug', "Can't gather proper data. Max retries exceeded.");
        }
            

        if (!in_array($sensibility,array(0,1,2,3,4,5,6)))
        {
            log::add('jeezviz', 'debug', "Unproper sensibility (should be within 1 to 6).");
        }
            

        try
        {
            $headers=array('sessionId:'.$this->_sessionId);
            $data=array('subSerial'=>$serial, 'type'=>'0', 'sessionId'=>$this->_sessionId, 'value'=>$sensibility);
            $response_json = $this->QueryAPIPost($this->DETECTION_SENSIBILITY_URL, $data, $timeout=$this->_timeout,$headers);
        }
        catch (Exception $e)
        {
            log::add('jeezviz', 'debug', "Could not access Ezviz' API: ".$e);
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login(true);
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                return $this->detection_sensibility($serial, $enable, $max_retries+1);
            }
        }
        return True;
    }

    function get_detection_sensibility($serial, $max_retries=0)
    {
        log::add('jeezviz', 'debug', "Get detection sensibility.\r\n");
        if ($max_retries > $this->MAX_RETRIES)
        {
            log::add('jeezviz', 'debug', "Can't gather proper data. Max retries exceeded.");
        }

        try
        {
            $headers=array('sessionId:'.$this->_sessionId);
            $data=array('subSerial'=>$serial, 'sessionId'=>$this->_sessionId, 'clientType'=>1);
            $response_json = $this->QueryAPIPost($this->DETECTION_SENSIBILITY_GET_URL, $data, $timeout=$this->_timeout,$headers);
        }
        catch (Exception $e)
        {
            log::add('jeezviz', 'debug', "Could not access Ezviz' API: ".$e);
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login(true);
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                return $this->get_detection_sensibility($serial, $enable, $max_retries+1);
            }
        }
        
        if ($response_json['resultCode'] != '0')
        {
            //log::add('jeezviz', 'debug', "Could not get detection sensibility : ".var_dump($response_json['resultCode']).")");
            return 'Unknown';
        }
        else
        {
            return $response_json['algorithmConfig']['algorithmList'][0]['value'];
        }
    }

    function alarm_sound($serial, $soundType, $enable=1, $max_retries=0)
    {
        log::add('jeezviz', 'debug', "Enable alarm sound by API.\r\n");
        if ($max_retries > $this->MAX_RETRIES)
        {
            log::add('jeezviz', 'debug', "Can't gather proper data. Max retries exceeded.");
        }

        if (!in_array( array(0,1,2),$soundType))
        {
            log::add('jeezviz', 'debug', "Invalid soundType, should be 0,1,2: ".$soundType);
        }
        
        try
        {
            $data=array('enable'=>$enable, 'soundType'=>$soundType, 'voiceId'=>'0', 'deviceSerial'=>$serial);            
            $headers=array('sessionId:'.$this->_sessionId);
            $response_json = $this->QueryAPIPut($this->DEVICES_URL.$serial.$this->API_ENDPOINT_ALARM_SOUND, $data, $timeout=$this->_timeout, $headers);
        }
        catch (Exception $e)
        {
            log::add('jeezviz', 'debug', "Could not access Ezviz' API: ".$e);
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login(true);
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                return $this->alarm_sound($serial, $enable, $soundType, $max_retries+1);
            }   
        }    
        return True;
    }


    function data_report($serial, $enable=1, $max_retries=0)
    {
        try {
            log::add('jeezviz', 'debug', "Enable alarm notifications.\r\n");
            if ($max_retries > $this->MAX_RETRIES)
            {
                log::add('jeezviz', 'debug', "Can't gather proper data. Max retries exceeded.");
            }            

            # operationType = 2 if disable, and 1 if enable
            $operationType = 2 - intval($enable);
            log::add('jeezviz', 'debug', "enable: {".$enable."}, operationType: {".$operationType."}");
            $infoDetail=json_encode(array("operationType" =>$operationType, "detail"=>'0', "deviceSerial"=>$serial.",2"));
            $postData=array('clientType'=>'1', 
                        'infoDetail'=>$infoDetail, 
                        'infoType'=>'3', 
                        'netType'=>'WIFI', 
                        'reportData'=>null, 
                        'requestType'=>'0', 
                        'sessionId'=>$this->_sessionId);
            try
            {
                $response_json = $this->QueryAPIPost($this->DATA_REPORT_URL, $postData, $this->_timeout);
            }
            catch (Exception $e)
            {
                log::add('jeezviz', 'debug', "Could not access Ezviz' API: ".$e);
            }
            if (array_key_exists("meta",$response_json))
            {
                if ($response_json["meta"]["code"] == 401)
                {
                    # session is wrong, need to re-log-in
                    $this->login(true);
                    log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                    return $this->data_report($serial, $enable, $max_retries+1);
                }
                else
                {
                    log::add('jeezviz', 'debug', var_dump($response_json));
                }
            }
            return True;
        } catch (Exception $e) {
            log::add('jeezviz', 'debug', $e->getMessage());
        }
        
    }
    function ptzControl($command, $serial, $action, $speed=5, $max_retries=0)
    {
        log::add('jeezviz', 'debug', "PTZ Control by API.\r\n");
        if ($max_retries > $this->MAX_RETRIES)
        {
            log::add('jeezviz', 'debug', "Can't gather proper data. Max retries exceeded.");            
        }

        if ($command === null)
        {
            log::add('jeezviz', 'debug', "Trying to call ptzControl without command");
        }
        if ($action === null)
        {
            log::add('jeezviz', 'debug', "Trying to call ptzControl without action");
        }

        try
        {
            $data=array('command'=>$command, 'action'=>$action, 'channelNo'=>"1", 'speed'=>$speed, 'uuid'=>uniqid(), 'serial'=>$serial);
            $headers=array('sessionId:'.$this->_sessionId, 'clientType:1');
            $response_json = $this->QueryAPIPut($this->DEVICES_URL.$serial.$this->API_ENDPOINT_PTZCONTROL, $data, $timeout=$this->_timeout, $headers);
        }
        catch (Exception $e) 
        {
            log::add('jeezviz', 'debug', "Could not access Ezviz' API: ".$e);
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login(true);
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                return $this->ptzControl($max_retries+1);
            }
        }

    }
    function get_PAGE_LIST($max_retries=0)
    {
        return $this->_get_pagelist($filter='CLOUD,TIME_PLAN,CONNECTION,SWITCH,STATUS,WIFI,STATUS_EXT,NODISTURB,P2P,TTS,KMS,HIDDNS', $json_key=null);
    }

    function get_DEVICE($max_retries=0)
    {
        return $this->_get_pagelist($filter='CLOUD',$json_key='deviceInfos');
    }

    function get_CONNECTION($max_retries=0)
    {
        return $this->_get_pagelist($filter='CONNECTION',$json_key='connectionInfos');
    }

    function get_STATUS($max_retries=0)
    {
        return $this->_get_pagelist($filter='STATUS',$json_key='statusInfos');
    }

    function get_SWITCH($max_retries=0)
    {
        return $this->_get_pagelist($filter='SWITCH',$json_key='switchStatusInfos');
    }

    function get_WIFI($max_retries=0)
    {
        return $this->_get_pagelist($filter='WIFI',$json_key='wifiInfos');
    }

    function get_NODISTURB($max_retries=0)
    {
        return $this->_get_pagelist($filter='NODISTURB',$json_key='alarmNodisturbInfos');
    }

    function get_P2P($max_retries=0)
    {
        return $this->_get_pagelist($filter='P2P',$json_key='p2pInfos');
    }

    function get_KMS($max_retries=0)
    {
        return $this->_get_pagelist($filter='KMS',$json_key='kmsInfos');
    }

    function get_TIME_PLAN($max_retries=0)
    {
        return $this->_get_pagelist($filter='TIME_PLAN',$json_key='timePlanInfos');
    }

    function switch_devices_privacy($enable=0)
    {
        #"""Switch status on all devices."""
        return $this->_switch_devices_privacy($enable);
    }

    function switch_status($serial, $status_type, $enable=0)
    {
        #"""Switch status of a device."""
        return $this->_switch_status($serial, $status_type, $enable);
    }
    function login($force=false)
    {        
        return $this->_login($force);
    }

}
