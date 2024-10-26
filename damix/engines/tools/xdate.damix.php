<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
declare(strict_types=1);
namespace damix\engines\tools;

class xDate
    extends \DateTime
{
    protected $_empty = false;
    
    public const LOCALE_SELECTOR = 'damix~lclformat';
    
    
    private static $locales = array();
    /**
     * Format de date en fonction de la langue utilisateur
     * @var int
     */
    const LANG_DFORMAT = '10';
    
    /**
     * Format de date + heure en fonction de la langue utilisateur
     * @var int
     */
    const LANG_DTFORMAT = '11';
    
    /**
     * Format d'heure en fonction de la langue utilisateur
     * @var int
     */
    const LANG_TFORMAT = '12';
    
    /**
     * Format de date en fonction de la base de données
     * @var int
     */
    const DB_DFORMAT = '20';
    
    /**
     * Format de date + heure en fonction de la base de données
     * @var int
     */
    const DB_DTFORMAT = '21';
    
    /**
     * Format d'heure en fonction de la base de données
     * @var int
     */
    const DB_TFORMAT = '22';
    
    /**
     * Numéro du jour de l'année de 1 à 365
     * @var int
     */
    const INT_NUMDAY = '71';
    
    /**
     * Nombre de jours dans le mois de 1 à 31
     * @var int
     */
    const INT_MONTHLENGTH = '72';
    
    /**
     * Numéro de la semaine au format ISO-8601
     * @var int
     */
    const INT_NUMWEEK = '73';
    
    /**
     * Numéro du mois de 1 à 12
     * @var int
     */
    const INT_MONTH = '74';
    
    /**
     * Numéro du jour dans le mois de 1 à 31
     * @var int
     */
    const INT_DAY = '75';
    
    /**
     * L'année sur 4 caractères
     * @var int
     */
    const INT_YEAR = '76';
    
    /**
     * Numéro du jour dans la semaine
     * @var int
     */
    const INT_DAYWEEK = '77';
    
    /**
     * L'année sur 2 caractères
     * @var int
     */
    const INT_YEAR_TWOLETTER = '78';
    /**
     * L'année sur 2 caractères
     * @var int
     */
    const ISO_8601 = '80';
    
    const ISO_8601_DT = '81';
    const ISO_8601_D = '82';
    
    const INT_YMD = '90';
    const INT_HMS = '91';
    const INT_DMY = '92';
    const INT_DMY_TWOLETTER = '93';
    
    const STR_DAY = '101'; // Jour de la semaine, dans la langue courante
    const STR_DAY_THREELETTER = '102'; // Jour de la semaine, en trois lettres, dans la langue courante
    
    public function __construct( string $time = "now", ?\DateTimeZone $timezone = NULL )
    {
        if( $time == 'now' )
        {
            parent::__construct( $time, $timezone ?? new \DateTimeZone( date_default_timezone_get() ) );
        }
        else
        {
            parent::__construct("now", $timezone ?? new \DateTimeZone( date_default_timezone_get() ) );
            
            if( is_string( $time ) )
            {
                if( ! $this->IsDBDate( $time ) )
                {
                    if( ! $this->IsLangDate( $time ) )
                    {
                        $this->_empty = true;
                    }
                }
            }
        }
    }
    
    public static function now() : xDate
    {
        return new xDate( 'now' );
    }
    
    public static function dateNull() : xDate
    {
        $xdate = new xDate();
        $xdate->setNull();
        return $xdate;
    }
    
    public static function load( mixed $time = "now", ?\DateTimeZone $timezone = NULL ) : ?xDate
    {
        if( $time === null )
        {
            return null;
        }

        if( $time instanceof xDate  )
        {
            return $time;
        }
        elseif( $time instanceof DateTime )
        {
            $xdate = new xDate();
            $xdate->setDateTime( $time->format( 'Y' ), $time->format( 'm' ), $time->format( 'd' ), $time->format( 'H' ), $time->format( 'i' ), $time->format( 's' ) );
        }
        elseif( $time == '' )
        {
            $xdate = new xDate();
            $xdate->setNull();
        }
        else
        {
            $xdate = new xDate( $time, $timezone );
        }

        return $xdate;
    }
	
	public static function loadformat( mixed $value, string $format ) : string
	{
		$xdate = \damix\engines\tools\xDate::load( $value );
		return $xdate->format( $format );
	}
    
    public function isNull() : bool
    {
        return $this->_empty;
    }

    public function setNull()
    {
        $this->_empty = true;
    }
    
    public function copy() : xDate
    {
        $xdate = new xDate();
        $xdate->setDate( $this->getYear(), $this->getMonth(), $this->getDay() );
        $xdate->setTime( $this->getHour(), $this->getMinute(), $this->getSecond() );
        
        return $xdate;
    }
    
    private function IsDBDate( string $date, string &$db_format = null ) : bool
    {
        // $p = array( 'driver' => 'mariadb' );
        $driver = \damix\engines\databases\Db::getDriverName( '' );

        if( empty( $date ) )
        {
            return false;
        }

        $loc = xDate::LOCALE_SELECTOR . '.' . $driver . '.reg_match_datetime';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $this->setDateTime( intval($matches[1]), intval($matches[2]), intval($matches[3]), intval($matches[4]), intval($matches[5]), intval($matches[6]) );
            $db_format = self::DB_DTFORMAT;
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.' . $driver . '.reg_match_datetimeshort';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $this->setDateTime( intval($matches[1]), intval($matches[2]), intval($matches[3]), intval($matches[4]), intval($matches[5]), 0 );
            $db_format = self::DB_DTFORMAT;
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.' . $driver . '.reg_match_date';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $this->setDateTime( intval($matches[1]), intval($matches[2]), intval($matches[3]), 0, 0, 0 );
            $db_format = self::DB_DFORMAT;
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.' . $driver . '.reg_match_time';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $this->setTime( intval($matches[1]), intval($matches[2]), intval($matches[3]) );
            $db_format = self::DB_TFORMAT;
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.' . $driver . '.reg_match_timeshort';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $this->setTime( intval($matches[1]), intval($matches[2]), 0 );
            $db_format = self::DB_TFORMAT;
            return true;
        }

        return false;
    }

    /**
     * Permet de savoir si $date est une date au format de la langue courante
     * @param   string  $date  Date à analyser
     * @return  bool
     */
    public function IsLangDate( string | xDate $date ) : bool
    {
        if( $date instanceof xDate )
        {
            return true;
        }
        
        $year = 0;
        $month = 0;
        $day = 0;
        $hour = 0;
        $minute = 0;
        $second = 0;
        $loc = xDate::LOCALE_SELECTOR . '.local.reg_match_datetime';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $format = preg_split('/,/', \damix\engines\locales\Locale::get( xDate::LOCALE_SELECTOR . '.local.format_datetime' ) );
            foreach( $format as $i => $elt )
            {
                $$elt = $matches[$i + 1];
            }
            $this->setDateTime( $year, $month, $day, $hour, $minute, $second );
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.local.reg_match_datetimeshort';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $format = preg_split('/,/', \damix\engines\locales\Locale::get( xDate::LOCALE_SELECTOR . '.local.format_datetimeshort' ) );
            foreach( $format as $i => $elt )
            {
                $$elt = $matches[$i + 1];
            }
            $this->setDateTime( $year, $month, $day, $hour, $minute, $second );
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.local.reg_match_date';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $format = preg_split('/,/', \damix\engines\locales\Locale::get( xDate::LOCALE_SELECTOR . '.local.format_date' ) );
            foreach( $format as $i => $elt )
            {
                $$elt = intval($matches[$i + 1]);
            }
            $this->setDateTime( $year, $month, $day, 0, 0, 0 );
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.local.reg_match_time';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $format = preg_split('/,/', \damix\engines\locales\Locale::get( xDate::LOCALE_SELECTOR . '.local.format_time' ) );
            foreach( $format as $i => $elt )
            {
                $$elt = $matches[$i + 1];
            }
            $this->setTime( intval($hour), intval($minute), intval($second) );
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.local.reg_match_timeshort';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $format = preg_split('/,/', \damix\engines\locales\Locale::get( xDate::LOCALE_SELECTOR . '.local.format_timeshort' ) );
            foreach( $format as $i => $elt )
            {
                $$elt = $matches[$i + 1];
            }
            $this->setTime( intval($hour), intval($minute), intval($second) );
            return true;
        }

        $loc = xDate::LOCALE_SELECTOR . '.local.reg_match_ymd';
        if( !isset( self::$locales[ $loc ] ) )
        {
            self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
        }
        $regex = self::$locales[ $loc ];
        if( preg_match( $regex, $date, $matches ) )
        {
            $format = preg_split('/,/', \damix\engines\locales\Locale::get( xDate::LOCALE_SELECTOR . '.local.format_ymd' ) );
            foreach( $format as $i => $elt )
            {
                $$elt = $matches[$i + 1];
            }
            $this->setDateTime( $year, $month, $day, 0, 0, 0 );
            return true;
        }

        
        if( preg_match( '/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})([+-][0-9]{2}:[0-9]{2}){0,1}/', $date, $matches ) )
        {
            $this->setDateTime( $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6] );
            return true;
        }
    
        
        
        return false;
    }
    
    public static function day360( xDate $date1, xDate $date2, bool $methodUS = true ) : int
    {
        $startDay = $date1->format( 'j' );
        $startMonth = $date1->format( 'n' );
        $startYear = $date1->format( 'Y' );
        $endDay = $date2->format( 'j' );
        $endMonth = $date2->format( 'n' );
        $endYear = $date2->format( 'Y' );

        if ($startDay == 31)
        {
            --$startDay;
        }
        elseif ($methodUS && ($startMonth == 2 && ($startDay == 29 || ($startDay == 28 && !$date1->isBissextile()))))
        {
            $startDay = 30;
        }
        if ($endDay == 31)
        {
            if ($methodUS && $startDay != 30)
            {
                $endDay = 1;
                if ($endMonth == 12)
                {
                    ++$endYear;
                    $endMonth = 1;
                }
                else
                {
                    ++$endMonth;
                }
            }
            else
            {
                $endDay = 30;
            }
        }

        return $endDay + $endMonth * 30 + $endYear * 360 - $startDay - $startMonth * 30 - $startYear * 360;
    }
    
    public function setTime(int $hour, int $minute, int $second = 0, int $microseconds = 0): xDate
    {
        parent::setTime( $hour, $minute, $second);
        $this->_empty = false;
		return $this;
    }
   
    public function setDateTime( int $year, int $month, int $day, int $hour, int $minute, int $second, int $microseconds = 0 ) : bool
    {
        $this->_empty = $year == 0 && $month == 0 && $day == 0 && $hour == 0 && $minute == 0 && $second == 0;

        if( $this->_empty )
        {
            return false;
        }

        if( $year > 0 || $month > 0 || $day > 0 )
        {
            $this->setDate( $year, $month, $day );
        }
        
        $this->setTime( $hour, $minute, $second, $microseconds );
        
        return true;
    }
    
    public function format( string $format ) : string
    {
        if( $this->_empty )
        {
            return '';
        }
        if( is_numeric( $format ) )
        {
            // $p = \jProfiles::get( 'jdb' );
            $p = array( 'driver' => 'mariadb' );
			$driver = $p[ 'driver' ];

            switch( $format )
            {
                case xDate::LANG_DFORMAT:
                    $loc = xDate::LOCALE_SELECTOR . '.local.date';
                    break;
                case xDate::LANG_DTFORMAT:
                    $loc = xDate::LOCALE_SELECTOR . '.local.datetime';
                    break;
                case xDate::LANG_TFORMAT:
                    $loc = xDate::LOCALE_SELECTOR . '.local.time';
                    break;
                case xDate::DB_DFORMAT:
                    $loc = xDate::LOCALE_SELECTOR . '.'.$driver.'.date';
                    break;
                case xDate::DB_DTFORMAT:
                    $loc = xDate::LOCALE_SELECTOR . '.'.$driver.'.datetime';
                    break;
                case xDate::DB_TFORMAT:
                    $loc = xDate::LOCALE_SELECTOR . '.'.$driver.'.time';
                    break;
                case xDate::INT_NUMDAY:
                    return parent::format( 'z' );
                    break;
                case xDate::INT_MONTHLENGTH:
                    return parent::format( 't' );
                    break;
                case xDate::INT_NUMWEEK:
                    return parent::format( 'W' );
                    break;
                case xDate::INT_MONTH:
                    return parent::format( 'm' );
                    break;
                case xDate::INT_DAY:
                    return parent::format( 'd' );
                    break;
                case xDate::INT_YEAR:
                    return parent::format( 'Y' );
                    break;
                case xDate::INT_DAYWEEK:
                    return parent::format( 'w' );
                    break;
                case xDate::INT_YEAR_TWOLETTER:
                    return parent::format( 'y' );
                    break;
                case xDate::ISO_8601:
                    return parent::format( 'c' );
                    break;
                case xDate::ISO_8601_DT:
                    return parent::format( 'Y-m-d\TH:i:s' );
                    break;
                case xDate::ISO_8601_D:
                    return parent::format( 'Y-m-d' );
                    break;
                case xDate::STR_DAY:
                    $loc = xDate::LOCALE_SELECTOR . '.local.dayofweek.' . strtolower(parent::format( 'l' ));
                    if( !isset( self::$locales[ $loc ] ) ){self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );}
                    return self::$locales[ $loc ];
                    break;
                case xDate::STR_DAY_THREELETTER:
                    $loc = xDate::LOCALE_SELECTOR . '.local.dayofweek.' . strtolower(parent::format( 'D' ));
                    if( !isset( self::$locales[ $loc ] ) ){self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );}
                    return self::$locales[ $loc ];
                    break;
                case xDate::INT_YMD:
                    return parent::format( 'Y' ) . parent::format( 'm' ) . parent::format( 'd' );
                    break;
                case xDate::INT_HMS:
                    return parent::format( 'H' ) . parent::format( 'i' ) . parent::format( 's' );
                    break;
                case xDate::INT_DMY:
                    return parent::format( 'd' ) . parent::format( 'm' ) . parent::format( 'Y' );
                    break;
                case xDate::INT_DMY_TWOLETTER:
                    return parent::format( 'd' ) . parent::format( 'm' ) . parent::format( 'y' );
                    break;
            }
            if( !isset( self::$locales[ $loc ] ) )
            {
                self::$locales[ $loc ] = \damix\engines\locales\Locale::get( $loc );
            }
            $format = self::$locales[ $loc ];
        }
        return parent::format( $format );
    }
    
    public function getYear() : int
    {
        return parent::format( 'Y' );
    }
    
    public function getMonth() : int
    {
        return parent::format( 'm' );
    }
    
    public function getDay() : int
    {
        return parent::format( 'd' );
    }
    
    public function getHour() : int
    {
        return parent::format( 'H' );
    }

    public function getMinute() : int
    {
        return parent::format( 'i' );
    }

    public function getSecond() : int
    {
        return parent::format( 's' );
    }
    
    /**
     * Renvoie le jour de la semaine 
     * @return int (1 à 7)
     */
    public function getDayOfWeek() : string
    {
        return parent::format( 'N' );
    }

    public function compareTo(xDate $dt) : int
	{
        $fields=array('Y','m','d','H','i','s');
        foreach($fields as $field){
            if($dt->format($field) > $this->format($field))
                return -1;
            if($dt->format($field) < $this->format($field))
                return 1;
        }
        return 0;
    }
    
    public function isBissextile() : bool
    {
        $ans = $this->getYear();
        if ( (($ans % 4 == 0) && $ans % 100 != 0) || $ans % 400 == 0 ) return true;
        else return false;
    }
    
    public function addPeriod( array $period ) : void
    {
        $time = false;
        $out = '';
     
		foreach( $period as $name => $val )
		{
			switch( $name )
			{
				case 'hour':
					$out = 'H';
					$time = true;
					break;
				case 'day':
					$out = 'D';
					break;
				case 'minute':
					$out = 'M';
					$time = true;
					break;
				case 'month':
					$out = 'M';
					break;
				case 'week':
					$out = 'D';
					$val *= 7;
					break;
				case 'second':
					$out = 'S';
					$time = true;
					break;
				case 'year':
					$out = 'Y';
					break;
			}
			if( $out != '' )
			{
				parent::add( new \DateInterval( 'P' . ( $time ? 'T' : '' ) . intval( $val ) . $out ) );
			}
        }
    }
    
    public function subPeriod( array $period ) : void
    {
        $time = false;
        $out = '';

		foreach( $period as $name => $val )
		{
			switch( $name )
			{
				case 'hour':
					$out = 'H';
					$time = true;
					break;
				case 'day':
					$out = 'D';
					break;
				case 'minute':
					$out = 'M';
					$time = true;
					break;
				case 'month':
					$out = 'M';
					break;
				case 'week':
					$out = 'D';
					$val *= 7;
					break;
				case 'second':
					$out = 'S';
					$time = true;
					break;
				case 'year':
					$out = 'Y';
					break;
			}
			if( $out != '' )
			{
				parent::sub( new \DateInterval( 'P' . ( $time ? 'T' : '' ) . intval( $val ) . $out ) );
			}
		}
    }
    
    public function addDay( int $day ) : void
    {
        if( $day > 0 )
        {
            parent::add( new \DateInterval( 'P' . $day . 'D' ) );
        }
        else
        {
            $this->SubDay(abs($day));
        }
    }
    
    public function subDay(int $day) : void
    {
        parent::sub( new \DateInterval( 'P' . $day . 'D' ) );
    }
    
    public function addMonth( int  $month = 1 ) : xDate
    {
        parent::add( new \DateInterval( 'P' . $month . 'M' ) );
    }
    
    public function subMonth( int $month = 1 ) : void
    {
        parent::sub( new \DateInterval( 'P' . $month . 'M' ) );
    }

    public function addYear( int $year = 1 ) : void
    {
        parent::add( new \DateInterval( 'P' . $year . 'Y' ) );
    }
    
    public function subYear( int $year = 1 ) : void
    {
        parent::sub( new \DateInterval( 'P' . $year . 'Y' ) );
    }
}