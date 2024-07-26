MAIL=avrjoe@acme.com
SITE=/path/to/vhosts/acme.com
H=1
D=2
W=3
M=4
#
# Run this Route Guide Order report HOURLY as at 0/50 minutes 
# 0/1 * * * * /usr/bin/php -f $SITE/module/acme/bin/scheduled.php notifications $H &> /tmp/sc.txt
#
# Run this Route Guide Order report DAILY at 12:01a
# 1 0 * * * /usr/bin/php -f $SITE/module/acme/bin/scheduled.php notifications $D &> /tmp/sc.txt
#
# Run this Route Guide Order report WEEKLY at 12:05a
# 5 0 * * 7 /usr/bin/php -f $SITE/module/acme/bin/scheduled.php notifications $W &> /tmp/sc.txt
#
# Run this Route Guide Order report MONTHLY at 12:10a
# 10 0 1 * * /usr/bin/php -f $SITE/module/acme/bin/scheduled.php notifications $M &> /tmp/sc.txt
