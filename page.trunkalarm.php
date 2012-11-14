<?php
//
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

// check to see if user has automatic updates enabled
$cm =& cronmanager::create($db);
$online_updates = $cm->updates_enabled() ? true : false;

// check if new version of module is available
if ($online_updates && $foo = trunkalarm_vercheck()) {
	print "<br>A <b>new version</b> of the Trunk Monitor module is available from the <a target='_blank' href='http://github.com/reconwireless/freepbx-trunk-monitor/downloads'>Reconwireless Repository on github</a><br>";
}

//tts_findengines()
if(count($_POST)){
	trunkalarmoptions_saveconfig();
}
	$date = trunkalarmoptions_getconfig();
	$selected = ($date[0]);

//  Get current featurecode from FreePBX registry
$fcc = new featurecode('trunkalarm', 'trunkalarm');
$featurecode = $fcc->getCodeActive(); 

?>
<form method="POST" action="">
	<br><h2><?php echo _("U.S. Tide by City")?><hr></h5></td></tr>
Tide by City allows you to retrieve current tide information from any touchtone phone using nothing more than your PBX connected to the Internet.  When prompted you key your U.S. Zip Code, the report is downloaded, converted to an audio file, and played back to you.<br><br>
Current tide conditions and/or forecast for the chosen Zip Code will then will be retrieved from the selected service using the selected text-to-speech engine. <br><br>
The feature code to access this service is currently set to <b><?PHP print $featurecode; ?></b>.  This value can be changed in Feature Codes. <br>

<br><h5>User Data:<hr></h5>
Select the Text To Speach engine and Forecast source combination you wish the Tide by City program to use.<br>The module does not check to see if the selected TTS engine is present, ensure to choose an engine that is installed on the system.<br><br>
<a href="#" class="info">Choose a service and engine:<span>Choose from the list of supported TTS engines and Tide services</span></a>

<select size="1" name="engine">
<?php
echo "<option".(($date[0]=='trunkalarm-nocall')?' selected':'').">trunkalarm-nocall</option>\n";
echo "<option".(($date[0]=='trunkalarm-internal-flite')?' selected':'').">trunkalarm-internal-flite</option>\n";
echo "<option".(($date[0]=='trunkalarm-external-flite')?' selected':'').">trunkalarm-external-flite</option>\n";
echo "<option".(($date[0]=='trunkalarm-both-flite')?' selected':'').">trunkalarm-both-flite</option>\n";
?>
</select>
<br><a href="#" class="info">PBX Name:<span>What would you like to call this PBX</span></a>
<input type="text" name="pbxname" size="40" value="<?php echo $pbxname[1]; ?>">  <a href="javascript: return false;" class="info"> 
<br><a href="#" class="info">Trunk Alarm Reports Email:<span>Input email address for trunk alarm delivery</span></a>
<input type="text" name="trunkemail" size="40" value="<?php echo $trunkemail[1]; ?>">  <a href="javascript: return false;" class="info"> 
<br><a href="#" class="info">Trunk Alarm Extension:<span>Input Internal Extension to be dialed if trunk fails</span></a>
<input type="text" name="trunkalarmext" size="15" value="<?php echo $trunkext[1]; ?>">  <a href="javascript: return false;" class="info"> 
<br><a href="#" class="info">Trunk Alarm Number:<span>Input External Number to be dialed if trunk fails</span></a>
<input type="text" name="trunkalarmnumber" size="15" value="<?php echo $trunknumber[1]; ?>">  <a href="javascript: return false;" class="info"> 
<br><br>key:<br>
<b>nocall</b> - No Trunk Alert Calls will be placed<br>
<b>internal</b> - Trunk Alert Calls will be placed to the internal extension specified<br>
<b>external</b> - Trunk Alert Calls will be placed to the external extension specified (assuming there is a working outbound trunk)<br>
<b>both</b> - Trunk Alert Calls will be placed to both the internal and external numbers specified<br>
		
<br><br><input type="submit" value="Submit" name="B1"><br>

<center><br>
The module is designed for the personal testing and use of Reconwireless. Support, documentation and current versions are available at the trunk-monitor module page on the <a target="_blank" href="https://github.com/reconwireless/freepbx-trunk-monitor">reconwireless dev site</a></center>
<?php


?>