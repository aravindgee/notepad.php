<!DOCTYPE html>
<html>
	<head>
		<title>notepad.php: No-Nonsense Note-taking.</title>
		<style type="text/css">
			body { text-align: left; font-size: 12px; font-family: monospace }
			.textbox { border: 1px solid #C0C0C0; padding: 1px; width: 250px;}
			.textbox:active { background-color: #CCC }
			.textarea { width: 75%;}
			.button { border: 1px solid blue; background-color: #DDD; padding: 5px; margin: 5px; width: 70px }
			.button:active { background-color: #C0C0C0 }
		</style>
		<script type="text/javascript">
			function doInit()
			{ txtTime.value = new Date(); }
		</script>	
	</head>
	<body onLoad="doInit()">
		<form action="notepad.php" method="POST">

<?php

//User Data
$noteTime = "Wed Jul 25 2012 15:11:58 GMT+0530 (IST)";
$noteBody = "thw quick brown foz";
$noteRead = "";
$noteWrite = "C";

if(isset($_POST["txtRead"])) //Perform write operations
{
	$noteTime = isset($_POST["txtTime"])?$_POST["txtTime"]:$noteTime;
	$noteBody = isset($_POST["txtBody"])?$_POST["txtBody"]:$noteBody;
	$noteRead = isset($_POST["txtRead"])?$_POST["txtRead"]:$noteRead;
	$noteWrite = isset($_POST["txtWrite"])?$_POST["txtWrite"]:$noteWrite;
	
	$strOriginal = Array('/\$noteTime = ".*"/','/\$noteBody = "fgfd"/');
	$strReplace = Array('\$noteTime = "'.$noteTime.'"','\$noteBody = "'.$noteBody.'"','\$noteRead = "'.$noteRead.'"','\$noteWrite = "'.$noteWrite.'"');

	$stream = fopen('notepad.php',"r+");
	$fileRead = stream_get_contents($stream);
	$fileWrite = preg_replace($strOriginal, $strReplace, $fileRead, 1);

	//echo "<textarea>".htmlemv Rntities($fileWrite)."</textarea>";

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
		echo "Unable to write to file";
	}

	fclose($stream);
}

/*
preg_match('/\$noteTime = "dfgd"/', $strFile, $match );
unset($strFile);*/

/*
if($noteRead !="" && !isset($_POST['txtRead']))
{
	echo 'Enter "read" password to view read-only note, or enter "write" password for read-write mode.';
	echo 'R<input type="password" name="txtRead" />';
	echo 'W<input type="password" name="txtWrite"/>';
}
else if($noteWrite !="" && isset($_POST['txtWrite']))
{
	if($noteWrite != isset($_POST['txtWrite']))
	*/

echo '<table>';
echo '<tr><td>Time</td><td><input class="textbox" type="text" name="txtTime" id="txtTime" value="'.$noteTime.'" /></td></tr>';
echo '<tr><td>Reading password</td><td><input class="textbox" type="text" name="txtRead" value="'.$noteRead.'"/> Blank to disable</td></tr>';
echo '<tr><td>Writing password</td><td><input class="textbox" type="text" name="txtWrite" value="'.$noteWrite.'"/> Blank to disable</td></tr></table>';
echo '<textarea class="textarea" name="txtBody" rows="25">'.$noteBody.'</textarea><br>';


echo '<input class="button" type="submit" onclick="this.disabled=1;this.form.submit();" value="Submit" />';


?>
	</form>
	</body>
	</html>