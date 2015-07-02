<?php
	if(isset($_POST['newbuild'])) {
		`./pull-and-build.sh > build.log 2>&1 &`;
		header("HTTP/1.1 200 OK");
		exit();
	} elseif(isset($_POST['spec'])) {
		var_dump($_POST['spec']);
		exit();
	} elseif (isset($_GET['tail'])) {
		$file =  $_GET['tail'];
		$tail = `tail -10000 $file 2> /dev/null`;
		echo $tail;
		exit();
	}

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
		return $list;
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

			$(document).on("ready", function() {
				showBuildLog();
			})
		</script>
	</head>
	<body>	
		<form id="controller-form" action="/" method="POST">
			<div>
			<?php if(count($builds) > 0): ?>
				<select name="spec[build]">
					<option value="" disabled selected>- Select build -</option>
					<?php foreach($builds as $build): ?>
						<option value="<?php echo $build ?>"><?php echo $build; ?></option>					
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<button type="button" autocomplete="off" onclick="newBuild(this)">Pull new build</button>
			</div>
			<div>
			<?php if(count($builds) > 0 && count($samples) > 0): ?>
				<select name="spec[sample]">
					<option value="" disabled selected>- Select sample -</option>
					<?php foreach($samples as $sample): ?>
						<option value="<?php echo $sample ?>"><?php echo $sample; ?></option>					
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			</div>
			<input type="submit" value="TODO: Run tests" />
		</form>
		<h2>Build log</h2>
		<pre style="max-height: 500px; overflow: auto" id="build-tail">
		</pre>
	</body>
</html>