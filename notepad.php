<!DOCTYPE html>
<html>
	<head>
		<title>notepad.php: No-Nonsense Note-taking.</title>
		<style type="text/css">
			body { text-align: center; font-size: 11px; margin: auto; padding: 1em; }
			body, input { font-family: Consolas, 'Courier New', FreeMono, monospace; }
			.container { font-size: 1.1em; width: 85%; text-align: left; margin: auto; padding: 2em; border: 0.1em solid #BBB}
			.textbox {  border: 0.1em solid #C0C0C0; padding: 0.2em; width: 20em;}
			.textbox:active, textarea:active { background-color: #DDD }
			.textbox:focus, textarea:focus { border: 0.1em dashed teal }
			.textarea { width: 75%; margin: 1em; padding: 0.5em}
			.button { border: 0.1em solid blue; background-color: #DDD; padding: 0.2em; margin: 0.3em; width: 4em }
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

//Data 'stored' in this file
$noteTime = "Sun Feb 10 2013 21:52:05";
$noteBody = "Hello World!";
$noteReadPass = "";
$noteWritePass = "";

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

//If passwords have been set, and no password validation has been done, send authentication notice.
if($noteMode == "n" && !isset($_POST["txtWrite"]))
{
	echo '<div class="header">Notepad &mid; Authentication Required</div>';

	if(isset($_GET["write"]) && $noteWritePass != "") //User is converting read to write mode
	{
		echo 'Write: <input type="password" class="textbox" name="pwdWritePass" /> <input class="button" type="submit" name="btnWrite" value="GO" />';
		echo '</form></div></body></html>';
		exit;
	}
	if($noteReadPass != "") //Authentication for reading as well as writing.
	{
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

if(isset($_POST["txtWrite"])) //Perform write operations if main form has been submitted
{
	//Check for possible hacking.
	$hdnWritePass = isset($_POST["hdnWritePass"])?$_POST["hdnWritePass"]:$hdnWritePass;
	if($hdnWritePass != $noteWritePass)
	{
		echo 'Authentication Error. Please <a href=""/>refresh</a> and try again.';
		echo '</form></div></body></html>';
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
}

//Some customization depending upon the mode.
$isReadOnly = ($noteMode=='r')?'readonly':'';
$txtModeName = ($noteMode == 'r')?"Read":"Write";

echo '<div class="header">Notepad &mid; '.$txtModeName.' mode &mid; Last Save: '.stripslashes($noteTime).'</div>';

echo '<label>Time</label><input class="textbox" type="text" name="txtTime" id="txtTime" value="'.stripslashes($noteTime).'" '.$isReadOnly.' /><br>';

//If in write mode, show password feilds for editing
if($noteMode == "w")
{
	echo '<label>Reading password</label><input class="textbox" type="text" name="txtRead" value="'.stripslashes($noteReadPass).'"/><label><i>[Blank to disable]</i></label><br>';
	echo '<label>Writing password</label><input class="textbox" type="text" name="txtWrite" value="'.stripslashes($noteWritePass).'"/><label><i>[Blank to disable]</i></label><br>';
}

if ($noteBodyDisplay == "")
	$noteBodyDisplay = preg_replace("/\\n/", "\n", stripslashes($noteBody));

echo '<textarea class="textarea" name="txtBody" rows="25" '.$isReadOnly.'>'.$noteBodyDisplay.'</textarea><br>';

//If in write mode, show submit button and add an hidden feild with write password. 
if($noteMode == "w")
{
	echo '<input type="hidden" name="hdnWritePass" value="'.$usrWritePass.'" /><br>';
	echo '<input class="button" type="submit" onclick="this.disabled=1;this.form.submit();" value="Save" />';
}
else // If in read mode, display link to convert to write mode.
	echo '<a href="?write=y" class="button"/>Switch to write mode</a>';
?>
	</form>
	</div>
	</body>
	</html>