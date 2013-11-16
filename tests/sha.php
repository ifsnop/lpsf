<?

include('../src/Ifsnop/functions.inc.php');
$i=0;


print "====sha1" . PHP_EOL;
print hash('sha1', '') . PHP_EOL;
print "da39a3ee5e6b4b0d3255bfef95601890afd80709" . PHP_EOL;
print string2dec("da39a3ee5e6b4b0d3255bfef95601890afd80709", 16) . PHP_EOL;
print dec2string(string2dec("da39a3ee5e6b4b0d3255bfef95601890afd80709", 16), 62) . PHP_EOL;

print "====sha224" . PHP_EOL;
print hash('sha224', '') . PHP_EOL;
print "d14a028c2a3a2bc9476102bb288234c415a2b01f828ea62ac5b3e42f" . PHP_EOL;
print string2dec("d14a028c2a3a2bc9476102bb288234c415a2b01f828ea62ac5b3e42f", 16) . PHP_EOL;
print dec2string(string2dec("d14a028c2a3a2bc9476102bb288234c415a2b01f828ea62ac5b3e42f", 16), 62) . PHP_EOL;
print "====256" . PHP_EOL;
print hash('sha256', '') . PHP_EOL;
print "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855" . PHP_EOL;
print string2dec("e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855", 16) . PHP_EOL;
print dec2string(string2dec("e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855", 16), 62) . PHP_EOL;
print "====384" . PHP_EOL;
print hash('sha384', '') . PHP_EOL;
print "38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b" . PHP_EOL;
print string2dec("38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b", 16) . PHP_EOL;
print dec2string(string2dec("38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b", 16), 62) . PHP_EOL;
print "====512" . PHP_EOL;
print hash('sha512', '') . PHP_EOL;
print "cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e" . PHP_EOL;
print string2dec("cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e", 16) . PHP_EOL;
print dec2string(string2dec("cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e", 16), 62) . PHP_EOL;

while (true) {

$h=hash('sha256',$i);
$cache_hash1 = dec2string(string2dec($h, 16), 62);
$cache_hash2 = str_pad(dec2string(string2dec($h, 16), 62), 43, '0', STR_PAD_LEFT);

//print ">" . $h . PHP_EOL;
//print ">" . string2dec($h, 16) . PHP_EOL;
print ">" . $cache_hash1 . PHP_EOL;
print "<" . $cache_hash2 . PHP_EOL;

//print $i . " => " . dec2string($i,62) . PHP_EOL;
print "=================================================================" . PHP_EOL;
$i++;


}

?>