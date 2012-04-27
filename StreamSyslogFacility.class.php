<?
/* StreamSyslogFacility.class.php - Implements RFC 3164 syslogging
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

class StreamSyslogFacility extends Syslog
{
  protected $hostname; // no embedded space, no domain name, only a-z A-Z 0-9 and other authorized characters
  protected $fqdn;
  protected $ipFrom;
  protected $url;      // Syslog destination
  protected $timeout;  // Timeout of the UDP connection (in seconds)
  protected $stream;
 
  public function __construct($ident = null, $url = 'udp://localhost:514')
  {
    parent::__construct();

    $this->ident = substr($ident, 0, 32);
    $this->setTimeout(1);
    $this->url = $url;
    $this->open($ident, null, null);
  }

  public function setHostname($hostname)
  {
    $hostname = substr($hostname, 0, strpos($hostname. '.', '.'));
    $this->hostname = $hostname;
  }
  
  
  public function setFqdn($fqdn)
  {
    $this->fqdn = $fqdn;
  }
  
  
  public function setIpFrom($ipFrom)
  {
    $this->ipFrom = $ipFrom;
  }
  
  
  public function setTimeout($timeout)
  {
    if (intval($timeout) > 0)
      $this->timeout = intval($timeout);
  }
  
  public function open($ident, $options, $facility)
  {
    $errno = null;
    $errstr = null;
    $this->stream = stream_socket_client($this->url, $errno, $errstr, $this->timeout);
    if(!$this->stream)
      throw new Exception( get_class($this) . "::open failed to open {$this->url}; $errstr", $errno);
    stream_set_write_buffer($this->stream, 0);
    $this->ident = $ident;
    $this->facility = $facility;
  }

  public function close()
  {
    fclose($this->stream);
  }
  
  public function send($mask, $message)
  {
    $time      = time();
    $timestamp = date('M j H:i:s', $time);
    $pri    = '<' . ($mask + $this->facility) . '>';
   // $pri = '<13>';
    $packet = $pri
              . $timestamp 
              . ' ' . ($this->hostname ? $this->hostname . ' ': '') 
              . $this->ident 
              . ($this->option_mask&Syslog::LOG_PID ? '[' . getmypid() . ']' : '')
              . ': ' 
              . ($this->fqdn ? $this->fqdn . ' ' : '')
              . ($this->ipFrom ? $this->ipFrom . ' ' : '')
              . $message
              . "\0";
    //$packet = $pri . "May 17 15:50:44 eosterman: test\0";
    $packet = substr($packet, 0, 1024);
    //print "[$packet]\n";
    stream_socket_sendto($this->stream, $packet);
  }
}

/*
$log = new StreamSyslogFacility('eosterman');
$log->pid(true);
$log->perror(true);
$log->local0('eosterman');
$log->notice('quit');
*/

?>
