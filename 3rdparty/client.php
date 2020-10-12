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
                log::add('jeezviz', 'debug', "La rotation PTZ de l'équipement atteint la limite supérieure\r\n");
                break;
            case 2009:
                log::add('jeezviz', 'debug', "Le réseau de l'appareil est anormal, veuillez vérifier le réseau de l'appareil ou réessayer\r\n");
                break;
            case 1310731:
                log::add('jeezviz', 'debug', "L'appareil est dans un état de protection de la vie privée (fermez l'objectif, puis allez faire fonctionner le PTZ)\r\n");
                break;
            case -6:
                log::add('jeezviz', 'debug', "Erreur de paramètre de demande\r\n");
                break;
            case 0:
                #log::add('jeezviz', 'debug', "Operation completed\r\n");
                break;
            case 200:
                #log::add('jeezviz', 'debug', "Operation completed\r\n");
                break;
            case 400:
                log::add('jeezviz', 'debug', "Password cannot be empty; login account cannot be empty\r\n");
                break;
            case 405:
                log::add('jeezviz', 'debug', "The method in the client request is forbidden\r\n");
            case 1001:
                log::add('jeezviz', 'debug', "Invalid user name\r\n");
                break;
            case 1002:
                log::add('jeezviz', 'debug', "The user name is occupied\r\n");
                break;
            case 1003:
                log::add('jeezviz', 'debug', "Invalid password\r\n");
                break;
            case 1004:
                log::add('jeezviz', 'debug', "Duplicated password\r\n");
                break;
            case 1005:
                log::add('jeezviz', 'debug', "No more incorrect password attempts are allowed\r\n");
                break;
            case 1006:
                log::add('jeezviz', 'debug', "The phone number is registered\r\n");
                break;
            case 1007:
                log::add('jeezviz', 'debug', "Unregistered phone number\r\n");
                break;
            case 1008:
                log::add('jeezviz', 'debug', "Invalid phone number\r\n");
                break;
            case 1009:
                log::add('jeezviz', 'debug', "The user name and phone does not match\r\n");
                break;
            case 1010:
                log::add('jeezviz', 'debug', "Getting verification code failed\r\n");
                break;
            case 1011:
                log::add('jeezviz', 'debug', "Incorrect verification code\r\n");
                break;
            case 1012:
                log::add('jeezviz', 'debug', "Invalid verification code\r\n");
                break;
            case 1013:
                log::add('jeezviz', 'debug', "The user does not exist\r\n");
                break;
            case 1014:
                log::add('jeezviz', 'debug', "Incorrect password or appKey\r\n");
                break;
            case 1015:
                log::add('jeezviz', 'debug', "The user is locked\r\n");
                break;
            case 1021:
                log::add('jeezviz', 'debug', "Verification parameters exception\r\n");
                break;
            case 1026:
                log::add('jeezviz', 'debug', "The email is registered\r\n");
                break;
            case 1031:
                log::add('jeezviz', 'debug', "Unregistered email\r\n");
                break;
            case 1032:
                log::add('jeezviz', 'debug', "Invalid email\r\n");
                break;
            case 1041:
                log::add('jeezviz', 'debug', "No more attempts are allowed to get verification code\r\n");
                break;
            case 1043:
                log::add('jeezviz', 'debug', "No more incorrect verification code attempts are allowed\r\n");
                break;
            case 2000:
                log::add('jeezviz', 'debug', "The device does not exist\r\n");
                break;
            case 2001:
                log::add('jeezviz', 'debug', "The camera does not existThe camera is not registered to Ezviz Cloud. Check the camera network configuration\r\n");
                break;
            case 2003:
                log::add('jeezviz', 'debug', "The device is offlineRefer to Service Center Trouble Shooting Method\r\n");
                break;
            case 2004:
                log::add('jeezviz', 'debug', "Device exception\r\n");
                break;
            case 2007:
                log::add('jeezviz', 'debug', "Incorrect device serial No.\r\n");
                break;
            case 2009:
                log::add('jeezviz', 'debug', "The device request timeout\r\n");
                break;
            case 2030:
                log::add('jeezviz', 'debug', "The device does not support Ezviz CloudCheck whether the device support Ezviz Cloud. You can also contact our supports: 4007005998\r\n");
                break;
            case 5000:
                log::add('jeezviz', 'debug', "The device is added by yourself\r\n");
                break;
            case 5001:
                log::add('jeezviz', 'debug', "The device is added by others\r\n");
                break;
            case 5002:
                log::add('jeezviz', 'debug', "Incorrect device verification code\r\n");
                break;
            case 7001:
                log::add('jeezviz', 'debug', "The invitation does not exist\r\n");
                break;
            case 7002:
                log::add('jeezviz', 'debug', "Verifying the invitation failed\r\n");
                break;
            case 7003:
                log::add('jeezviz', 'debug', "The invited user does not match\r\n");
                break;
            case 7004:
                log::add('jeezviz', 'debug', "Canceling invitation failed\r\n");
                break;
            case 7005:
                log::add('jeezviz', 'debug', "Deleting invitation failed\r\n");
                break;
            case 7006:
                log::add('jeezviz', 'debug', "You cannot invite yourself\r\n");
                break;
            case 7007:
                log::add('jeezviz', 'debug', "Duplicated invitationYou should call the interface for sharing or deleting the sharing. Troubleshooting: Clear all the sharing data in Ezviz Client and add the device again by calling related interface\r\n");
                break;
            case 10001:
                log::add('jeezviz', 'debug', "Parameters errorParameter is empty or the format is incorrect\r\n");
                break;
            case 10002:
                log::add('jeezviz', 'debug', "accessToken exception or expiredThe accessToken is valid for seven days. It is recommended that you can get the accessToken when the accessToken will be expired or Error Code 10002 appears\r\n");
                break;
            case 10004:
                log::add('jeezviz', 'debug', "The user does not exist\r\n");
                break;
            case 10005:
                log::add('jeezviz', 'debug', "appKey exceptionReturn the error code when appKey is incorrect or appKey status is frozen\r\n");
                break;
            case 10006:
                log::add('jeezviz', 'debug', "The IP is limited\r\n");
                break;
            case 10007:
                log::add('jeezviz', 'debug', "No more calling attempts are allowed\r\n");
                break;
            case 10008:
                log::add('jeezviz', 'debug', "Signature error① For getting the signature type, refer to apidemo and Manual of the Last Version ② The encoding format is UTF-8.\r\n");
                break;
            case 10009:
                log::add('jeezviz', 'debug', "Signature parameters error\r\n");
                break;
            case 10010:
                log::add('jeezviz', 'debug', "Signature timeoutSynchronize the time by calling the Interface of Synchronizing Server Time .\r\n");
                break;
            case 10011:
                log::add('jeezviz', 'debug', "Cloud P2P service is not allowedRefer to Binding Procedure\r\n");
                break;
            case 10012:
                log::add('jeezviz', 'debug', "The third-party account is bound with the Ezviz account\r\n");
                break;
            case 10013:
                log::add('jeezviz', 'debug', "The APP has no permission to call this interface\r\n");
                break;
            case 10014:
                log::add('jeezviz', 'debug', "The APPKEY corresponding third-party userId is not bound with the phoneThe appKey for getting AccessToken is different from the one set in SDK\r\n");
                break;
            case 10017:
                log::add('jeezviz', 'debug', "appKey does not existFill in the App key applied in the official website\r\n");
                break;
            case 10018:
                log::add('jeezviz', 'debug', "AccessToken does not match with AppkeyCheck whether the appKey for getting AccessToken is the same with the one set in SDK.\r\n");
                break;
            case 10019:
                log::add('jeezviz', 'debug', "Password error\r\n");
                break;
            case 10020:
                log::add('jeezviz', 'debug', "The requesting method is required\r\n");
                break;
            case 10029:
                log::add('jeezviz', 'debug', "The call frequency exceeds the upper-limit\r\n");
                break;
            case 10030:
                log::add('jeezviz', 'debug', "appKey and appSecret mismatch.\r\n");
                break;
            case 10031:
                log::add('jeezviz', 'debug', "The sub-account or the EZVIZ user has no permission\r\n");
                break;
            case 10032:
                log::add('jeezviz', 'debug', "Sub-account not exist\r\n");
                break;
            case 10034:
                log::add('jeezviz', 'debug', "Sub-account name already exist\r\n");
                break;
            case 10035:
                log::add('jeezviz', 'debug', "Getting sub-account AccessToken error\r\n");
                break;
            case 10036:
                log::add('jeezviz', 'debug', "The sub-account is frozen.\r\n");
                break;
            case 20001:
                log::add('jeezviz', 'debug', "The channel does not existCheck whether the camera is added again and the channel parameters are updated\r\n");
                break;
            case 20002:
                log::add('jeezviz', 'debug', "The device does not exist①The device does not register to Ezviz. Check the network is connected. ②The device serial No. does not exist.\r\n");
                break;
            case 20003:
                log::add('jeezviz', 'debug', "Parameters exception and you need to upgrade the SDK version\r\n");
                break;
            case 20004:
                log::add('jeezviz', 'debug', "Parameters exception and you need to upgrade the SDK version\r\n");
                break;
            case 20005:
                log::add('jeezviz', 'debug', "You need to perform SDK security authenticationSecurity authentication is deleted\r\n");
                break;
            case 20006:
                log::add('jeezviz', 'debug', "Network exception\r\n");
                break;
            case 20007:
                log::add('jeezviz', 'debug', "The device is offlineRefer to Service Center Check Method\r\n");
                break;
            case 20008:
                log::add('jeezviz', 'debug', "The device response timeoutThe device response timeout. Check the network is connected and try again\r\n");
                break;
            case 20009:
                log::add('jeezviz', 'debug', "The device cannot be added to child account\r\n");
                break;
            case 20010:
                log::add('jeezviz', 'debug', "The device verification code errorThe verification code is on the device tag. It contains six upper-cases\r\n");
                break;
            case 20011:
                log::add('jeezviz', 'debug', "Adding device failed.Check whether the network is connected.\r\n");
                break;
            case 20012:
                log::add('jeezviz', 'debug', "Adding the device failed.\r\n");
                break;
            case 20013:
                log::add('jeezviz', 'debug', "The device has been added by other users.\r\n");
                break;
            case 20014:
                log::add('jeezviz', 'debug', "Incorrect device serial No..\r\n");
                break;
            case 20015:
                log::add('jeezviz', 'debug', "The device does not support the function.\r\n");
                break;
            case 20016:
                log::add('jeezviz', 'debug', "The current device is formatting.\r\n");
                break;
            case 20017:
                log::add('jeezviz', 'debug', "The device has been added by yourself.\r\n");
                break;
            case 20018:
                log::add('jeezviz', 'debug', "The user does not have this device.Check whether the device belongs to the user.\r\n");
                break;
            case 20019:
                log::add('jeezviz', 'debug', "The device does not support cloud storage service.\r\n");
                break;
            case 20020:
                log::add('jeezviz', 'debug', "The device is online and is added by yourself.\r\n");
                break;
            case 20021:
                log::add('jeezviz', 'debug', "The device is online and is not added by the user.\r\n");
                break;
            case 20022:
                log::add('jeezviz', 'debug', "The device is online and is added by other users.\r\n");
                break;
            case 20023:
                log::add('jeezviz', 'debug', "The device is offline and is not added by the user.\r\n");
                break;
            case 20024:
                log::add('jeezviz', 'debug', "The device is offline and is added by the user.\r\n");
                break;
            case 20025:
                log::add('jeezviz', 'debug', "Duplicated sharing.Check whether the sharing exists in the account that added the device.\r\n");
                break;
            case 20026:
                log::add('jeezviz', 'debug', "The video does not exist in Video Gallery.\r\n");
                break;
            case 20029:
                log::add('jeezviz', 'debug', "The device is offline and is added by yourself.\r\n");
                break;
            case 20030:
                log::add('jeezviz', 'debug', "The user does not have the video in this video gallery.\r\n");
                break;
            case 20031:
                log::add('jeezviz', 'debug', "The terminal binding enabled, and failed to verify device code.Disable the terminal binding,refer to this procedure.\r\n");
                break;
            case 20032:
                log::add('jeezviz', 'debug', "The channel does not exist for this user.\r\n");
                break;
            case 20033:
                log::add('jeezviz', 'debug', "The video shared by yourself cannot be added to favorites.\r\n");
                break;
            case 20101:
                log::add('jeezviz', 'debug', "Share the video to yourself.\r\n");
                break;
            case 20102:
                log::add('jeezviz', 'debug', "No corresponding invitation information.\r\n");
                break;
            case 20103:
                log::add('jeezviz', 'debug', "The friend already exists.\r\n");
                break;
            case 20104:
                log::add('jeezviz', 'debug', "The friend does not exist.\r\n");
                break;
            case 20105:
                log::add('jeezviz', 'debug', "The friend status error.\r\n");
                break;
            case 20106:
                log::add('jeezviz', 'debug', "The corresponding group does not exist.\r\n");
                break;
            case 20107:
                log::add('jeezviz', 'debug', "You cannot add yourself as friend.\r\n");
                break;
            case 20108:
                log::add('jeezviz', 'debug', "The current user is not the friend of the added user.\r\n");
                break;
            case 20109:
                log::add('jeezviz', 'debug', "The corresponding sharing does not exist.\r\n");
                break;
            case 20110:
                log::add('jeezviz', 'debug', "The friend group does not belong to the current user.\r\n");
                break;
            case 20111:
                log::add('jeezviz', 'debug', "The friend is not in the status of waiting verification.\r\n");
                break;
            case 20112:
                log::add('jeezviz', 'debug', "Adding the user in application as friend failed.\r\n");
                break;
            case 20201:
                log::add('jeezviz', 'debug', "Handling the alarm information failed.\r\n");
                break;
            case 20202:
                log::add('jeezviz', 'debug', "Handling the leaved message failed.\r\n");
                break;
            case 20301:
                log::add('jeezviz', 'debug', "The alarm message searched via UUID does not exist.\r\n");
                break;
            case 20302:
                log::add('jeezviz', 'debug', "The picture searched via UUID does not exist.\r\n");
                break;
            case 20303:
                log::add('jeezviz', 'debug', "The picture searched via FID does not exist.\r\n");
                break;
            case 30001:
                log::add('jeezviz', 'debug', "The user doesn't exist\r\n");
                break;
            case 49999:
                log::add('jeezviz', 'debug', "Data exception.\r\n");
                break;
            case 50000:
                log::add('jeezviz', 'debug', "The server exception.\r\n");
                break;
            case 60000:
                log::add('jeezviz', 'debug', "The device does not support PTZ control.\r\n");
                break;
            case 60001:
                log::add('jeezviz', 'debug', "The user has no PTZ control permission.\r\n");
                break;
            case 60002:
                log::add('jeezviz', 'debug', "The device PTZ has reached the top limit.\r\n");
                break;
            case 60003:
                log::add('jeezviz', 'debug', "The device PTZ has reached the bottom limit.\r\n");
                break;
            case 60004:
                log::add('jeezviz', 'debug', "The device PTZ has reached the left limit.\r\n");
                break;
            case 60005:
                log::add('jeezviz', 'debug', "The device PTZ has reached the right limit.\r\n");
                break;
            case 60006:
                log::add('jeezviz', 'debug', "PTZ control failed.\r\n");
                break;
            case 60007:
                log::add('jeezviz', 'debug', "No more preset can be added.\r\n");
                break;
            case 60008:
                log::add('jeezviz', 'debug', "The preset number of C6 has reached the limit. You cannot add more preset.\r\n");
                break;
            case 60009:
                log::add('jeezviz', 'debug', "The preset is calling.\r\n");
                break;
            case 60010:
                log::add('jeezviz', 'debug', "The preset is the current position.\r\n");
                break;
            case 60011:
                log::add('jeezviz', 'debug', "The preset does not exist.\r\n");
                break;
            case 60012:
                log::add('jeezviz', 'debug', "Unknown error.\r\n");
                break;
            case 60013:
                log::add('jeezviz', 'debug', "The version is the latest one.\r\n");
                break;
            case 60014:
                log::add('jeezviz', 'debug', "The device is upgrading.\r\n");
                break;
            case 60015:
                log::add('jeezviz', 'debug', "The device is rebooting.\r\n");
                break;
            case 60016:
                log::add('jeezviz', 'debug', "The encryption is disabled.\r\n");
                break;
            case 60017:
                log::add('jeezviz', 'debug', "Capturing failed.\r\n");
                break;
            case 60018:
                log::add('jeezviz', 'debug', "Upgrading device failed.\r\n");
                break;
            case 60019:
                log::add('jeezviz', 'debug', "The encryption is enabled.\r\n");
                break;
            case 60020:
                log::add('jeezviz', 'debug', "The command is not supported.Check whether the device support the command.\r\n");
                break;
            case 60021:
                log::add('jeezviz', 'debug', "It is current arming/disarming status.\r\n");
                break;
            case 60022:
                log::add('jeezviz', 'debug', "It is current status.It is current open or closed status.\r\n");
                break;
            case 60023:
                log::add('jeezviz', 'debug', "Subscription failed.\r\n");
                break;
            case 60024:
                log::add('jeezviz', 'debug', "Canceling subscription failed.\r\n");
                break;
            case 60025:
                log::add('jeezviz', 'debug', "Setting people counting failed.\r\n");
                break;
            case 60026:
                log::add('jeezviz', 'debug', "The device is in privacy mask status.\r\n");
                break;
            case 60027:
                log::add('jeezviz', 'debug', "The device is mirroring.\r\n");
                break;
            case 60028:
                log::add('jeezviz', 'debug', "The device is controlling PTZ.\r\n");
                break;
            case 60029:
                log::add('jeezviz', 'debug', "The device is in two-way audio status.\r\n");
                break;
            case 60030:
                log::add('jeezviz', 'debug', "No more incorrect card password attempts are allowed. Try again after 24 hours.\r\n");
                break;
            case 60031:
                log::add('jeezviz', 'debug', "Card password information does not exist.\r\n");
                break;
            case 60032:
                log::add('jeezviz', 'debug', "Incorrect card password status or the password is expired.\r\n");
                break;
            case 60033:
                log::add('jeezviz', 'debug', "The card password is not for sale. You can only buy the corresponding device.\r\n");
                break;
            case 60035:
                log::add('jeezviz', 'debug', "Buying cloud storage server failed.\r\n");
                break;
            case 60040:
                log::add('jeezviz', 'debug', "The added devices are not in the same LAN with the parent device.\r\n");
                break;
            case 60041:
                log::add('jeezviz', 'debug', "The added devices are not in the same LAN with the parent device.\r\n");
                break;
            case 60042:
                log::add('jeezviz', 'debug', "Incorrect password for added device.\r\n");
                break;
            case 60043:
                log::add('jeezviz', 'debug', "No more devices can be added.\r\n");
                break;
            case 60044:
                log::add('jeezviz', 'debug', "Network connection for the added device timeout.\r\n");
                break;
            case 60045:
                log::add('jeezviz', 'debug', "The added device IP conflicts with the one of other channel.\r\n");
                break;
            case 60046:
                log::add('jeezviz', 'debug', "The added device IP conflicts with the one of parent device.\r\n");
                break;
            case 60047:
                log::add('jeezviz', 'debug', "The stream type is not supported.\r\n");
                break;
            case 60048:
                log::add('jeezviz', 'debug', "The bandwidth exceeds the system accessing bandwidth.\r\n");
                break;
            case 60049:
                log::add('jeezviz', 'debug', "Invalid IP or port.\r\n");
                break;
            case 60050:
                log::add('jeezviz', 'debug', "The added device is not supported. You should upgrade the device.\r\n");
                break;
            case 60051:
                log::add('jeezviz', 'debug', "The added device is not supported.\r\n");
                break;
            case 60052:
                log::add('jeezviz', 'debug', "Incorrect channel No. for added device.\r\n");
                break;
            case 60053:
                log::add('jeezviz', 'debug', "The resolution of added device is not supported.\r\n");
                break;
            case 60054:
                log::add('jeezviz', 'debug', "The account for added device is locked.\r\n");
                break;
            case 60055:
                log::add('jeezviz', 'debug', "Getting stream for the added device error.\r\n");
                break;
            case 60056:
                log::add('jeezviz', 'debug', "Deleting device failed.\r\n");
                break;
            case 60057:
                log::add('jeezviz', 'debug', "The deleted device has no linkage.Check whether there's linkage between IPC and NVR.\r\n");
                break;
            case 60060:
                log::add('jeezviz', 'debug', "The device does not bind.\r\n");
                break;
            default:
                log::add('jeezviz', 'debug', "Error code ".$code." not found\r\n");
                var_dump($response_json);
        }
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
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            // Decode the response
            $response_json = json_decode($response, TRUE);    
            log::add('jeezviz', 'debug', var_dump($response_json));
            $this->get_JsonLastError();
            $this->get_EZVIZ_Result_Message($response_json);           
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
            $this->get_EZVIZ_Result_Message($response_json);
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
            $this->get_EZVIZ_Result_Message($response_json);
            return $response_json;
        } catch(Exception $e) {
            log::add('jeezviz', 'debug', $e);
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
                $this->login();
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
                    $this->login();
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
                $this->login();
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
                $this->login();
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                return $this->get_detection_sensibility($serial, $enable, $max_retries+1);
            }
        }
        
        if ($response_json['resultCode'] != '0')
        {
            log::add('jeezviz', 'debug', "Could not get detection sensibility: Got %s : %s)",str(req.status_code), str(req.text));
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
            $response_json = $this->QueryAPIPut($DEVICES_URL + $serial + $this->API_ENDPOINT_ALARM_SOUND, $data, $timeout=$this->_timeout, $headers);
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
                $this->login();
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                return $this->alarm_sound($serial, $enable, $soundType, $max_retries+1);
            }   
        }    
        return True;
    }


    function data_report($serial, $enable=1, $max_retries=0)
    {
        log::add('jeezviz', 'debug', "Enable alarm notifications.\r\n");
        if ($max_retries > $this->MAX_RETRIES)
        {
            log::add('jeezviz', 'debug', "Can't gather proper data. Max retries exceeded.");
        }            

        # operationType = 2 if disable, and 1 if enable
        $operationType = 2 - int($enable);
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
                $this->login();
                log::add('jeezviz', 'debug', "Got 401, relogging (max retries: $max_retries)");
                return $this->data_report($serial, $enable, $max_retries+1);
            }
        }
        return True;
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
                $this->login();
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
