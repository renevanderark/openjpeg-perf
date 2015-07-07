# openjpeg-perf

Measures processing speed of the openjpeg decoder on sample files. Currently supports .jp2 and .j2k formats.

Compares processing times of the opj\_decompress binary with the kdu\_expand binary using shell scripts. 

Built on ubuntu 14.04 LTS.

![Screenshot](https://raw.githubusercontent.com/renevanderark/openjpeg-perf/master/screenshot.png)



Quick start
---

Installing package dependencies


	$ sudo apt-get -y install php5 git-core gcc make cmake


Clone the latest openjpeg version from github and build it.


	$ ./pull-and-build.sh


Generate and run a test suite using the 2 provided sample files on the build tagged as 'latest'


	$ ./execute_tests.sh latest


If all goes well the following directory should contain an .html report:


	$ ls out/test/html


Running the webapp
---

Given the aforementioned package dependencies are met, the webapp should run out-of-the box with the following command, executed from the root of the project


	$ php -S -d upload_max_filesize=150M -d post_max_size=150M localhost:5000 web.php


You should now be able to navigate to http://localhost:5000 using a web browser and perform the following tasks from there:
- Cloning and building the latest version of openjpeg (button: Pull new build)
- Upload and delete sample files (.jp2 and .j2k)
- Autogenerate the test suite and run it (button: Run tests)


Running the webapp in docker
---
Using docker with the following commands allows the webapp and toolkit to be run in isolation. Using docker, package dependencies can all live in the docker container.

	$ docker pull ubuntu:trusty
	$ docker build -t renevanderark/openjpeg-perf .
	$ docker run -d -w /openjpeg-perf -p 5000:5002 -t renevanderark/openjpeg-perf

You should now be able to navigate to http://0.0.0.0:5000 using a web browser to use the webapp.


What tests are generated?
---
Per sample file there are 4 actual tests:

1. Decode the entire file with opj_decompress (and save it to a bitmap)
2. Decode the entire file with kdu_expand (and save it to a bitmap)
3. Decode all tiles of the file in parallel shell background processes with opj_decompress
4. Decode all tiles of the file in parallel shell background processes with kdu_expand

These 4 tests are multiplied by the number of resolution reduction levels supported by the provided sample image.

These tests are generated and executed by running this command:

	$ ./execute_tests.sh latest # first argument is the build ID (found in the builds directory)

The sections below explain what this script does in more detail.


Interpreting the test results
---

The screenshot below lists a test result based on the 2 provided samples:

![Table](https://raw.githubusercontent.com/renevanderark/openjpeg-perf/master/table.png)

There are 7 columns:

1. _Sample_: the sample file tested against
2. _Res_: the resolution reduction level of the measurements
3. _Decoder_: the decoder used for the measurements (opj or kdu)
4. _Full seq (ms)_: the time taken to decode the entire image using the default command at this reduction
5. _Avg/tile (ms)_: the average time taken to decode 1 tile of this image at this reduction
6. _Full parallel (ms)_: the total time taken to decode all tiles in parallel background processes
7. _N tiles_: the number of tiles the sample file contains.

There is therefore one row per decoder per resolution level per sample file.


How are the tests generated and run?
---
Because the kdu_expand binary expects a percentual region in stead of a tile index, regions are generated based on the image header.
This metadata is parsed to json by the opj-md binary (compiled after pulling the latest build with pull-and-build.sh) and read by a php script.

Executing this command outputs a test shell script.

	$ php generate-test-suite.php latest raw-log-file.log   # first argument: build ID, second argument: raw log file

Sample of the output:

	echo "Decoding full image sample1.jp2 with opj_decompress at reduction 0"
	./time_opj_decode_cmd.sh latest sample1.jp2 0 >> raw-log-file.log
	echo "Decoding full image sample1.jp2 with kdu_expand at reduction 0"
	./time_kdu_decode_cmd.sh sample1.jp2 0 >> raw-log-file.log

		(...)

	echo "Decoding tiles in parallel for sample1.jp2 with opj_decompress at reduction 0"
	ts=$(date +%s%N)
	./time_opj_decode_cmd.sh latest sample1.jp2 0 0 >> raw-log-file.log &
	./time_opj_decode_cmd.sh latest sample1.jp2 0 1 >> raw-log-file.log &
	   (...)
	./time_opj_decode_cmd.sh latest sample1.jp2 0 129 >> raw-log-file.log &
	wait
	echo "opj;latest;sample1.jp2;0;full-async;$tt" >> raw-log-file.log

	echo "Decoding tiles in parallel for sample1.jp2 with kdu_expand at reduction 0"
	ts=$(date +%s%N)
	./time_kdu_decode_cmd.sh sample1.jp2 0 "{0.000000000000000000000000000000,0.000000000000000000000000000000},{0.077622801697998787143723468768,0.106113989637305699481865284974}" >> raw-log-file.log &
	./time_kdu_decode_cmd.sh sample1.jp2 0 "{0.077622801697998787143723468768,0.000000000000000000000000000000},{0.077622801697998787143723468768,0.106113989637305699481865284974}" >> raw-log-file.log &
		(...)
	./time_kdu_decode_cmd.sh sample1.jp2 0 "{0.931473620375985445724681625227,0.955025906735751295336787564766},{0.077622801697998787143723468768,0.106113989637305699481865284974}" >> raw-log-file.log &
	wait
	tt=$((($(date +%s%N) - $ts)/1000000))
	echo "kdu; ;sample1.jp2;0;full-async;$tt" >> raw-log-file.log

As the above sample illustrates, two auxiliary shell scripts are used to time the processing speed of the decoders:

1. ./time\_opj_decode\_cmd.sh
2. ./time\_kdu_decode\_cmd.sh

The first command runs opj\_decompress from a given build and measures execution time of the command.
It uses the LD\_LIBRARY\_PATH to set the correct libraries from the 'builds' subdirectory.
It expects these arguments:

1. The build ID (can be found by running ls builds)
2. The sample image file
3. The resolution reduction factor
4. [optional] The tile index to decode

The second command runs kdu\_expand shipped in the libkdu directory, measures the same execution times and expects these arguments:

1. The sample image file
2. The resolution reduction factor
3. [optional] The region of the image to decode.

Executing the following command runs the actual generated test and logs the results to raw-log-file.log:
	
	$ php generate-test-suite.php latest raw-log-file.log | sh

The raw log file can then be processed into an HTML report by running this command:

	$ php generate-html-report.php < raw-log-file.log

