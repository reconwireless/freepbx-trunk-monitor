Installing TrunkAlarm<br>
<h5>FreePBX Trunk Monitoring (v0001)</h5>
This is an experimental trunk alarm as a personal project of reconwireless use at your own risk.<br>reconwireless.com<br>
<?php
if ( (isset($amp_conf['ASTVARLIBDIR'])?$amp_conf['ASTVARLIBDIR']:'') == '') {
	$astlib_path = "/var/lib/asterisk";
} else {
	$astlib_path = $amp_conf['ASTVARLIBDIR'];
}
// Need to add check here to check existing mysql table, get rid of zipcode and add wgroundkey
// add primary key index 


?><br>Installing Default Configuration values.<br>
<?php

$sql ="INSERT INTO trunkalarmoptions (engine, pbxname, trunkemail, trunkalarmext, trunkalarmnumber) ";
$sql .= "               VALUES ('trunkalarm-nocall', , , ,         '')";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create default values in `trunkalarmoptions` table: " . $check->getMessage() .  "\n");
}

// Add dialplan include to asterisk conf file
$filename = '/etc/asterisk/extensions_custom.conf';
$includecontent = "#include custom_trunkalarm.conf\n";

// First we need to look for existing occurances of the include line from past sloppy uninstall/upgrade and remove all of them
function replace_file($path, $string, $replace)
{
    set_time_limit(0);
    if (is_file($path) === true)
    {
        $file = fopen($path, 'r');
        $temp = tempnam('./', 'tmp');
        if (is_resource($file) === true)
        {
            while (feof($file) === false)
            {
                file_put_contents($temp, str_replace($string, $replace, fgets($file)), FILE_APPEND);
            }
            fclose($file);
        }
        unlink($path);
    }
    return rename($temp, $path);
}

replace_file($filename, $includecontent, '');

// Now add back include line
if (is_writable($filename)) {
 
    if (!$handle = fopen($filename, 'a')) {
         echo "Cannot open file ($filename)";
         exit;
    }
    // Write $somecontent to our opened file.
    if (fwrite($handle, $includecontent) === FALSE) {
        echo "Cannot write to file ($filename)";
        exit;
    }
    echo "<br>Success, wrote ($includecontent)<br> to file ($filename)<br><br>";

    fclose($handle);

} else {
    echo "The file $filename is not writable";
}
?>Verifying / Installing cronjob into the FreePBX cron manager.<br>
<?php
$sql = "SELECT * FROM `cronmanager` WHERE `module` = 't' LIMIT 1;";

$res = $db->query($sql);

if($res->numRows() != 2)
{
$sql = "INSERT INTO	cronmanager (module,id,time,freq,command) VALUES ('trunkalarm','every_day',23,24,'/usr/bin/find /var/lib/asterisk/sounds/tts -name \"*.wav\" -mtime +1 -exec rm {} \\\;')";

$sql = "INSERT INTO	cronmanager (module,id,time,freq,command) VALUES ('trunkalarm',*,15,*,'/asterisk/agi-bin/monitor_trunk.php \\\;')";

$check = $db->query($sql);
if (DB::IsError($check))
	{
	die_freepbx( "Can not create values in cronmanager table: " . $check->getMessage() .  "\n");
	}
}
?>Verifying / Creating TTS Folder.<br>
<?php
$parm_tts_dir = '/var/lib/asterisk/sounds/tts';
if (!is_dir ($parm_tts_dir)) mkdir ($parm_tts_dir, 0775);
?>Creating Feature Code.<br>
<?php
// Register FeatureCode - Trunk Monitor;
$fcc = new featurecode('trunkalarm', 'trunkalarm');
$fcc->setDescription('Trunk Alarm');
$fcc->setDefault('*878625');
$fcc->update();
unset($fcc);
?>
