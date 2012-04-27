<?
/* Syslog.class.php - Class that implements raw Syslog protocol
 * Copyright (C) 2007 Erik Osterman <e@osterman.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/* File Authors:
 *   Erik Osterman <e@osterman.com>
 */


define('FACILITY_KERN',     0 << 3); 
define('FACILITY_USER',     1 << 3); 
define('FACILITY_MAIL',     2 << 3); 
define('FACILITY_DAEMON',   3 << 3); 
define('FACILITY_AUTH',     4 << 3); 
define('FACILITY_SYSLOG',   5 << 3); 
define('FACILITY_LPR',      6 << 3); 
define('FACILITY_NEWS',     7 << 3); 
define('FACILITY_UUCP',     8 << 3); 
define('FACILITY_CRON',     9 << 3); 
define('FACILITY_AUTHPRIV', 10 << 3); 
define('FACILITY_FTP',      11 << 3); 
define('FACILITY_NTP',      12 << 3); 
define('FACILITY_AUDIT',    13 << 3); 
define('FACILITY_ALERT',    14 << 3); 
define('FACILITY_CLOCK',    15 << 3); 
define('FACILITY_LOCAL0',   16 << 3); 
define('FACILITY_LOCAL1',   17 << 3); 
define('FACILITY_LOCAL2',   18 << 3); 
define('FACILITY_LOCAL3',   19 << 3); 
define('FACILITY_LOCAL4',   20 << 3); 
define('FACILITY_LOCAL5',   21 << 3); 
define('FACILITY_LOCAL6',   22 << 3); 
define('FACILITY_LOCAL7',   23 << 3); 

abstract class Syslog
{
 
  // Levels
  const LOG_EMERG   = 0; // LOG_EMERG system is unusable 
  const LOG_ALERT   = 1; // action must be taken immediately 
  const LOG_CRIT    = 2; // critical conditions 
  const LOG_ERR     = 3; // error conditions 
  const LOG_WARNING = 4; // warning conditions 
  const LOG_NOTICE  = 5; // normal, but significant, condition 
  const LOG_INFO    = 6; // informational message 
  const LOG_DEBUG   = 7; // debug-level message 

  // Facilities
  const FACILITY_KERN     = FACILITY_KERN;     // kernel messages 
  const FACILITY_USER     = FACILITY_USER;     // generic user-level messages 
  const FACILITY_MAIL     = FACILITY_MAIL;     // mail subsystem 
  const FACILITY_DAEMON   = FACILITY_DAEMON;   // other system daemons 
  const FACILITY_AUTH     = FACILITY_AUTH;     // security/authorization messages (use FACILITY_AUTHPRIV instead in systems where that constant is defined)  
  const FACILITY_SYSLOG   = FACILITY_SYSLOG;   // messages generated internally by syslogd 
  const FACILITY_LPR      = FACILITY_LPR;      // line printer subsystem 
  const FACILITY_NEWS     = FACILITY_NEWS;     // USENET news subsystem 
  const FACILITY_UUCP     = FACILITY_UUCP;     // UUCP subsystem
  const FACILITY_CRON     = FACILITY_CRON;     // clock daemon (cron and at) 
  const FACILITY_AUTHPRIV = FACILITY_AUTHPRIV; // security/authorization messages (private) 
  const FACILITY_FTP      = FACILITY_FTP;      // FTP daemon
  const FACILITY_NTP      = FACILITY_NTP;      // NTP subsystem
  const FACILITY_AUDIT    = FACILITY_AUDIT;    // audit
  const FACILITY_ALERT    = FACILITY_ALERT;    // alert
  const FACILITY_CLOCK    = FACILITY_CLOCK;    // clock daemon
  const FACILITY_LOCAL0   = FACILITY_LOCAL0;   // reserved for local use, these are not available in Windows 
  const FACILITY_LOCAL1   = FACILITY_LOCAL1;   // reserved for local use, these are not available in Windows 
  const FACILITY_LOCAL2   = FACILITY_LOCAL2;   // reserved for local use, these are not available in Windows 
  const FACILITY_LOCAL3   = FACILITY_LOCAL3;   // reserved for local use, these are not available in Windows 
  const FACILITY_LOCAL4   = FACILITY_LOCAL4;   // reserved for local use, these are not available in Windows 
  const FACILITY_LOCAL5   = FACILITY_LOCAL5;   // reserved for local use, these are not available in Windows 
  const FACILITY_LOCAL6   = FACILITY_LOCAL6;   // reserved for local use, these are not available in Windows 
  const FACILITY_LOCAL7   = FACILITY_LOCAL7;   // reserved for local use, these are not available in Windows 


  const LOG_PID    = 0x01; // include PID with each message 
  const LOG_CONS   = 0x02; // if there is an error while sending data to the system logger, write directly to the system console  
  const LOG_ODELAY = 0x04; // (default) delay opening the connection until the first message is logged  
  const LOG_NDELAY = 0x08; // open the connection to the logger immediately  
  const LOG_NOWAIT = 0x10; // don't wait for console forks (deprecated)
  const LOG_PERROR = 0x20; // print log message also to standard error 

