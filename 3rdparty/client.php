<?php
require_once __DIR__."/userAgent.php";
require_once __DIR__."/camera.php";

class EzvizClient
{
    public $account;
    public $password;
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
                #echo " - Aucune erreur\r\n";
            break;
            case JSON_ERROR_DEPTH:
                echo " - Profondeur maximale atteinte\r\n";
            break;
            case JSON_ERROR_STATE_MISMATCH:
                echo " - Inadéquation des modes ou underflow\r\n";
            break;
            case JSON_ERROR_CTRL_CHAR:
                echo " - Erreur lors du contrôle des caractères\r\n";
            break;
            case JSON_ERROR_SYNTAX:
                echo " - Erreur de syntaxe ; JSON malformé\r\n";
            break;
            case JSON_ERROR_UTF8:
                echo " - Caractères UTF-8 malformés, probablement une erreur d\'encodage\r\n";
            break;
            default:
                echo " - Erreur inconnue\r\n";
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
                echo "La rotation PTZ de l'équipement atteint la limite supérieure\r\n";
                break;
            case 2009:
                echo "Le réseau de l'appareil est anormal, veuillez vérifier le réseau de l'appareil ou réessayer\r\n";
                break;
            case 1310731:
                echo "L'appareil est dans un état de protection de la vie privée (fermez l'objectif, puis allez faire fonctionner le PTZ)\r\n";
                break;
            case -6:
                echo "Erreur de paramètre de demande\r\n";
                break;
            case 0:
                #echo "Operation completed\r\n";
                break;
            case 200:
                #echo "Operation completed\r\n";
                break;
            case 400:
                echo "Password cannot be empty; login account cannot be empty\r\n";
                break;
            case 405:
                echo "The method in the client request is forbidden\r\n";
            case 1001:
                echo "Invalid user name\r\n";
                break;
            case 1002:
                echo "The user name is occupied\r\n";
                break;
            case 1003:
                echo "Invalid password\r\n";
                break;
            case 1004:
                echo "Duplicated password\r\n";
                break;
            case 1005:
                echo "No more incorrect password attempts are allowed\r\n";
                break;
            case 1006:
                echo "The phone number is registered\r\n";
                break;
            case 1007:
                echo "Unregistered phone number\r\n";
                break;
            case 1008:
                echo "Invalid phone number\r\n";
                break;
            case 1009:
                echo "The user name and phone does not match\r\n";
                break;
            case 1010:
                echo "Getting verification code failed\r\n";
                break;
            case 1011:
                echo "Incorrect verification code\r\n";
                break;
            case 1012:
                echo "Invalid verification code\r\n";
                break;
            case 1013:
                echo "The user does not exist\r\n";
                break;
            case 1014:
                echo "Incorrect password or appKey\r\n";
                break;
            case 1015:
                echo "The user is locked\r\n";
                break;
            case 1021:
                echo "Verification parameters exception\r\n";
                break;
            case 1026:
                echo "The email is registered\r\n";
                break;
            case 1031:
                echo "Unregistered email\r\n";
                break;
            case 1032:
                echo "Invalid email\r\n";
                break;
            case 1041:
                echo "No more attempts are allowed to get verification code\r\n";
                break;
            case 1043:
                echo "No more incorrect verification code attempts are allowed\r\n";
                break;
            case 2000:
                echo "The device does not exist\r\n";
                break;
            case 2001:
                echo "The camera does not existThe camera is not registered to Ezviz Cloud. Check the camera network configuration\r\n";
                break;
            case 2003:
                echo "The device is offlineRefer to Service Center Trouble Shooting Method\r\n";
                break;
            case 2004:
                echo "Device exception\r\n";
                break;
            case 2007:
                echo "Incorrect device serial No.\r\n";
                break;
            case 2009:
                echo "The device request timeout\r\n";
                break;
            case 2030:
                echo "The device does not support Ezviz CloudCheck whether the device support Ezviz Cloud. You can also contact our supports: 4007005998\r\n";
                break;
            case 5000:
                echo "The device is added by yourself\r\n";
                break;
            case 5001:
                echo "The device is added by others\r\n";
                break;
            case 5002:
                echo "Incorrect device verification code\r\n";
                break;
            case 7001:
                echo "The invitation does not exist\r\n";
                break;
            case 7002:
                echo "Verifying the invitation failed\r\n";
                break;
            case 7003:
                echo "The invited user does not match\r\n";
                break;
            case 7004:
                echo "Canceling invitation failed\r\n";
                break;
            case 7005:
                echo "Deleting invitation failed\r\n";
                break;
            case 7006:
                echo "You cannot invite yourself\r\n";
                break;
            case 7007:
                echo "Duplicated invitationYou should call the interface for sharing or deleting the sharing. Troubleshooting: Clear all the sharing data in Ezviz Client and add the device again by calling related interface\r\n";
                break;
            case 10001:
                echo "Parameters errorParameter is empty or the format is incorrect\r\n";
                break;
            case 10002:
                echo "accessToken exception or expiredThe accessToken is valid for seven days. It is recommended that you can get the accessToken when the accessToken will be expired or Error Code 10002 appears\r\n";
                break;
            case 10004:
                echo "The user does not exist\r\n";
                break;
            case 10005:
                echo "appKey exceptionReturn the error code when appKey is incorrect or appKey status is frozen\r\n";
                break;
            case 10006:
                echo "The IP is limited\r\n";
                break;
            case 10007:
                echo "No more calling attempts are allowed\r\n";
                break;
            case 10008:
                echo "Signature error① For getting the signature type, refer to apidemo and Manual of the Last Version ② The encoding format is UTF-8.\r\n";
                break;
            case 10009:
                echo "Signature parameters error\r\n";
                break;
            case 10010:
                echo "Signature timeoutSynchronize the time by calling the Interface of Synchronizing Server Time .\r\n";
                break;
            case 10011:
                echo "Cloud P2P service is not allowedRefer to Binding Procedure\r\n";
                break;
            case 10012:
                echo "The third-party account is bound with the Ezviz account\r\n";
                break;
            case 10013:
                echo "The APP has no permission to call this interface\r\n";
                break;
            case 10014:
                echo "The APPKEY corresponding third-party userId is not bound with the phoneThe appKey for getting AccessToken is different from the one set in SDK\r\n";
                break;
            case 10017:
                echo "appKey does not existFill in the App key applied in the official website\r\n";
                break;
            case 10018:
                echo "AccessToken does not match with AppkeyCheck whether the appKey for getting AccessToken is the same with the one set in SDK.\r\n";
                break;
            case 10019:
                echo "Password error\r\n";
                break;
            case 10020:
                echo "The requesting method is required\r\n";
                break;
            case 10029:
                echo "The call frequency exceeds the upper-limit\r\n";
                break;
            case 10030:
                echo "appKey and appSecret mismatch.\r\n";
                break;
            case 10031:
                echo "The sub-account or the EZVIZ user has no permission\r\n";
                break;
            case 10032:
                echo "Sub-account not exist\r\n";
                break;
            case 10034:
                echo "Sub-account name already exist\r\n";
                break;
            case 10035:
                echo "Getting sub-account AccessToken error\r\n";
                break;
            case 10036:
                echo "The sub-account is frozen.\r\n";
                break;
            case 20001:
                echo "The channel does not existCheck whether the camera is added again and the channel parameters are updated\r\n";
                break;
            case 20002:
                echo "The device does not exist①The device does not register to Ezviz. Check the network is connected. ②The device serial No. does not exist.\r\n";
                break;
            case 20003:
                echo "Parameters exception and you need to upgrade the SDK version\r\n";
                break;
            case 20004:
                echo "Parameters exception and you need to upgrade the SDK version\r\n";
                break;
            case 20005:
                echo "You need to perform SDK security authenticationSecurity authentication is deleted\r\n";
                break;
            case 20006:
                echo "Network exception\r\n";
                break;
            case 20007:
                echo "The device is offlineRefer to Service Center Check Method\r\n";
                break;
            case 20008:
                echo "The device response timeoutThe device response timeout. Check the network is connected and try again\r\n";
                break;
            case 20009:
                echo "The device cannot be added to child account\r\n";
                break;
            case 20010:
                echo "The device verification code errorThe verification code is on the device tag. It contains six upper-cases\r\n";
                break;
            case 20011:
                echo "Adding device failed.Check whether the network is connected.\r\n";
                break;
            case 20012:
                echo "Adding the device failed.\r\n";
                break;
            case 20013:
                echo "The device has been added by other users.\r\n";
                break;
            case 20014:
                echo "Incorrect device serial No..\r\n";
                break;
            case 20015:
                echo "The device does not support the function.\r\n";
                break;
            case 20016:
                echo "The current device is formatting.\r\n";
                break;
            case 20017:
                echo "The device has been added by yourself.\r\n";
                break;
            case 20018:
                echo "The user does not have this device.Check whether the device belongs to the user.\r\n";
                break;
            case 20019:
                echo "The device does not support cloud storage service.\r\n";
                break;
            case 20020:
                echo "The device is online and is added by yourself.\r\n";
                break;
            case 20021:
                echo "The device is online and is not added by the user.\r\n";
                break;
            case 20022:
                echo "The device is online and is added by other users.\r\n";
                break;
            case 20023:
                echo "The device is offline and is not added by the user.\r\n";
                break;
            case 20024:
                echo "The device is offline and is added by the user.\r\n";
                break;
            case 20025:
                echo "Duplicated sharing.Check whether the sharing exists in the account that added the device.\r\n";
                break;
            case 20026:
                echo "The video does not exist in Video Gallery.\r\n";
                break;
            case 20029:
                echo "The device is offline and is added by yourself.\r\n";
                break;
            case 20030:
                echo "The user does not have the video in this video gallery.\r\n";
                break;
            case 20031:
                echo "The terminal binding enabled, and failed to verify device code.Disable the terminal binding,refer to this procedure.\r\n";
                break;
            case 20032:
                echo "The channel does not exist for this user.\r\n";
                break;
            case 20033:
                echo "The video shared by yourself cannot be added to favorites.\r\n";
                break;
            case 20101:
                echo "Share the video to yourself.\r\n";
                break;
            case 20102:
                echo "No corresponding invitation information.\r\n";
                break;
            case 20103:
                echo "The friend already exists.\r\n";
                break;
            case 20104:
                echo "The friend does not exist.\r\n";
                break;
            case 20105:
                echo "The friend status error.\r\n";
                break;
            case 20106:
                echo "The corresponding group does not exist.\r\n";
                break;
            case 20107:
                echo "You cannot add yourself as friend.\r\n";
                break;
            case 20108:
                echo "The current user is not the friend of the added user.\r\n";
                break;
            case 20109:
                echo "The corresponding sharing does not exist.\r\n";
                break;
            case 20110:
                echo "The friend group does not belong to the current user.\r\n";
                break;
            case 20111:
                echo "The friend is not in the status of waiting verification.\r\n";
                break;
            case 20112:
                echo "Adding the user in application as friend failed.\r\n";
                break;
            case 20201:
                echo "Handling the alarm information failed.\r\n";
                break;
            case 20202:
                echo "Handling the leaved message failed.\r\n";
                break;
            case 20301:
                echo "The alarm message searched via UUID does not exist.\r\n";
                break;
            case 20302:
                echo "The picture searched via UUID does not exist.\r\n";
                break;
            case 20303:
                echo "The picture searched via FID does not exist.\r\n";
                break;
            case 30001:
                echo "The user doesn't exist\r\n";
                break;
            case 49999:
                echo "Data exception.\r\n";
                break;
            case 50000:
                echo "The server exception.\r\n";
                break;
            case 60000:
                echo "The device does not support PTZ control.\r\n";
                break;
            case 60001:
                echo "The user has no PTZ control permission.\r\n";
                break;
            case 60002:
                echo "The device PTZ has reached the top limit.\r\n";
                break;
            case 60003:
                echo "The device PTZ has reached the bottom limit.\r\n";
                break;
            case 60004:
                echo "The device PTZ has reached the left limit.\r\n";
                break;
            case 60005:
                echo "The device PTZ has reached the right limit.\r\n";
                break;
            case 60006:
                echo "PTZ control failed.\r\n";
                break;
            case 60007:
                echo "No more preset can be added.\r\n";
                break;
            case 60008:
                echo "The preset number of C6 has reached the limit. You cannot add more preset.\r\n";
                break;
            case 60009:
                echo "The preset is calling.\r\n";
                break;
            case 60010:
                echo "The preset is the current position.\r\n";
                break;
            case 60011:
                echo "The preset does not exist.\r\n";
                break;
            case 60012:
                echo "Unknown error.\r\n";
                break;
            case 60013:
                echo "The version is the latest one.\r\n";
                break;
            case 60014:
                echo "The device is upgrading.\r\n";
                break;
            case 60015:
                echo "The device is rebooting.\r\n";
                break;
            case 60016:
                echo "The encryption is disabled.\r\n";
                break;
            case 60017:
                echo "Capturing failed.\r\n";
                break;
            case 60018:
                echo "Upgrading device failed.\r\n";
                break;
            case 60019:
                echo "The encryption is enabled.\r\n";
                break;
            case 60020:
                echo "The command is not supported.Check whether the device support the command.\r\n";
                break;
            case 60021:
                echo "It is current arming/disarming status.\r\n";
                break;
            case 60022:
                echo "It is current status.It is current open or closed status.\r\n";
                break;
            case 60023:
                echo "Subscription failed.\r\n";
                break;
            case 60024:
                echo "Canceling subscription failed.\r\n";
                break;
            case 60025:
                echo "Setting people counting failed.\r\n";
                break;
            case 60026:
                echo "The device is in privacy mask status.\r\n";
                break;
            case 60027:
                echo "The device is mirroring.\r\n";
                break;
            case 60028:
                echo "The device is controlling PTZ.\r\n";
                break;
            case 60029:
                echo "The device is in two-way audio status.\r\n";
                break;
            case 60030:
                echo "No more incorrect card password attempts are allowed. Try again after 24 hours.\r\n";
                break;
            case 60031:
                echo "Card password information does not exist.\r\n";
                break;
            case 60032:
                echo "Incorrect card password status or the password is expired.\r\n";
                break;
            case 60033:
                echo "The card password is not for sale. You can only buy the corresponding device.\r\n";
                break;
            case 60035:
                echo "Buying cloud storage server failed.\r\n";
                break;
            case 60040:
                echo "The added devices are not in the same LAN with the parent device.\r\n";
                break;
            case 60041:
                echo "The added devices are not in the same LAN with the parent device.\r\n";
                break;
            case 60042:
                echo "Incorrect password for added device.\r\n";
                break;
            case 60043:
                echo "No more devices can be added.\r\n";
                break;
            case 60044:
                echo "Network connection for the added device timeout.\r\n";
                break;
            case 60045:
                echo "The added device IP conflicts with the one of other channel.\r\n";
                break;
            case 60046:
                echo "The added device IP conflicts with the one of parent device.\r\n";
                break;
            case 60047:
                echo "The stream type is not supported.\r\n";
                break;
            case 60048:
                echo "The bandwidth exceeds the system accessing bandwidth.\r\n";
                break;
            case 60049:
                echo "Invalid IP or port.\r\n";
                break;
            case 60050:
                echo "The added device is not supported. You should upgrade the device.\r\n";
                break;
            case 60051:
                echo "The added device is not supported.\r\n";
                break;
            case 60052:
                echo "Incorrect channel No. for added device.\r\n";
                break;
            case 60053:
                echo "The resolution of added device is not supported.\r\n";
                break;
            case 60054:
                echo "The account for added device is locked.\r\n";
                break;
            case 60055:
                echo "Getting stream for the added device error.\r\n";
                break;
            case 60056:
                echo "Deleting device failed.\r\n";
                break;
            case 60057:
                echo "The deleted device has no linkage.Check whether there's linkage between IPC and NVR.\r\n";
                break;
            case 60060:
                echo "The device does not bind.\r\n";
                break;
            default:
                echo "Error code ".$code." not found\r\n";
                var_dump($response_json);
        }
    }

    function QueryAPIPost($URL, $postData=null, $timeout=DEFAULT_TIMEOUT, $headers=null)
    {
        try {
            // Setup cURL
            #echo $URL."\r\n";
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            #var_dump($postData);
            #var_dump($headers);
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
            #var_dump($response);
            // Check for errors
            if ($response === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            // Decode the response
            $response_json = json_decode($response, TRUE);    
            var_dump($response_json);
            $this->get_JsonLastError();
            $this->get_EZVIZ_Result_Message($response_json);           
            return $response_json;
			//return true;
        } catch(Exception $e) {
            echo $e;
            trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        }
    }
    function QueryAPIGet($URL, $postData, $timeout=DEFAULT_TIMEOUT, $headers)
    {
        try {
            // Setup cURL
            #echo $URL."\r\n";
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            #var_dump($postData);
            #var_dump($headers);

            $postData1 = http_build_query($postData);
            
            #CURLOPT_URL => $URL."?".$postData1,
            $uri=$URL."?".$postData1;
            #echo $uri."\r\n";
            curl_setopt_array($ch, array(
                CURLOPT_URL=>$uri,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => $this->_UserAgent
            ));

            // Send the request
            $response = curl_exec($ch);
            #var_dump($response);
            // Check for errors
            if ($response === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            // Decode the response
            $response_json = json_decode($response, TRUE);    
            #var_dump($response_json);
            $this->get_JsonLastError();        
            $this->get_EZVIZ_Result_Message($response_json);
            return $response_json;
        } catch(Exception $e) {
            echo $e;
            trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        }
    }
    function QueryAPIPut($URL, $postData, $timeout=DEFAULT_TIMEOUT, $headers)
    {
        try {
            // Setup cURL
            #echo $URL."\r\n";
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            #var_dump($postData);
            #var_dump($headers);

            $postData1 = http_build_query($postData);
            
            #CURLOPT_URL => $URL."?".$postData1,
            $uri=$URL."?".$postData1;
            #echo $uri."\r\n";
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
            #var_dump($response);
            // Check for errors
            if ($response === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            // Decode the response
            $response_json = json_decode($response, TRUE);    
            #var_dump($response_json);
            $this->get_JsonLastError();        
            $this->get_EZVIZ_Result_Message($response_json);
            return $response_json;
        } catch(Exception $e) {
            echo $e;
            trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        }
    }
    function __construct($account, $password, $sessionId=null, $timeout=DEFAULT_TIMEOUT, $cloud=null, $connection=null)
    {
        #"""Initialize the client object."""
        $this->account = $account;
        $this->password = $password;
        $this->_sessionId = $sessionId;
        $this->_timeout = $timeout;
        $this->_CLOUD = $cloud;
        $this->_CONNECTION = $connection;
    }
        

    function _login($apiDomain="")
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
        echo "Login to Ezviz' API at ".$this->LOGIN_URL."\r\n";
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
            echo "Can not login to API\r\n";
        }
        
        # if the apidomain is not proper
        if ($response_json["meta"]["code"] == 1100)
        {
            return $this->_login($response_json["loginArea"]["apiDomain"]);
        }
        
        if ($response_json["meta"]["code"] != 200)
        {
			echo var_dump($response_json);
            return false;
        }
        # let's parse the answer, session is in {.."loginSession":{"sessionId":"xxx...}
        try
        {
            $sessionId = $response_json["loginSession"]["sessionId"];
            $this->_sessionId = $sessionId;
            echo "Login successfull sessionId =".$sessionId."\r\n";
        }
        catch (Exception $e)
        {          
            echo $e;
        }
        return True;
    }

    function _get_pagelist($filter=null, $json_key=null, $max_retries=0)
    {
        echo "Get data from pagelist API.\r\n";

        if ($max_retries > $this->MAX_RETRIES)
        {
            echo "Can't gather proper data. Max retries exceeded.";
        }

        if ($filter === null)
        {
            echo "Trying to call get_pagelist without filter";
        }
        $postData=array('filter'=>$filter);
        $headers=array('sessionId:'.$this->_sessionId);
        try
        {
            $response_json = $this->QueryAPIGet($this->PAGELIST_URL, $postData, $this->_timeout, $headers);
        }
        catch (Exception $e)
        {
            echo "Could not access Ezviz' API: " + $e;
        }
        if (array_key_exists("meta",$response_json))
        { 
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to relogin
                $this->login();
                echo "Got 401, relogging (max retries: ".$max_retries.")";
                return $this->_get_pagelist($max_retries+1);
            }
        }
        #var_dump($response_json);
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
            echo "Impossible to load the devices, here is the returned response: ".$response_json;
        }
        
        #var_dump($json_result);
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
                    $this->login();
                    echo "Got 401, relogging (max retries: $max_retries)";
                    return $this->_switch_status($serial, $type, $enable, $max_retries+1);
                }
            }
        }            
        catch (Exception $e)
        {
            echo "Could not access Ezviz' API: ".$e;
        }
        return True;
    }
       

    function _switch_devices_privacy($enable=0)
    {
        echo "Switch privacy status on ALL devices (batch)\r\n";

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
        echo "Enable alarm notifications.\r\n";
        if ($max_retries > $this->MAX_RETRIES)
        {
            echo "Can't gather proper data. Max retries exceeded.";
        }
            

        if (!in_array($sensibility,array(0,1,2,3,4,5,6)))
        {
            echo "Unproper sensibility (should be within 1 to 6).";
        }
            

        try
        {
            $headers=array('sessionId:'.$this->_sessionId);
            $data=array('subSerial'=>$serial, 'type'=>'0', 'sessionId'=>$this->_sessionId, 'value'=>$sensibility);
            $response_json = $this->QueryAPIPost($this->DETECTION_SENSIBILITY_URL, $data, $timeout=$this->_timeout,$headers);
        }
        catch (Exception $e)
        {
            echo "Could not access Ezviz' API: ".$e;
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login();
                echo "Got 401, relogging (max retries: $max_retries)";
                return $this->detection_sensibility($serial, $enable, $max_retries+1);
            }
        }
        return True;
    }

    function get_detection_sensibility($serial, $max_retries=0)
    {
        echo "Get detection sensibility.\r\n";
        if ($max_retries > $this->MAX_RETRIES)
        {
            echo "Can't gather proper data. Max retries exceeded.";
        }

        try
        {
            $headers=array('sessionId:'.$this->_sessionId);
            $data=array('subSerial'=>$serial, 'sessionId'=>$this->_sessionId, 'clientType'=>1);
            $response_json = $this->QueryAPIPost($this->DETECTION_SENSIBILITY_GET_URL, $data, $timeout=$this->_timeout,$headers);
        }
        catch (Exception $e)
        {
            echo "Could not access Ezviz' API: ".$e;
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login();
                echo "Got 401, relogging (max retries: $max_retries)";
                return $this->get_detection_sensibility($serial, $enable, $max_retries+1);
            }
        }
        
        if ($response_json['resultCode'] != '0')
        {
            # raise echo "Could not get detection sensibility: Got %s : %s)",str(req.status_code), str(req.text))
            return 'Unknown';
        }
        else
        {
            return $response_json['algorithmConfig']['algorithmList'][0]['value'];
        }
    }

    function alarm_sound($serial, $soundType, $enable=1, $max_retries=0)
    {
        echo "Enable alarm sound by API.\r\n";
        if ($max_retries > $this->MAX_RETRIES)
        {
            echo "Can't gather proper data. Max retries exceeded.";
        }

        if (!in_array( array(0,1,2),$soundType))
        {
            echo "Invalid soundType, should be 0,1,2: ".$soundType;
        }
        
        try
        {
            $data=array('enable'=>$enable, 'soundType'=>$soundType, 'voiceId'=>'0', 'deviceSerial'=>$serial);
            $headers=array('sessionId:'.$this->_sessionId);
            $response_json = $this->QueryAPIPut($DEVICES_URL + $serial + $this->API_ENDPOINT_ALARM_SOUND, $data, $timeout=$this->_timeout, $headers);
        }
        catch (Exception $e)
        {
            echo "Could not access Ezviz' API: ".$e;
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login();
                echo "Got 401, relogging (max retries: $max_retries)";
                return $this->alarm_sound($serial, $enable, $soundType, $max_retries+1);
            }   
        }    
        return True;
    }


    function data_report($serial, $enable=1, $max_retries=0)
    {
        echo "Enable alarm notifications.\r\n";
        if ($max_retries > $this->MAX_RETRIES)
        {
            echo "Can't gather proper data. Max retries exceeded.";
        }            

        # operationType = 2 if disable, and 1 if enable
        $operationType = 2 - int($enable);
        echo "enable: {".$enable."}, operationType: {".$operationType."}";
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
            echo "Could not access Ezviz' API: ".$e;
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login();
                echo "Got 401, relogging (max retries: $max_retries)";
                return $this->data_report($serial, $enable, $max_retries+1);
            }
        }
        return True;
    }
    function ptzControl($command, $serial, $action, $speed=5, $max_retries=0)
    {
        echo "PTZ Control by API.\r\n";
        if ($max_retries > $this->MAX_RETRIES)
        {
            echo "Can't gather proper data. Max retries exceeded.";            
        }

        if ($command === null)
        {
            echo "Trying to call ptzControl without command";
        }
        if ($action === null)
        {
            echo "Trying to call ptzControl without action";
        }

        try
        {
            $data=array('command'=>$command, 'action'=>$action, 'channelNo'=>"1", 'speed'=>$speed, 'uuid'=>uniqid(), 'serial'=>$serial);
            $headers=array('sessionId:'.$this->_sessionId, 'clientType:1');
            $response_json = $this->QueryAPIPut($this->DEVICES_URL.$serial.$this->API_ENDPOINT_PTZCONTROL, $data, $timeout=$this->_timeout, $headers);
        }
        catch (Exception $e) 
        {
            echo "Could not access Ezviz' API: ".$e;
        }
        if (array_key_exists("meta",$response_json))
        {
            if ($response_json["meta"]["code"] == 401)
            {
                # session is wrong, need to re-log-in
                $this->login();
                echo "Got 401, relogging (max retries: $max_retries)";
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
    function login()
    {
        #"""Set http session."""
        if ($this->_sessionId === null)
        {
            # setting fake user-agent header
            $this->_UserAgent = (new userAgent) ->generate(); 
        }       
        return $this->_login("");
    }

}
