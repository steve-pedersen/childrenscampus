#!/bin/bash
#Prepare sims files for copying into postgre tables

basedir="/var/local/dsp/sims-data"

head --lines=1 "$basedir/raw/users_sfsuid.lst" > "$basedir/prepped/users.lst" && iconv -t 'UTF8' -f 'LATIN1' "$basedir/raw/users_sfsuid.lst" | tail --lines=+2       | sed '$d' | sed '/^|/ d' |   head --bytes=-1 | sort | uniq -u >> "$basedir/prepped/users.lst"
head --lines=1 "$basedir/raw/courses_sfsuid.lst" > "$basedir/prepped/courses.lst" && iconv -t 'UTF8' -f 'LATIN1' "$basedir/raw/courses_sfsuid.lst" | tail --lines=+2 | sed '$d' | sed '/^|/ d' |   head --bytes=-1 | sort | uniq -u >> "$basedir/prepped/courses.lst"
head --lines=1 "$basedir/raw/enroll_sfsuid.lst" > "$basedir/prepped/enroll.lst" && iconv -t 'UTF8' -f 'LATIN1' "$basedir/raw/enroll_sfsuid.lst" | tail --lines=+2    | sed '$d' | sed '/^|/ d' |   head --bytes=-1 | sort | uniq -u >> "$basedir/prepped/enroll.lst"
head --lines=1 "$basedir/raw/descrip_sfsuid.lst" > "$basedir/prepped/descrip.lst" && iconv -t 'UTF8' -f 'LATIN1' "$basedir/raw/descrip_sfsuid.lst" | tail --lines=+2 | sed '$d' | sed '/^|/ d' |   head --bytes=-1 | sort | uniq -u >> "$basedir/prepped/descrip.lst"
