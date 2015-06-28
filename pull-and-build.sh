#!/bin/sh

if [ ! -d "openjpeg" ]; then
	git clone 'https://github.com/uclouvain/openjpeg.git'
fi

cd openjpeg
git pull origin master
build=`date +"%Y%m%d-"``git log -1 | head -1 | sed -e "s/commit //g"`
cd ..
export DESTDIR=`pwd`/builds/$build
cd openjpeg
mkdir -p ../builds/$build
cmake .
make
make install
