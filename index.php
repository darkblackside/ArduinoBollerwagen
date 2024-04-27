<?php
$usefunc = 1;
$deltafak = 1000;
$boostfak = 0;
$updateSettingsSeconds = 20;

$fileContents = file_get_contents("variables.csv");
if($fileContents)
{
	$values = explode(";", $fileContents);
	$usefunc = $values[0];
	$deltafak = $values[1];
	$boostfak = $values[2];
	$updateSettingsSeconds = $values[3];
}

$histories = [];
$historiesLoad = file_get_contents("history.csv");
if($historiesLoad)
{
	$histories = explode(PHP_EOL, $historiesLoad);
}

$readValue = 0;
$outputValue = 0;
$voltage1 = 0;
$voltage2 = 0;

$isAdmin = $_GET["admin"]=="ytFaMUnQ5HVu98Z4tGJcuMJGf69Q";
if($_POST && $isAdmin)
{
	if($_POST["reset"]=="öaoiijrpij293898jhbagioeoi")
	{
		$myfile = fopen("history.csv", "w") or die("Unable to open file!");
		fwrite($myfile, "read;set;voltage1;voltage2;date;time;useFunc;deltaFaktor;boostFaktor;updateSeconds;failedUpdatesCount".PHP_EOL);
		fclose($myfile);
		
		$usefunc = 1;
		$deltafak = 1000;
		$boostfak = 0;
		$updateSettingsSeconds = 20;
		
		$myfile = fopen("variables.csv", "w") or die("Unable to open file!");
		fwrite($myfile, $usefunc.";".$deltafak.";".$boostfak.";".$updateSettingsSeconds);
		fclose($myfile);
		
		echo "everything was reset. site will be reloaded soon";
		header("Refresh:3");
		
		exit;
	}
	$previousDeltafak = $deltafak;
	if($_POST["usefunc"] && $_POST["usefunc"] != $usefunc)
	{
		$usefunc = $_POST["usefunc"];
		if($usefunc == 1)
		{
			$deltafak = 1000;
			$boostfak = 0;
		} else if($usefunc == 2)
		{
			$deltafak = 1500;
			$boostfak = 1;
		} else if($usefunc == 3)
		{
			$deltafak = 0;
			$boostfak = 1;
		} else if($usefunc == 4)
		{
			$deltafak = 250000;
		}
	}
	if($_POST["deltafaktor"] && $_POST["deltafaktor"] != $previousDeltafak)
	{
		$deltafak = $_POST["deltafaktor"];
	}
	if($_POST["boostfaktor"] && $_POST["boostfaktor"] != $boostfak)
	{
		$boostfak = $_POST["boostfaktor"];
	}
	if($_POST["updateSeconds"] && $_POST["updateSeconds"] != $boostfak)
	{
		$updateSettingsSeconds = $_POST["updateSeconds"];
	}
	
	$myfile = fopen("variables.csv", "w") or die("Unable to open file!");
	fwrite($myfile, $usefunc.";".$deltafak.";".$boostfak.";".$updateSettingsSeconds);
	fclose($myfile);
	
	header("Refresh:0");
}
if($_GET && ($_GET["readValue"] || $_GET["outputValue"] || $_GET["voltage1"] || $_GET["voltage2"])) {
	if($_GET["readValue"])
	{
		$readValue = $_GET["readValue"];
	}
	if($_GET["outputValue"])
	{
		$outputValue = $_GET["outputValue"];
	}
	if($_GET["useFunc"])
	{
		$usefuncFromUpdate = $_GET["useFunc"];
	}
	if($_GET["deltaFak"])
	{
		$deltafakFromUpdate = $_GET["deltaFak"];
	}
	if($_GET["boostFak"])
	{
		$boostfakFromUpdate = $_GET["boostFak"];
	}
	if($_GET["voltage1"])
	{
		$voltage1 = $_GET["voltage1"];
	}
	if($_GET["voltage2"])
	{
		$voltage2 = $_GET["voltage2"];
	}
	if($_GET["failedUpdates"])
	{
		$failedUpdates = $_GET["failedUpdates"];
	}
	if($_GET["updateSeconds"])
	{
		$updateSeconds = $_GET["updateSeconds"];
	}
	
	$myfile = fopen("history.csv", "a") or die("Unable to open file!");
	fwrite($myfile, $readValue.";".$outputValue.";".$voltage1.";".$voltage2.";".date("d.m.Y").";".date("H:i:s").";".$usefuncFromUpdate.";".$deltafakFromUpdate.";".$boostfakFromUpdate.";".$updateSeconds.";".$failedUpdates.PHP_EOL);
	fclose($myfile);
}

