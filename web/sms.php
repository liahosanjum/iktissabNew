
<?php
phpinfo();die();
global $arraySendMsg;
$url = "www.mobily.ws/api/msgSend.php";
$applicationType = "68";
$msg = 'welcome to abdul';
$sender = urlencode('Iktissab');
$domainName = 'http://www.othaimmarkets.com';
//$stringToPost = "mobile=Othaim&password=0557554443&numbers=966569858396&sender=".$sender."&msg=".$msg."&timeSend=0&dateSend=0&applicationType=".$applicationType."&domainName=".$domainName."&msgId=2323&deleteKey=9898&lang=3";
$stringToPost = "mobile=Othaim&password=0557554443&numbers=966569858396&sender=Iktissab&msg=000welcomewelcome&timeSend=0&dateSend=0&applicationType=68&domainName=localhost&msgId=189&deleteKey=600&lang=3";
echo $stringToPost;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
$result = curl_exec($ch);
$info = curl_getinfo($ch);
var_dump($info);
echo "<hr />";
var_dump($result)
;
?>