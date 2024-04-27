<?php
$usefunc = 1;
$deltafak = 1000;
$boostfak = 0;

$fileContents = file_get_contents("variables.csv");
if($fileContents)
{
	$values = explode(";", $fileContents);
	$usefunc = $values[0];
	$deltafak = $values[1];
	$boostfak = $values[2];
}

$histories = [];
$historiesLoad = file_get_contents("history.csv");
if($historiesLoad)
{
	$histories = explode(PHP_EOL, $historiesLoad);
	$histories = array_slice($histories, -15, 15, true);
}

$readValue = 0;
$outputValue = 0;
$voltage1 = 0;
$voltage2 = 0;

$isAdmin = $_GET["admin"]=="ytFaMUnQ5HVu98Z4tGJcuMJGf69Q";
if($_POST && $isAdmin)
{
	$previousDeltafak = $deltafak;
	if($_POST["usefunc"] && $_POST["usefunc"] != $usefunc)
	{
		$usefunc = $_POST["usefunc"];
		if($usefunc == 1)
		{
			$deltafak = 1000;
		} else if($usefunc == 2)
		{
			$deltafak = 1500;
		} else if($usefunc == 3)
		{
			$deltafak = 0;
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
	
	$myfile = fopen("variables.csv", "w") or die("Unable to open file!");
	fwrite($myfile, $usefunc.";".$deltafak.";".$boostfak);
	fclose($myfile);
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
	if($_GET["voltage1"])
	{
		$voltage1 = $_GET["voltage1"];
	}
	if($_GET["voltage2"])
	{
		$voltage2 = $_GET["voltage2"];
	}
	
	$myfile = fopen("history.csv", "a") or die("Unable to open file!");
	fwrite($myfile, $readValue.";".$outputValue.";".$voltage1.";".$voltage2."\r\n");
	fclose($myfile);
}

if($_GET && $_GET["machine"])
{
	echo str_pad($deltafak, 10);
	echo str_pad($usefunc, 10);
	echo str_pad($boostfak, 10);
	exit;
}
else
{
	?>
	<table>
	<tr>
	<th>
	delta faktor
	</th>
	<th>
	use function (see below)
	</th>
	<th>
	boost faktor
	</th>
	</tr>
	<tr>
	<td><?php echo $deltafak; ?></td>
	<td><?php echo $usefunc; ?></td>
	<td><?php echo $boostfak; ?></td>
	</tr>
	</table>
	<?php
}
?>
<table>
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
</tr>
<?php
for($i = 0; $i < sizeof($histories); $i++)
{
	$currentOutput = explode(";", $histories[$i]);
	?>
	<tr>
		<td><?php echo $currentOutput[0]; ?></td>
		<td><?php echo $currentOutput[1]; ?></td>
		<td><?php echo $currentOutput[2]; ?></td>
		<td><?php echo $currentOutput[3]; ?></td>
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
		<input type="hidden" id="admin" name="admin" value="ytFaMUnQ5HVu98Z4tGJcuMJGf69Q">
		<label for="deltafaktor">deltafaktor</label><br>
		<input type="number" id="deltafaktor" name="deltafaktor" value="<?php echo $deltafak ?>"><br><br>
		<label for="usefunc">use function</label><br>
		<select id="usefunc" name="usefunc" value="<?php echo $usefunc ?>">
			<option value="1" <?php if($usefunc == 1){ echo "selected";} ?>>pwmValue = x²/deltafaktor + boostfaktor (deltafaktor default 1000)</option>
			<option value="2" <?php if($usefunc == 2){ echo "selected";} ?>>pwmValue = (x*1000/deltafaktor) * boostfaktor (deltafaktor default 1500)</option>
			<option value="3" <?php if($usefunc == 3){ echo "selected";} ?>>pwmValue = x</option>
			<option value="4" <?php if($usefunc == 4){ echo "selected";} ?>>pwmValue = ((x-500)^3/deltafaktor) + 500 (deltafaktor default 250.000)</option>
		</select><br><br>
		<label for="deltafaktor">boostfaktor</label><br/>
		<input type="number" id="boostfaktor" name="boostfaktor" value="<?php echo $boostfak ?>"><br><br>
		<input type="submit" value="submit" />
	<?php
}
?>