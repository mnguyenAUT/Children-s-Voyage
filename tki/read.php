<title>PDF file Reader</title>
<?php
// Include the database configuration file
require_once '../books/config.php';
//echo "https://instructionalseries.tki.org.nz/content/download/35364/401284/file/K%C4%81kano-JJ50%20L2%20Feb%202015.pdf"."<hr/>";
$filePath=$_GET['url'];
$pieces = explode("/", $filePath);

$filePath1 = str_replace(' ', '%20', $filePath);
echo $filePath1;
echo "<hr/>";
$filePath=htmlspecialchars($filePath);
echo $filePath;
echo "<hr/>";

echo "K%C4%81kano-JJ50%20L2%20Feb%202015.pdf"."<hr/>";

$filename=end($pieces);
$removed = array_pop($pieces);
$newstringPath = implode("/", $pieces)."/".rawurlencode($filename);
echo "new string path: ".$newstringPath."<br/>";
$filename1 = str_replace(' ', '%20', $filename);
echo $filename1;

$filename=rawurlencode($filename);
echo "<hr/>";
echo $filename;
echo "<hr/>";
?>
<?php
//return;
header('Content-type:application/pdf');
header('Content-disposition: inline; filename="'.$filename.'"');
header('content-Transfer-Encoding:binary');
header('Accept-Ranges:bytes');
@ readfile($newstringPath);
?>
