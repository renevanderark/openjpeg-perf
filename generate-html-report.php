<?php
	$resMap = [];
	$opjBuild = false;
	while($line = fgets(STDIN)) {
		if(trim($line) === "") { continue; }
		list($decoder, $build, $sample, $res, $tile, $time) = preg_split("/;/", trim($line));

		if($opjBuild === false && trim($build) !== "") { $opjBuild = $build; }

		if(!isset($resMap[$sample])) { $resMap[$sample] = []; }
		if(!isset($resMap[$sample][$res])) { $resMap[$sample][$res] = []; }
		if(!isset($resMap[$sample][$res][$decoder])) { $resMap[$sample][$res][$decoder] = []; }

		if(!isset($resMap[$sample][$res][$decoder]["tiles"])) { $resMap[$sample][$res][$decoder]["tiles"] = []; }

		if(trim($tile) === "") {
			$resMap[$sample][$res][$decoder]["full-seq"] = $time;
		} elseif(trim($tile) === "full-async") {
			$resMap[$sample][$res][$decoder]["full-async"] = $time;
		} else {
			$resMap[$sample][$res][$decoder]["tiles"][intval($tile)] = $time;
		}
	}

	var_dump($resMap);