<?php
function unlink_dir($path)
{
	$_path = realpath($path);
	if (!file_exists($_path))
	{
		return false;
	}
	if (is_dir($_path))
	{
		$list = scandir($_path);
		foreach ($list as $v)
		{
			if ($v=='.'||$v=='..') continue;
			$_paths=$_path.'/'.$v;
			if (is_dir($_paths))
			{
				unlink_dir($_paths);
			} 
			elseif (unlink($_path) === false)
			{
				return false;
			}
		}
		return true;
	}
	return !is_file($_path) ? false : unlink($_path);
}
unlink_dir('./test');
?>