  protected static $instantiated;
  protected static $instance;
  protected $priorities;
  protected $facilities;
  protected $options;
  protected $option_mask;
  protected $ident;
  protected $facility;

  public function __construct()
  {
    Syslog::$instantiated = true;
    self::$instance = $this;
    $this->option_mask = 0;

    $this->priorities = Array(
                'emerg'    => Syslog::LOG_EMERG,   
                'alert'    => Syslog::LOG_ALERT,   
                'crit'     => Syslog::LOG_CRIT,    
                'critical' => Syslog::LOG_CRIT,  
                'err'      => Syslog::LOG_ERR,   
                'error'    => Syslog::LOG_ERR,   
                'warning'  => Syslog::LOG_WARNING, 
                'notice'   => Syslog::LOG_NOTICE,  
                'info'     => Syslog::LOG_INFO,    
                'debug'    => Syslog::LOG_DEBUG  
                 );

    $this->facilities = Array(
                'auth'      => Syslog::FACILITY_AUTH,    
                'authpriv'  => Syslog::FACILITY_AUTHPRIV,  
                'cron'      => Syslog::FACILITY_CRON,      
                'daemon'    => Syslog::FACILITY_DAEMON,    
                'kern'      => Syslog::FACILITY_KERN,    
                'local0'    => Syslog::FACILITY_LOCAL0,    
                'local1'    => Syslog::FACILITY_LOCAL1,    
                'local2'    => Syslog::FACILITY_LOCAL2,    
                'local3'    => Syslog::FACILITY_LOCAL3,    
                'local4'    => Syslog::FACILITY_LOCAL4,    
                'local5'    => Syslog::FACILITY_LOCAL5,    
                'local6'    => Syslog::FACILITY_LOCAL6,    
                'local7'    => Syslog::FACILITY_LOCAL7,    
                'lpr'       => Syslog::FACILITY_LPR,     
                'mail'      => Syslog::FACILITY_MAIL,    
                'news'      => Syslog::FACILITY_NEWS,    
                'syslog'    => Syslog::FACILITY_SYSLOG,    
                'user'      => Syslog::FACILITY_USER,    
                'uucp'      => Syslog::FACILITY_UUCP,    
              );

    $this->options = Array (
                'cons'      => Syslog::LOG_CONS,  
                'ndelay'    => Syslog::LOG_NDELAY,  
                'odelay'    => Syslog::LOG_ODELAY,  
                'perror'    => Syslog::LOG_PERROR,  
                'pid'       => Syslog::LOG_PID    
            );
  }

  public function __destruct()
  {
    try {
      $this->close();
      Syslog::$instantiated = false;
    } catch ( Exception $e )
    {
       print get_class($this) . "::__destruct " . $e->getMessage() . "\n";
    }
    unset($this->priorities);
    unset($this->facilities);
    unset($this->options);
    unset($this->option_mask);
    unset($this->ident);
    unset($this->facility);
  }
    
  public function __get( $property )
  {
    switch( $property )
    {
      case 'ident':
        return $this->ident;
      case 'facility':
        return $this->facility;
      default:
        throw new Exception( get_class($this) . "::$property not defined");
    }
  }

  public function __set( $property, $property  ) 
  {
    switch( $property )
    {
      default:
        throw new Exception( get_class($this) . "::$property cannot be set");
    }
  }

  public function __unset( $property )
  {
    throw new Exception( get_class($this) . "::$property cannot be unset");
  }
  
  public function __call( $func, $args )
  {
    // Are we trying to log something?
    if( array_key_exists( $func, $this->priorities ) )
    {
      array_unshift($args, $this->priorities[ $func ] );
      return call_user_func_array( Array( &$this, 'log' ), $args );
    } 

    // Are we setting the facility?
    elseif ( array_key_exists( $func, $this->facilities ) )
    {
      $this->facility = $func;
      $this->ident  = $args[0];
      if( empty($this->ident) )
        throw new Exception("Ident cannot be empty");
      if( $this->option_mask == 0 )
        throw new Exception("Must select atleast 1 logging option (" . join(", ", array_keys($this->options) ) . ")");
      return $this->open($this->ident, $this->option_mask, $this->facilities[$this->facility]);
    } 

    // Are we setting an option?
    elseif( array_key_exists( $func, $this->options ) )
    {
      $mask = $this->options[ $func ] ;
      $selected = $args[0];
      if( $selected === false )
      {
        //print "Deselected $func\n";
        $this->option_mask &= ~$mask;
      } if( $selected === true ) {
        //print "Selected $func\n";
        $this->option_mask |= $mask;
      } else
      {
        return $this->option_mask & $mask;
      }
  
    } else
      throw new Exception( get_class($this) . "::$func is not configured" );
  }

  public function getInstance()
  {
    return self::$instance;
  }
  
  public function log()
  {
    if( empty($this->ident) )
      throw new Exception("IDENT is empty. Cannot log to syslog. Must first call one of (" . join(", ", array_keys($this->facilities)) . ")"); 
    $args = func_get_args();
    $mask = array_shift($args);
    $message = call_user_func_array('sprintf', $args);
    $this->send( $mask, $message );
  }

  abstract public function open($ident, $options, $facility);
  abstract public function close();
  abstract public function send($mask, $message);
}

?>
