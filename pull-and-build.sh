#!/bin/sh

rm -rf openjpeg
git clone 'https://github.com/uclouvain/openjpeg.git'

cd openjpeg
build=`date +"%Y%m%d-"``git log -1 | head -1 | sed -e "s/commit //g"`
cd ..
export DESTDIR=`pwd`/builds/$build
cd openjpeg
mkdir -p ../builds/$build
cmake .
make
make install
rm -rf ../builds/latest
cp -R ../builds/$build ../builds/latest

cd ..
./compile-opj-md.sh
echo 'BUILD DONE'