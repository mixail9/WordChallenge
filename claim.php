<?php

if(!intval($_GET['claim_id']))
	die('ok');
	
	
$claims = json_decode(file_get_contents('claims.txt'), true);
$claims[intval($_GET['claim_id'])] += 1;
file_put_contents('claims.txt', json_encode($claims));

?>
