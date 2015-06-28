# openjpeg-perf

todo general outline:
---


1) create small webinterface to coordinate builds and tests with
2) upload sample files, generate sample-metadata and save in sample-md file
3) use sample and sample metadata to execute for kdu_expand and opj_decompress in similar manner using these adjustable parameters:

    full file
    decode area / tile-index
    reduction factor

4) record processing times (and cpu load) meaningfully in file or database
5) expose above scripts through http for load- and stress-tests
6) expose test reports through web-interface
