# openjpeg-perf

Measures processing speed of the openjpeg decoder on sample files. Currently supports .jp2 and .j2l formats.

Compares processing times of the opj_decompress binary with the kdu_expand binary using shell scripts. 

Built on ubuntu 14.04 LTS.


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


	$ php -S localhost:5000 web.php


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


![Screenshot](https://raw.githubusercontent.com/renevanderark/openjpeg-perf/master/screenshot.png)
