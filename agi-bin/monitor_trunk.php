#!/usr/bin/php -q
<?php

/**
* To monitor the trunk registration, dispatch email when non-registered trunk is detected
* @package      Eyo
* @version      $Revision: 1.2 $
* @file         $RCSfile: monitor_trunk.php,v $
* @date         $Date: 2009/11/02 04:31:29 $
* @author       $Author: nsu $
*/


/** ***********************************************************
* A quick and dirty way to report a failing trunk registration.
* Run this regularly, hopefully you will get an alert about one or more failing trunks.
* You run this script at your own risk.
* Not sure what damage this can cause, but I think a bathroom leakage should have nothing to do with this.
* Nikol S ns at eyo.com.au
* *************************************************************
*/

// To setup,
// 1. Change the report_email to yours.
// 2. Put this file somewhere (eg. /root/cron/monitor_trunk.php)
//	chmod it to 750(#chmod 750 monitor_trunk.php). Either owned by root or asterisk(#chown root.root monitor_trunk.php).
// 3. Edit /var/spool/cron/root, and add
//    */15 * * * * /asterisk/agi-bin/monitor_trunk.php
//    to the file. Last line in the cron file must have a carriage return!
//    Above entry in the cron file will run the script every 15 minutes.

  //*** start code added for #module compatibility
$bootstrap_settings['freepbx_auth'] = false;
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
include_once('/etc/asterisk/freepbx.conf');
}
// get user data from module
$date = trunkalarmoptions_getconfig();
//*** end code added for #module compatibility      

// set up the email address to receive the alert email
$report_email = $trunkemail;




// No need to edit below, unless you need or want.

$state = wots_up();
//echo $state;

if ($state == 'ok')
{

	//grace, usually should go this route
	exit;
}
else
{
	//keep cool for 20 seconds, we try again.
	sleep(20);
	$state = wots_up();
	$have_done = "CustomerX has a problem with a trunk. I am attempting to fix it.\r\n";
}

if ($state == 'ok')
{
	//lucky we tried
	exit;
}
else
{
	$have_done .= "Trying to reload the sip channels.\r\n";
	$have_done .= shell_exec('/usr/sbin/asterisk -rx "module reload chan_sip"');
	sleep(20);
	$have_done .= "Reloaded sip channels and waited 20 seconds.\r\n";
	$state = wots_up();
}

if ($state == 'ok')
{
	$have_done = "I managed to fix the problem. You can relax. \r\n";
	//wipes the sweat
	exit;
}

$status = "$have_done\r\n=====================have done above, the current status: =====================\r\n$status";

if ($state == 'requesting')
{
	send_alert_email('CustomerX trunk has been sending registrating requests', $status);
}
elseif ($state == 'empty')
{
	send_alert_email('CustomerX voip trunk registration status is empty', $status);
}
elseif ($state == 'no_auth')
{
	send_alert_email('CustomerX - No Authentication is reported, wrong password?', $status);
}
elseif ($state == 'unregistered')
{
	send_alert_email('CustomerX has Unregistered trunk', $status);
}
elseif ($state == 'failed')
{
	send_alert_email('CustomerX has Registration failed trunk', $status);
}
elseif ($state == 'auth_sent')
{
	send_alert_email('CustomerX has trunk with Auth. Sent status', $status);
}
elseif ($state == 'rejected')
{
	send_alert_email('CustomerX has trunk with Rejected status', $status);
}
else
{
	send_alert_email('CustomerX trunk status is not registered', "$state\r\n\r\n$status");
}

// the function wots_up() parses the output of the asterisk command "sip show registry"
// and compares the content of the "State" column.  Working for Asterisk 1.8, changes in 
// Asterisk output may break the function.
function wots_up()
{
	global $status;
	
	//Prob the trunk registration.
	$status = shell_exec('/usr/sbin/asterisk -rx "sip show registry"');
	
	if (strlen(trim($status)) == 0)
	{
		return 'empty';
	}
	
	$lines = explode("\n", str_replace("\r\n", "\n", trim($status)));

	for ($i = 1; $i < count($lines) - 1; $i++)
	{
		//echo "line $i " .  $lines[$i] . "\n";
		if (strpos($lines[$i], 'Request Sent') !== false)
		{
			return 'requesting';
		}
		elseif(strpos($lines[$i], 'No Authentication') !== false)
		{
			return 'no_auth';
		}
		elseif(strpos($lines[$i], 'Unregistered') !== false)
		{
			return 'unregistered';
		}
		elseif (strpos($lines[$i], 'Failed') !== false)
		{
			return 'failed';
		}
		elseif (strpos($lines[$i], 'Auth. Sent') !== false)
		{
			return 'auth_sent';
		}
		elseif (strpos($lines[$i], 'Rejected') !== false)
		{
			return 'rejected';
		}
		elseif (strpos($lines[$i], 'Timeout') !== false)
		{
			return 'timeout';
		}
		elseif (strpos($lines[$i], 'Unknown') !== false)
		{
			return 'unknown';
		}
		
		$temp = preg_split('/\s+/', $lines[$i]);
		if (!isset($temp[4]))
		{
			return "Can not extract Reg State for this line: " . $lines[$i];
		}
		elseif ($temp[4] <> "Registered")
		{
			return "Unknown Reg state for this line: " . $lines[$i];
		}
	}
	
	return 'ok';
}
///create code here to place phone call with email_content using TTS
function send_alert_email($subject, $email_content = '')
{
	global $report_email;
	mail($report_email, $subject, $email_content);
}

?>