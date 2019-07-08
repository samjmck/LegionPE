<?php

if(!($fp = stream_socket_client('tcp://0.0.0.0:1276', $errno, $errstr, 10))){
	echo "{$errstr} ({$errno}\n";
}else{
	echo "Success";
	fclose($fp);
}