if($_GET && $_GET["machine"])
{
	echo str_pad($deltafak, 10);
	echo str_pad($usefunc, 10);
	echo str_pad($boostfak, 10);
	echo str_pad($updateSettingsSeconds, 10);
	exit;
}
else
{
	?>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<style>
	  body, html {
		margin: 0;
		padding: 0;
		height: 100%;
	  }
	  .container-my {
		display: flex;
		flex-direction: row; /* default layout - horizontal */
	  }
	  .iframe-container {
		flex: 1;
		height: 100%;
	  }
	  iframe {
		width: 400px;
		height: 350px;
		border: none;
	  }
	  @media (max-width: 1600px) {
		/* For mobile devices, switch to vertical layout */
		.container-my {
		  flex-flow: row wrap;
		}
	  }
	</style>
	<script>
	
	</script>
	<h1>current settings</h1>
	<table border="1">
	<tr>
	<td>
	use function (see below)
	</td>
	<td><?php echo $usefunc; ?></td>
	</tr>
	<tr>
	<td>
	delta faktor
	</td>
	<td><?php echo $deltafak; ?></td>
	</tr>
	<tr>
	<td>
	boost faktor
	</td>
	<td><?php echo $boostfak; ?></td>
	</tr>
	<tr>
	<td>
	update every x seconds
	</td>
	<td><?php echo $updateSettingsSeconds; ?></td>
	</tr>
	</table>
	<?php
}
?>
<h1>history values</h1>
<table border="1">
<tr>
<th>
read value (from resistor, transformed)
</th>
<th>
set value for PWM
</th>
<th>
voltage 1
</th>
<th>
voltage 2
</th>
<th>
Date
</th>
<th>
Time
</th>
<th>
use function
</th>
<th>
delta faktor
</th>
<th>
boost faktor
</th>
<th>
update every x seconds
</th>
<th>
failed update calls
</th>
</tr>
<?php
$historiesLast15 = array_slice($histories, -15, 14);
for($i = 0; $i < sizeof($historiesLast15); $i++)
{
	$currentOutput = explode(";", $historiesLast15[$i]);
	?>
	<tr>
		<td><?php echo $currentOutput[0]; ?></td>
		<td><?php echo $currentOutput[1]; ?></td>
		<td><?php echo $currentOutput[2]; ?></td>
		<td><?php echo $currentOutput[3]; ?></td>
		<td><?php if($currentOutput[4]) { echo $currentOutput[4]; } ?></td>
		<td><?php if($currentOutput[5]) { echo $currentOutput[5]; } ?></td>
		<td><?php if($currentOutput[6]) { echo $currentOutput[6]; } ?></td>
		<td><?php if($currentOutput[7]) { echo $currentOutput[7]; } ?></td>
		<td><?php if($currentOutput[8]) { echo $currentOutput[8]; } ?></td>
		<td><?php if($currentOutput[9]) { echo $currentOutput[9]; } ?></td>
		<td><?php if($currentOutput[10]) { echo $currentOutput[10]; } ?></td>
	</tr>
	<?php
}
?>
</table>
<?php

if($isAdmin)
{
	echo "<h1>admin area</h1>";
	?>
	<form method="POST">
		<label for="deltafaktor">deltafaktor</label><br>
		<input type="number" id="deltafaktor" name="deltafaktor" value="<?php echo $deltafak ?>"><br><br>
		<label for="usefunc">use function</label><br>
		<select id="usefunc" name="usefunc" value="<?php echo $usefunc ?>">
			<option value="1" <?php if($usefunc == 1){ echo "selected";} ?>>pwmValue = x²/deltafaktor + boostfaktor (deltafaktor default 1000)</option>
			<option value="2" <?php if($usefunc == 2){ echo "selected";} ?>>pwmValue = (x*1000/deltafaktor) * boostfaktor (deltafaktor default 1500)</option>
			<option value="3" <?php if($usefunc == 3){ echo "selected";} ?>>pwmValue = x * boostfaktor</option>
			<option value="4" <?php if($usefunc == 4){ echo "selected";} ?>>pwmValue = ((x-500)^3/deltafaktor) + 500 (deltafaktor default 250.000)</option>
		</select><br><br>
		<label for="deltafaktor">boostfaktor</label><br/>
		<input type="number" id="boostfaktor" name="boostfaktor" value="<?php echo $boostfak ?>"><br><br>
		<label for="deltafaktor">update every x seconds</label><br>
		<input type="number" id="updateSeconds" name="updateSeconds" value="<?php echo $updateSettingsSeconds ?>"><br><br>
		<input type="submit" value="submit" />
	</form>
	<?php
}
?>

<h2>PWM graphs</h2>
<div class="container-my">
  <div class="iframe-container">
	Function 1<br/>
    <iframe src="barplot.php?func=1"></iframe>
  </div>
  <div class="iframe-container">
	Function 2<br/>
    <iframe src="barplot.php?func=2"></iframe>
  </div>
  <div class="iframe-container">
	Function 3<br/>
    <iframe src="barplot.php?func=3"></iframe>
  </div>
  <div class="iframe-container">
	Function 4<br/>
    <iframe src="barplot.php?func=4"></iframe>
  </div>
</div>
<?php
if($isAdmin)
{
	?>
	<form method="POST">
		<input type="hidden" name="reset" id="reset" value="öaoiijrpij293898jhbagioeoi" />
		<input type="submit" value="reset everything" />
	</form>
	<?php
}
?>