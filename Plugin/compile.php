<?php

$opts = getopt("", ["author::"]);
$author = "PEMapModder";
if(isset($opts["author"])){
	$author = $opts["author"];
}

chdir(dirname(__FILE__));
echo "Name of implementation to compile: ";

$line = trim(fgets(\STDIN));
#echo "Classic\n";
#$line = "Classic";

$path = realpath("Games/" . $line);
if($path === false){
	echo "The subproject doesn't exist!";
	exit(3);
}
$path = rtrim($path, "/\\") . DIRECTORY_SEPARATOR;
$line = basename($path);
if(!is_file($path . "plugin.yml")){
	echo "plugin.yml not found";
	exit(3);
}
if(!is_dir($path . "src")){
	echo "source folder not found";
	exit(3);
}

$pharPath = "CompiledPhars" . DIRECTORY_SEPARATOR . $line . ".phar";
if(!is_dir($dir = dirname($pharPath))){
	mkdir($dir, 0777, true);
}
if(is_file($pharPath)){
	unlink($pharPath);
}
$phar = new \Phar($pharPath);
$phar->setStub("<?php echo 'LegionPE-Iota ($line) - built at Unix timestamp " . time() . ". Copyright 2016 LegionPE and contributors. Some code as well as this compiler was written by PEMapModdder.'; __HALT_COMPILER();");
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();

function addDir(\Phar $phar, $realPath, $localPath){
	global $i;
	$realPath = str_replace("\\", "/", $realPath);
	$localPath = rtrim($localPath, "/\\") . "/";
	echo "\nDirectory transfer: $realPath > $localPath";
	foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($realPath)) as $path){
		if(!$path->isFile()){
			continue;
		}
		$path = str_replace("\\", "/", (string) $path);
		if(strpos($path, ".git") !== false){
			continue;
		}
		$relative = ltrim(substr($path, strlen($realPath)), "/");
		$local = $localPath . $relative;
		$num = str_pad((string) (++$i), 3, "0", STR_PAD_LEFT);
		echo "\n[$num] Adding: " . realpath($path) . " to $local";
		$phar->addFile($path, $local);
	}
}

$i = 0;
$pluginYml = file($path . "plugin.yml");
if(!in_array("#exclude Base", $pluginYml)){
	echo "Adding Base files...\n";
	if(is_dir("Games/Base/resources")){
		addDir($phar, "Games/Base/resources/", "resources");
	}
	addDir($phar, "Games/Base/src/", "src");
}
foreach($pluginYml as $line){
	if(substr($line, 0, 9) === "#include "){
		$dir = trim(substr($line, 9));
		$real = realpath($dir);
		$base = basename($dir);
		addDir($phar, $real . "/src", "src");
		if(is_dir($real . "/resources")){
			addDir($phar, $real . "/resources", "resources");
		}
	}
}

echo "\n[" . str_pad((string) (++$i), 3, "0", STR_PAD_LEFT) . "] Adding: " . realpath($path . "plugin.yml");
$phar->addFile($path . "plugin.yml", "plugin.yml");
//$phar->addFile(dirname(__FILE__) . "/LICENSE", "LICENSE");
addDir($phar, $path . "src", "src");
if(is_dir($path . "resources")){
	addDir($phar, $path . "resources", "resources");
}
/*echo "\nInjecting build information into resources...\n";
$phar->addFromString("resources/timestamp.LEGIONPE", (string) time());
$disk = json_decode(file_get_contents(dirname($path) . "/LegionPE_Iota/resources/build.json"));
$phar->addFromString("resources/build.json", json_encode([
	"time" => time(),
	"microtime" => microtime(true),
	"buildNumber" => ++$disk->lastBuildNumber,
	"buildAuthor" => $author
]));
file_put_contents("LegionPE_Iota/resources/build.json", json_encode($disk, JSON_PRETTY_PRINT));*/
$phar->stopBuffering();
echo "Signing phar...\n";
foreach(["md5"] as $algo){
	echo "Signing phar with $algo...\r";
	$file = dirname($pharPath) . DIRECTORY_SEPARATOR . "sig" . DIRECTORY_SEPARATOR . basename($pharPath) . ".md5";
	if(!is_dir(dirname($file))){
		mkdir(dirname($file));
	}
	file_put_contents($file, hash_file($algo, $pharPath));
}
echo "\nStaging...\n";
chdir(dirname($pharPath));
//exec("git add -A .");

echo "Done :) Phar is saved at $pharPath", PHP_EOL;
//echo "This is build #" . $disk->lastBuildNumber;
exit(0);
