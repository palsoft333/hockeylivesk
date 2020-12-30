<?
$key = md5(uniqid());
$crc = md5($key);
echo "API KEY: ".$key."<br>";
echo "CRC CHECKSUM: ".$crc;
?>