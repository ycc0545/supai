<?php
	$pass = $_GET['pass'];
	if ($pass == date("Ymd"))
	{
		header("Location: index.php?g=Admin&m=Admin&a=index");
	}
	else
	{
		header("Location: index.php");
	}
?>