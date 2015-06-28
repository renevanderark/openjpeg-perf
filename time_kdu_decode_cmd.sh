#!/bin/sh



sample=$1
reduction=$2

mkdir -p `pwd`/out
export LD_LIBRARY_PATH=`pwd`/libkdu
ts=$(date +%s%N)
`pwd`/libkdu/kdu_expand -reduce $reduction -i `pwd`/samples/$sample -o `pwd`/out/kdu.raw  2>&1 1> /dev/null
tt=$((($(date +%s%N) - $ts)/1000000))
echo "Timed: $tt MS"
