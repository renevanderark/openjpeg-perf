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
	}



	$builds = listDir("builds");
	$samples = listDir("samples");
?>

<html>
	<head>
		<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
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

			function newBuild(button) {
				var fd = new FormData();    
				fd.append( 'newbuild', 1);
				building = true;
				$("#build-tail").html("");
				$(button).prop("disabled", true);
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

			function runTests(button) {
				var fd = new FormData();
				var build = $("select[name='build']").val();
				if(!build || build === "") {
					build = "latest";
					$("select[name='build']").val(build);
				}
				fd.append('runtests', 1);
				fd.append('build', build);
				building = true;
				$("#build-tail").html("");
				$(button).prop("disabled", true);
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

			$(document).on("ready", function() {
				showBuildLog();
			})
		</script>
	</head>
	<body>	
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
			<button type="button" autocomplete="off" onclick="runTests(this)">Run tests</button>
		</form>

		<h4>Samples</h4>
		<form action="/" method="POST" enctype="multipart/form-data">
    		<input type="file" name="upload">
    		<input type="submit" value="Upload Sample" name="spec[upload]">
		</form>
		<?php if(count($builds) > 0 && count($samples) > 0): ?>
			<?php foreach($samples as $sample): ?>
				<div>
					<?php echo $sample; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>

		<h2>Build log</h2>
		<pre style="max-height: 500px; overflow: auto" id="build-tail">
		</pre>
	</body>
</html>