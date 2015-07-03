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
?>

<table>
<thead>
	<tr>
		<th>Sample file</th>
		<th>Resolution reduction level</th>
		<th>Decoder</th>
		<th>Full image (ms)</th>
		<th>Avg/tile parallel (ms)</th>
		<th>Full image parallel tiles (ms)</th>
	</tr>
</thead>
<tbody>
	<?php foreach($resMap as $sample => $sampleMap): ?>
		<?php foreach($sampleMap as $res => $resolutionMap): ?>
			<?php foreach($resolutionMap as $decoder => $decoderMap): ?>
				<tr>
					<td><?php echo $sample; ?></td>
					<td><?php echo $res; ?></td>
					<td><?php echo $decoder; ?></td>
					<td><?php echo $decoderMap["full-seq"]; ?></td>
					<td><?php echo bcdiv(array_sum($decoderMap["tiles"]), count($decoderMap["tiles"]), 1); ?></td>
					<td><?php echo $decoderMap["full-async"]; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
	<?php endforeach; ?>
</tbody>
</table>
