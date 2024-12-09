<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

class xTools
{
	public static function login() : string
	{
		$sessionname = \damix\engines\settings\Setting::getValue('default', 'auth', 'sessionname');
		
		
		return $_SESSION[$sessionname]->login ?? '';
	}
	
	public static function usergroup() : string
	{
		return '';
	}
	
	public static function getIp() : string
	{
		$ip = '';
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])&&$_SERVER['HTTP_X_FORWARDED_FOR']){
			$list=preg_split('/[\s,]+/',$_SERVER['HTTP_X_FORWARDED_FOR']);
			$list=array_reverse($list);
			$lastIp='';
			foreach($list as $ip){
				$ip=trim($ip);
				if(preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/',$ip,$m)){
					if($m[1]=='10'||$m[1]=='010'
						||($m[1]=='172'&&(intval($m[2])& 240==16))
						||($m[1]=='192'&&$m[2]=='168'))
						break;
					$lastIp=$ip;
				}
				elseif(preg_match('/^(?:[a-f0-9]{1,4})(?::(?:[a-f0-9]{1,4})){7}$/i',$ip)){
					$lastIp=$ip;
				}
			}
			if($lastIp)
				$ip = $lastIp;
		}
		if(isset($_SERVER['HTTP_CLIENT_IP'])&&$_SERVER['HTTP_CLIENT_IP']){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}else{
			$ip = isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		}
		
		return $ip;
	}
	
	 public static function parseFloat( $value )
    {
        if( is_string( $value ) )
        {
            $value = preg_replace ( array( '/,/', '/ /' ), array( '.', '' ), $value );
            return floatval( $value );
        }
        if( is_scalar( $value ) )
        {
            return floatval( $value );
        }
        return 0.0;
    }
    
    public static function parseInt( $value )
    {
        if( is_string( $value ) )
        {
            $value = preg_replace ( array( '/,/', '/ /' ), array( '.', '' ), $value );
            return intval( $value );
        }
        if( is_scalar( $value ) )
        {
            return intval( $value );
        }
        return 0;
    }
    
    public static function numberFormat( float $number, $decimal = 2, $virgule = '.', $millier = ' ' )
    {
        if(!is_numeric($number))
        {
            $number = xTools::ParseFloat( $number );
        }
        
        return number_format($number, $decimal, $virgule, $millier);
    }
	
	public static function phoneFormat( $value )
    {
        $value = preg_replace( '/^([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '$1 $2 $3 $4 $5', $value );
        return $value;
    }
}