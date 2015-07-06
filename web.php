<?php
	require_once('list-dir.php');

	if(isset($_POST['newbuild'])) {
		`./pull-and-build.sh > build.log 2>&1 &`;
		header("HTTP/1.1 200 OK");
		exit();
	} elseif(isset($_POST['runtests']) && isset($_POST['build']) ) {
		$build = $_POST['build'];
		`./execute_tests.sh $build > build.log 2>&1 &`;
		header("HTTP/1.1 200 OK");
		exit();
	} elseif(isset($_POST['spec'])) {
		if(key($_POST['spec']) === 'upload') {
			move_uploaded_file($_FILES["upload"]["tmp_name"], "./samples/" . $_FILES["upload"]["name"]);
			header("Location: /");
		}
		exit();
	} elseif (isset($_GET['tail'])) {
		$file =  $_GET['tail'];
		$tail = `tail -10000 $file 2> /dev/null`;
		echo $tail;
		exit();
	} elseif (isset($_GET['report'])) {
		$file = $_GET['report'];
		header("HTTP/1.1 200 OK");
		header("Content-type: text/html");

		echo file_get_contents($file);
		exit();
	}

	@$builds = listDir("builds");
	@$samples = listDir("samples");
	@$reports = listDir("out/test/html");
?>

<html>
	<head>
		<style type="text/css">
			body, html, table {
				font-size: 12px;
				font-family: sans-serif;
				padding: 0;
				margin: 0;
			}
			th {
				text-align: left;
			}
		 	pre {
		 		height: 90%; 
		 		overflow: auto
		 	}
		 	#logs {
		 		float: right;
		 		width: 40%;
		 	}
		 	#left {
		 		padding-left: 20px;
		 		width: calc(60% - 20px);
		 	}
		 	a {
		 		color: blue;
		 	}

		 	#result-container td {
		 		text-align: right;
		 	}

			table {
			    border-collapse: collapse;
			}

			td {
			    border-top: 1px solid #aaa;
			    border-bottom: 1px solid #ddd;
			    border-right: 1px solid #ddd;
			    padding-right: 4px;
			}
			#result-container td:nth-child(1) {
				text-align: left;
			}

			tr:nth-child(even) {
				background-color: #eee
			}
		</style>
		<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>

	</head>
	<body>	
		<div id="logs">
			<h2>Build log</h2>
			<pre id="build-tail"></pre>
		</div>

		<div id="left">
			<h2>Builds</h2>
			<form id="controller-form" action="/" method="POST">
				<?php if(count($builds) > 0): ?>
					<select name="build">
						<option value="" disabled selected>- Select build -</option>
						<?php foreach($builds as $build): ?>
							<?php $buildTS = preg_replace("/-.*$/", "", $build); ?>

							<option value="<?php echo $build ?>">
								<?php if($build !== "latest"): ?>(<?php echo date("d-m-Y", $buildTS); ?>) -<?php endif; ?>
								<?php echo $build; ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<button type="button" autocomplete="off" onclick="newBuild(this)">Pull new build</button>
				<button
					 type="button"
					 <?php if(count($builds) === 0 || count($samples) === 0) { echo "disabled"; } ?>
					 autocomplete="off"
					 onclick="runTests(this)">
					 Run tests
				</button>
			</form>

			<h2>Samples</h2>
			<form action="/" method="POST" enctype="multipart/form-data">
	    		<input type="file" name="upload">
	    		<input type="submit" value="Upload Sample" name="spec[upload]">
			</form>
			<?php if(count($samples) > 0): ?>
				<?php foreach($samples as $sample): ?>
					<div>
						<?php echo $sample; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<h2>Test report overview</h2>
			<?php if(count($reports) === 0): ?>
				<em>No reports yet</em>
			<?php else: ?>
				<table id="result-list">
					<thead>
						<tr><th>Date</th><th>Build date</th><th>Build ID</th></tr>
					</thead>
					<tbody>
					<?php foreach($reports as $report): ?>
						<?php $buildId = preg_replace("/^[^-]+-(.*)\.html$/", "$1", $report); ?>
						<?php $buildTS = preg_replace("/-.*$/", "", $buildId); ?>
						<?php $testTS = date("d-m-Y H:i:s (T)", preg_replace("/^([^-]+)-.*$/", "$1", $report)); ?>
						<tr>
							<td>
								<a href="#<?php echo $report; ?>" 
									data-test-ts="<?php echo $testTS; ?>"
									data-build-id="<?php echo $buildId; ?>"
									onclick="showReport(this)">
									<?php echo $testTS; ?>
								</a>
							</td>
							<td>
								<?php if($buildId !== "latest"): ?>(<?php echo date("d-m-Y", $buildTS); ?>)<?php endif; ?>
							</td>
							<td>
								<a href="#<?php echo $report; ?>" 
									data-test-ts="<?php echo $testTS; ?>"
									data-build-id="<?php echo $buildId; ?>"
									onclick="showReport(this)">		
										<?php echo $buildId; ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
			<div id="test-result">
				<h2>Test results</h2>
				<em id="test-md">
					<?php if(count($reports) === 0): ?>No reports yet<?php endif; ?>
				</em><br /><br />
				<div id="result-container">
				</div>
			</div>
		</div>
	</body>
	<script type="text/javascript">
	var building = false;
	function showBuildLog(callback) {
		$.ajax("/?tail=build.log", {
			success: function(data) {
				var before = $("#build-tail").html();
				$("#build-tail").html(data).scrollTop($('#build-tail')[0].scrollHeight);
				if(building) {
					setTimeout(showBuildLog, 50);
				}
				if(callback) {
					callback();
				}
			}
		});
	}

	function _execBuild(fd) {
		building = true;
		$("#build-tail").html("");
		$('button, input[type="submit"]').prop("disabled", true)
		showBuildLog(function() { 
			$.ajax({
			  url: '/',
			  data: fd,
			  processData: false,
			  contentType: false,
			  type: 'POST',
			  success: function(data){
			  		location.reload();
			  }
			});
		});
	}

	function newBuild(button) {
		var fd = new FormData();    
		fd.append( 'newbuild', 1);
		_execBuild(fd);
	}

	function showReport(ref) {
		var file = "out/test/html/" + $(ref).attr("href").replace("#", "");
		$("#test-md").html(
			$(ref).attr("data-test-ts") + " (build ID: " + $(ref).attr("data-build-id") + ")"
		);
		$.ajax("/", {
			data: {report: file},
			type: "GET",
			success: function(html) { $('#result-container').html(html); }
		});
	}

	function runTests(button) {
		var fd = new FormData();
		var build = $("select[name='build']").val();
		if(!build || build === "") {
			build = "latest";
			$("select[name='build']").val(build);
		}
		fd.append('runtests', 1);
		fd.append('build', build);
		_execBuild(fd);
	}

	$(document).on("ready", function() {
		showBuildLog();
		if($("#result-list a")) {
			showReport($("#result-list a").get(0));
		}
	})
</script>
</html>