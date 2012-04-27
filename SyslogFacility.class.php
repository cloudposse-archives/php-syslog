<?
/* SyslogFacility.class.php - Implements Syslog via the native PHP functions, which are buggy
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

class SyslogFacility extends Syslog
{

  public function close()
  {
		closelog();
	}

  public function open($ident, $options, $facility)
  {
	  return openlog( $ident, $options, $facility);
	}

  public function send($mask, $message)
  {
		syslog( $mask, $message );
	}

}

/*
// Example Usage:
$syslog = new SyslogFacility();
$syslog->perror(true);
print "selected? " . $syslog->perror() . "\n";
$syslog->perror(false);
print "selected? " . $syslog->perror() . "\n";
$syslog->perror(true);
print "selected? " . $syslog->perror() . "\n";

$syslog->local0('test');
$syslog->notice("Unable to open %s", 'file');
$syslog->error("You should use err");
$syslog2 = new SyslogFacility();
*/
?>
