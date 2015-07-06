<?php
	function listDir($path) {
		$list = [];
		$dh = opendir($path);
		if($dh) {
			while($file = readdir($dh)) {
				if($file === "." || $file === "..") { continue; }
				$list[] = $file;
			}
			closedir($dh);
		}
		sort($list);
		return array_reverse($list);
	}