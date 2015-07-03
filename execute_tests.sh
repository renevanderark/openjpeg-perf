#!/bin/sh

result_dir=out/test/raw
result_file=`date +%s`.log

mkdir -p $result_dir
touch $result_dir/$result_file
php generate-test-suite.php $1 $result_dir/$result_file  | sh