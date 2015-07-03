#!/bin/sh

sample=$1
reduction=$2
region=$3

mkdir -p `pwd`/out
export LD_LIBRARY_PATH=`pwd`/libkdu
ts=$(date +%s%N)

if [ -z "$3" ] 
then `pwd`/libkdu/kdu_expand -reduce $reduction -i `pwd`/samples/$sample -o `pwd`/out/kdu.bmp  2>&1 1> /dev/null
else `pwd`/libkdu/kdu_expand -reduce $reduction -i `pwd`/samples/$sample -o `pwd`/out/kdu.bmp -region $region 2>&1 1> /dev/null
fi


tt=$((($(date +%s%N) - $ts)/1000000))
echo "kdu;;$sample;$reduction;$region;$tt"