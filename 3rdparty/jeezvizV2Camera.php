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

class EzvizV2Camera
{
    public $_serial;
    public $_loaded;
    public $_device;
    public $_account;
    public $_password;
    function __construct($serial)
    {
        log::add('jeezvizv2', 'debug', "Initialize the camera V2 object.\r\n");
        $this->_serial = $serial;
        $this->_account = config::byKey('identifiant', 'jeezvizv2');
        $this->_password =  config::byKey('motdepasse', 'jeezvizv2');
        $this->_loaded = 0;
        # $this->load();
    }

        
    function load()
    {
        log::add('jeezvizv2', 'debug', "Load object properties V2\r\n");
        log::add('jeezvizv2', 'debug', 'pyezviz -u '.$this->_account.' -p ****** camera '.'--serial '.$this->_serial.' status');
        exec('pyezviz -u '.$this->_account.' -p '.$this->_password.' camera '.'--serial '.$this->_serial.' status', $response);        
        log::add('jeezvizv2', 'debug',implode('\r\n', $response));        
        $response=json_decode(implode('',$response), true);
        $this->_loaded = 1;
        return $response;
    }

    function alarm_notify($enable)
    {
        log::add('jeezvizv2', 'debug', "Enable/Disable camera notification when movement is detected V2.\r\n");
        log::add('jeezvizv2', 'debug', 'pyezviz -u '.$this->_account.' -p ****** camera '.'--serial '.$this->_serial.' alarm --notify '.$enable);
        exec('pyezviz -u '.$this->_account.' -p '.$this->_password.' camera '.'--serial '.$this->_serial.' alarm --notify '.$enable, $response);        
        log::add('jeezvizv2', 'debug',implode('\r\n', $response));        
        $response=json_decode(implode('',$response), true);
        return $response;
    }

    function alarm_sound($soundMode)
    {
        log::add('jeezvizv2', 'debug', "Change camera notification mode V2.\r\n");
        log::add('jeezvizv2', 'debug', 'pyezviz -u '.$this->_account.' -p ****** camera '.'--serial '.$this->_serial.' alarm --sound '.$soundMode);
        exec('pyezviz -u '.$this->_account.' -p '.$this->_password.' camera '.'--serial '.$this->_serial.' alarm --sound '.$soundMode, $response);        
        log::add('jeezvizv2', 'debug',implode('\r\n', $response));        
        $response=json_decode(implode('',$response), true);
        return $response;
    }

    function switch_privacy_mode($enable=0)
    {
        return $this->switch_mode('privacy',$enable);
    }

    function switch_audio_mode($enable=0)
    {
        return $this->switch_mode('audio',$enable);
    }
    
    function switch_ir_mode($enable=0)
    {
        return $this->switch_mode('ir',$enable);
    }
    
    function switch_state_mode($enable=0)
    {
        return $this->switch_mode('state',$enable);
    }
    function switch_sleep_mode($enable=0)
    {
        return $this->switch_mode('sleep',$enable);
    }
    function switch_follow_move_mode($enable=0)
    {
        return $this->switch_mode('follow_move',$enable);
    }
    function switch_sound_alarm_mode($enable=0)
    {
        return $this->switch_mode('sound_alarm',$enable);
    }
    function switch_mode($switchName,$enable)
    {
        log::add('jeezvizv2', 'debug', 'Switch '.$switchName.' mode on a device V2');
        log::add('jeezvizv2', 'debug', 'pyezviz -u '.$this->_account.' -p ****** camera '.'--serial '.$this->_serial.' switch --switch '.$switchName.' --enable '.$enable);
        exec('pyezviz -u '.$this->_account.' -p '.$this->_password.' camera '.'--serial '.$this->_serial.' switch --switch '.$switchName.' --enable '.$enable, $response);        
        log::add('jeezvizv2', 'debug',implode('\r\n', $response));        
        $response=json_decode(implode('',$response), true);
        return $response;
    }
    function move($direction, $speed=5)
    {
        log::add('jeezvizv2', 'debug', "Move a device V2\r\n");
        log::add('jeezvizv2', 'debug', 'pyezviz -u '.$this->_account.' -p ****** camera '.'--serial '.$this->_serial.' move --direction '.$direction.' --speed '.$speed);
        exec('pyezviz -u '.$this->_account.' -p '.$this->_password.' camera '.'--serial '.$this->_serial.' move --direction '.$direction.' --speed '.$speed, $response);        
        log::add('jeezvizv2', 'debug',implode('\r\n', $response));        
        $response=json_decode(implode('',$response), true);
        return $response;
    }

    function move_coords($x, $y)
    {
        log::add('jeezvizv2', 'debug', "Move a device to center V2\r\n");
        log::add('jeezvizv2', 'debug', 'pyezviz -u '.$this->_account.' -p ****** camera '.'--serial '.$this->_serial.' move_coords --x '.$x.' --y '.$y);
        exec('pyezviz -u '.$this->_account.' -p '.$this->_password.' camera '.'--serial '.$this->_serial.' move_coords --x '.$x.' --y '.$y, $response);        
        log::add('jeezvizv2', 'debug',implode('\r\n', $response));        
        $response=json_decode(implode('',$response), true);
        return $response;
    }

    

    function set_home_defence_mode($home_defence_mode)
    {
        log::add('jeezvizv2', 'debug', "Set home defence mode V2\r\n");
        log::add('jeezvizv2', 'debug', 'pyezviz -u '.$this->_account.' -p ****** home_defence_mode --mode '.$home_defence_mode);
        exec('pyezviz -u '.$this->_account.' -p '.$this->_password.' home_defence_mode --mode '.$home_defence_mode, $response);        
        log::add('jeezvizv2', 'debug',implode('\r\n', $response));        
        $response=json_decode(implode('',$response), true);
        return $response;
    }
}
    