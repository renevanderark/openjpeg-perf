<?php

	require_once('list-dir.php');

	$build = isset($argv[1]) ? $argv[1] : "latest";
	$samples = listDir("samples");
	$resultFile = "out/test/raw/" . time() . ".log";

	@mkdir("out/test/raw", "0777", true);
	touch($resultFile);
	foreach($samples as $sample) {
		$fileMd = json_decode(`export LD_LIBRARY_PATH=\`pwd\`/builds/$build/usr/local/lib; ./opj-md samples/$sample`, true);
		#var_dump($fileMd);
		# ./time_opj_decode_cmd.sh latest   sample1.jp2                     4
		# ./time_opj_decode_cmd.sh latest   sample1.jp2                     0  1
		for($res = 0; $res < $fileMd["num_res"]; $res++) {
			echo "echo \"Decoding full image $sample with opj_decompress at reduction $res\"\n";
			echo "./time_opj_decode_cmd.sh $build $sample $res >> $resultFile\n";
			echo "echo \"Decoding full image $sample with kdu_expand at reduction $res\"\n";
			echo "./time_kdu_decode_cmd.sh $sample $res >> $resultFile\n";
		}
		$nTiles = $fileMd["tw"] * $fileMd["th"];
		for($res = 0; $res < $fileMd["num_res"]; $res++) {
			echo "echo \"Decoding tiles in parallel for $sample with opj_decompress at reduction $res\"\n";
			for($i = 0; $i < $nTiles; $i++) {
				echo "./time_opj_decode_cmd.sh $build $sample $res $i >> $resultFile &\n";
			}
			echo "wait\n";

			echo "echo \"Decoding tiles in parallel for $sample with kdu_expand at reduction $res\"\n";
			for($tx = 0; $tx < $fileMd["tw"]; $tx++)
				for($ty = 0; $ty < $fileMd["th"]; $ty++) {
					$leftPerc = bcdiv(($tx * $fileMd["tdx"]), $fileMd["x1"], 30);
					$wPerc = bcdiv(($fileMd["tdx"]), $fileMd["x1"], 30);
					$topPerc = bcdiv(($ty * $fileMd["tdy"]), $fileMd["y1"], 30);
					$hPerc = bcdiv(($fileMd["tdy"]), $fileMd["y1"], 30);
					$kduRegion = "\"{" . $topPerc . "," . $leftPerc . "},{" . $hPerc . "," . $wPerc . "}\"";
					echo "./time_kdu_decode_cmd.sh $sample $res $kduRegion >> $resultFile &\n";
				}
			echo "wait\n";
		}


	}

