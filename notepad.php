<!DOCTYPE html>
<html>
	<head>
		<title>notepad.php: No-Nonsense Note-taking.</title>
		<style type="text/css">
			body { text-align: center; font-size: 1em; margin: auto; padding: 1em; }
			body, input { font-family: Consolas, 'Courier New', FreeMono, monospace; }
			.container { font-size: 1.1em; width: 85%; text-align: left; margin: auto; padding: 2em; border: 0.1em solid #BBB}
			.textbox {  border: 0.1em solid #C0C0C0; padding: 0.2em; width: 20em;}
			.textbox:active, textarea:active { background-color: #DDD }
			.textbox:focus, textarea:focus { border: 0.1em dashed teal }
			.textarea { width: 75%; margin: 1em; padding: 0.5em}
			.button { border: 0.1em solid blue; background-color: #DDD; padding: 0.2em; margin: 0.3em; width: 4em }
			#switch { width: 15em; }
			.button:active { background-color: #C0C0C0 }
			a { text-decoration: none; color: navy}
			a:hover, a:active { color: red }
			.header { background: #000; color: #ccc; padding: 0.9em; margin-bottom: 0.9em; font-size: 1.3em; font-weight: bold; text-align: center}
			label {width:16em; height: 0.9em; margin: 0.4em; padding: 0.4em; display: inline-block}

		</style>
		<script type="text/javascript">
			function doInit()
			{ txtTime.value = new Date(); }
		</script>	
	</head>
	<body onLoad="doInit()">
		<div class="container">
		<form action="" method="POST">

<?php

//The data that is 'stored' in this file
$noteTime = "Sun Mar 10 2013 21:49:03 GMT+0530 (IST)";
$noteBody = "Hello World!";
$noteReadPass = "read";
$noteWritePass = "write";

//Mode: n = none, r = read, w = write
$noteMode = "n"; 

//Data sent by the user
$usrReadPass = "";
$usrWritePass = "";

//If read password has been sent by the client, validate it.
if(isset($_POST["btnRead"]))
{
	$usrReadPass = isset($_POST["pwdReadPass"])?$_POST["pwdReadPass"]:"";
	if($usrReadPass == $noteReadPass)
		$noteMode = "r";
}

//If write password has been sent by the client, validate it.
else if(isset($_POST["btnWrite"]))
{
	$usrWritePass = isset($_POST["pwdWritePass"])?$_POST["pwdWritePass"]:"";
	if($usrWritePass == $noteWritePass)
		$noteMode = "w";
}

//If user is converting read to write mode, authenticate read password.
if(isset($_POST["hdnReadPass"]))
{
	$hdnReadPass = isset($_POST["hdnReadPass"])?$_POST["hdnReadPass"]:"";
	if($hdnReadPass != $noteReadPass)
	{
		echo 'Authentication Error. Please <a href=""/>refresh</a> and try again.';
		echo '</form></div></body></html>';
		exit;
	}
	else if($noteWritePass == "") // If no write password is set, set to write mode straightaway.
		$noteMode = "w";
}	

//If passwords have been set, validate them or send authentication notice.
if($noteMode == "n" && !isset($_POST["txtWrite"]))
{
	if(isset($_POST["hdnReadPass"]) && $noteWritePass != "") //User is converting read to write mode AND there is a write pass set.
	{
		echo '<div class="header">Notepad &lowast; Authentication Required</div>';
		echo 'Write: <input type="password" class="textbox" name="pwdWritePass" /> <input class="button" type="submit" name="btnWrite" value="GO" />';
		echo '</form></div></body></html>';
		exit;
	}
	if($noteReadPass != "") //Authentication message for reading as well as writing.
	{
		echo '<div class="header">Notepad &lowast; Authentication Required</div>';
		echo '<label>Read</label><input type="password" class="textbox" name="pwdReadPass" /> <input class="button" type="submit" name="btnRead" value="GO" /><br>';
		if($noteWritePass != "")
		{
			echo '</form><form action="" method="POST">';
			echo '<label>Write</label><input type="password" class="textbox" name="pwdWritePass" /> <input class="button" type="submit" name="btnWrite" value="GO" />';
		}
		echo '</form></div></body></html>';
		exit;
	}
	else 
	{
		if($noteWritePass == "")
			$noteMode = "w";
		else
			$noteMode = "r";
	}
}

//This varible stores the note text to be displayed in textarea
$noteBodyDisplay = "";


if(isset($_POST["hdnWritePass"])) //Perform write operations if main form has been submitted
{
	//Check for possible hacking.
	$hdnWritePass = $_POST["hdnWritePass"];
	if($hdnWritePass != $noteWritePass)
	{
		echo 'Authentication Error. Please <a href=""/>refresh</a> and try again.';
		echo '</form></div></body></html>';
		exit;
	}

	//Get data from user-submited form
	$noteTime = isset($_POST["txtTime"])?$_POST["txtTime"]:$noteTime;
	$noteBody = isset($_POST["txtBody"])?$_POST["txtBody"]:$noteBody;
	$noteReadPass = isset($_POST["txtRead"])?$_POST["txtRead"]:$noteReadPass;
	$noteWritePass = isset($_POST["txtWrite"])?$_POST["txtWrite"]:$noteWritePass;

	//Store note body for display
	$noteBodyDisplay = $noteBody;

	//Escape special characters
	$noteTime = addslashes($noteTime);
	$noteBody = addslashes($noteBody);
	$noteReadPass = addslashes($noteReadPass);
	$noteWritePass = addslashes($noteWritePass);
	$noteBody = preg_replace("/(\r\n|\r|\n)/", "\\n", $noteBody);
	
	//Replacement expressions for user data - to be applied to this file for 'saving'
	$strOriginal = Array('/\$noteTime = ".*";/','/\$noteBody = ".*";/','/\$noteReadPass = ".*";/','/\$noteWritePass = ".*";/');
	$strReplace = Array('\$noteTime = "'.$noteTime.'";','\$noteBody = "'.$noteBody.'";','\$noteReadPass = "'.$noteReadPass.'";','\$noteWritePass = "'.$noteWritePass.'";');

	//Do file I/O
	$stream = fopen(basename(__FILE__),"r+");
	$fileRead = stream_get_contents($stream);
	$fileWrite = preg_replace($strOriginal, $strReplace, $fileRead, 1);

	//Commit Changes
	if(flock($stream, LOCK_EX))
	{
		ftruncate($stream, 0);
		rewind($stream);
		fwrite($stream, $fileWrite);
		fflush($stream);
		flock($stream, LOCK_UN); 
	} 
	else
	{
		echo 'Unable to write to file.';
		echo '</form></div></body></html>';
		exit;
	}

	fclose($stream);
	$noteMode = 'w';
	$usrWritePass = $hdnWritePass;
}

//If note mode is not set to read or write - well, something is wrong.
if($noteMode != 'r' && $noteMode != 'w')
{
	echo "Error";
	echo '</form></div></body></html>';
	exit;
}

//Some customization depending upon the mode.
$isReadOnly = ($noteMode=='r')?'readonly':'';
$txtModeName = ($noteMode == 'r')?"Read":"Write";

echo '<div class="header">Notepad &lowast; '.$txtModeName.' mode &lowast; Last Save: '.stripslashes($noteTime).'</div>';

echo '<label>Time</label><input class="textbox" type="text" name="txtTime" id="txtTime" value="'.stripslashes($noteTime).'" '.$isReadOnly.' /><br>';

//If in write mode, show password fields for editing
if($noteMode == "w")
{
	echo '<label>Reading password</label><input class="textbox" type="text" name="txtRead" value="'.stripslashes($noteReadPass).'"/><label><i>[Blank to disable]</i></label><br>';
	echo '<label>Writing password</label><input class="textbox" type="text" name="txtWrite" value="'.stripslashes($noteWritePass).'"/><label><i>[Blank to disable]</i></label><br>';
}

if ($noteBodyDisplay == "")
	$noteBodyDisplay = preg_replace("/\\n/", "\n", stripslashes($noteBody));

echo '<textarea class="textarea" name="txtBody" rows="25" '.$isReadOnly.'>'.$noteBodyDisplay.'</textarea><br>';

//If in write mode, show submit button and add an hidden feild with write password (for write auth). 
if($noteMode == "w")
{
	echo '<input type="hidden" name="hdnWritePass" value="'.$usrWritePass.'" /><br>';
	echo '<input class="button" type="submit" name="btnSave" onclick="this.disabled=1;this.form.submit();" value="Save" />';
}
else // If in read mode, display button to convert to write mode and hidden field for authenticating read password.
{
	echo '<input type="hidden" name="hdnReadPass" value="'.$usrReadPass.'" /><br>';
	echo '<input class="button" type="submit" name="btnSwitch" id="switch" value="Switch to write mode" />';
}

?>
	</form>
	</div>
	</body>
	</html>