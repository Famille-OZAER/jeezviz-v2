<?php

# seems to be some internal reference. 21 = sleep mode
#const TYPE_PRIVACY_MODE = 21;
const TYPE_PRIVACY_MODE = 7;
const TYPE_ALARM_NOTIFY = 7;
const TYPE_AUDIO = 22;
const TYPE_STATE_LED = 3;
const TYPE_IR_LED = 10;
const TYPE_FOLLOW_MOVE = 25;

const KEY_ALARM_NOTIFICATION = 'globalStatus';

const ALARM_SOUND_MODE = array(0=>'Software', 1=>'Intensive', 2=>'Disabled');

class EzvizCamera
{
    public $_serial;
    public $_client;
    public $_loaded;
    public $_device;
    function __construct($client, $serial)
    {
        log::add('jeezviz-v2', 'debug', "Initialize the camera object.\r\n");
        $this->_serial = $serial;
        $this->_client = $client;

        $this->_loaded = 0;

        # $this->load();
    }

        
    function load()
    {
        log::add('jeezviz-v2', 'debug', "Load object properties\r\n");
        $page_list = $this->_client->get_PAGE_LIST();

        # we need to know the index of this camera's $this->_serial  
        foreach ($page_list['deviceInfos'] as $device)
        {
            if ($device['deviceSerial'] == $this->_serial)
            {
                $this->_device = $device;
            }
            break;
        }           

        foreach ($page_list['cameraInfos'] as $camera)
        {
            if ($camera['deviceSerial'] == $this->_serial)
            {
                $this->_camera_infos = $camera;
                break;
            }
        }

        # global status
        $this->_status = $page_list['statusInfos'][$this->_serial];


        # load connection infos
        $this->_connection = $page_list['connectionInfos'][$this->_serial];

        # a bit of wifi infos
        #$this->_wifi = $page_list['wifiStatusInfos'][$this->_serial];

        # load switches
        $switches = array();
        foreach ($page_list['switchStatusInfos'][$this->_serial] as $switch)
        {
            $switches[$switch['type']] = $switch; 
        }

        $this->_switch = $switches;
        //log::add('jeezviz-v2', 'debug', var_dump($switches));
        # load detection sensibility
        $this->_detection_sensibility = $this->_client->get_detection_sensibility($this->_serial);

        # load camera object
        try
        {
            # $this->_switch = page_list['switchStatusInfos'][$this->_serial];
            $this->_time_plan = $page_list['timePlanInfos'][$this->_serial];
            $this->_nodisturb = $page_list['alarmNodisturbInfos'][$this->_serial];
            $this->_kms = $page_list['kmsInfos'][$this->_serial];
            $this->_hiddns = $page_list['hiddnsInfos'][$this->_serial];
            $this->_p2p = $page_list['p2pInfos'][$this->_serial];
        }
        catch (Exception $e)
        {
            log::add('jeezviz-v2', 'debug', $e);
        }
        $this->_loaded = 1;
        return $this;
    }


    function status()
    {
        log::add('jeezviz-v2', 'debug', "Return the status of the camera.\r\n");

        if (!$this->_loaded)
        {
            $this->load();
        }
        $status=array('serial'=>$this->_serial,
            'name'=>$this->_device['name'],
            'status'=>$this->_device['status'],
            'device_sub_category'=>$this->_device['deviceSubCategory'],
            'privacy'=>$this->_switch[TYPE_PRIVACY_MODE]['enable'],
            'audio'=>$this->_switch[TYPE_AUDIO]['enable'],
            'ir_led'=>$this->_switch[TYPE_IR_LED]['enable'],
            'state_led'=>$this->_switch[TYPE_STATE_LED]['enable'],
            'follow_move'=>$this->_switch[TYPE_FOLLOW_MOVE]['enable'],
            'alarm_notify'=>$this->_status[KEY_ALARM_NOTIFICATION],
            'alarm_sound_mod'=>ALARM_SOUND_MODE[$this->_status['alarmSoundMode']],
            # 'alarm_sound_mod'=>'Intensive',
            'encrypted'=>$this->_status['isEncrypt'],
            'local_ip'=>$this->_connection['localIp'],
            'local_rtsp_port'=>$this->_connection['localRtspPort'],
            'detection_sensibility'=>$this->_detection_sensibility,
        );
        return $status;
    }


    function move($direction, $speed=5)
    {
        log::add('jeezviz-v2', 'debug', "Moves the camera to the ".$direction."\r\n");
        $allowedDirections=array('right','left','down','up');
        if (!in_array($direction, $allowedDirections))
        {
            log::add('jeezviz-v2', 'debug', "Invalid direction: ".$direction."\r\n");
            return false;
        }
        # launch the start command
        $this->_client->ptzControl(strtoupper($direction), $this->_serial, 'START', $speed);
        sleep (1);
        # launch the stop command
        $this->_client->ptzControl(strtoupper($direction), $this->_serial, 'STOP', $speed);
        return True;
    }

    function alarm_notify($enable)
    {
        log::add('jeezviz-v2', 'debug', "Enable/Disable camera notification when movement is detected.\r\n");
        #return $this->_client->data_report($this->_serial, $enable);
        return $this->_client->switch_alarm($this->_serial, $enable);
    }

    function alarm_sound($sound_type)
    {
        log::add('jeezviz-v2', 'debug', "Enable/Disable camera sound when movement is detected.\r\n");
        # we force enable = 1 , to make sound...
        return $this->_client->alarm_sound($this->_serial, $sound_type, 1);
    }

    function alarm_detection_sensibility($sensibility)
    {
        log::add('jeezviz-v2', 'debug', "Enable/Disable camera sound when movement is detected.\r\n");
        # we force enable = 1 , to make sound...
        return $this->_client->detection_sensibility($this->_serial, $sensibility);
    }

    function switch_device_audio($enable=0)
    {
        log::add('jeezviz-v2', 'debug', "Switch audio status on a device.\r\n");
        return $this->_client->switch_status($this->_serial, TYPE_AUDIO, $enable);
    }

    function switch_device_state_led($enable=0)
    {
        log::add('jeezviz-v2', 'debug', "Switch audio status on a device.\r\n");
        return $this->_client->switch_status($this->_serial, TYPE_STATE_LED, $enable);
    }

    function switch_device_ir_led($enable=0)
    {
        log::add('jeezviz-v2', 'debug', "Switch audio status on a device.\r\n");
        return $this->_client->switch_status($this->_serial, TYPE_IR_LED, $enable);
    }

    function switch_privacy_mode($enable=0)
    {
        log::add('jeezviz-v2', 'debug', "Switch privacy mode on a device.\r\n");        
        return $this->_client->switch_status($this->_serial, TYPE_PRIVACY_MODE, $enable);
    }

    function switch_follow_move($enable=0)
    {
        log::add('jeezviz-v2', 'debug', "Switch follow move.\r\n");
        return $this->_client->switch_status($this->_serial, TYPE_FOLLOW_MOVE, $enable);
    }
}
    