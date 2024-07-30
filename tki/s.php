<?php
$filePath=$_GET['url'];
$pieces = explode("/", $filePath);
$filename=end($pieces);
echo urlencode($filePath);
echo "<br/>";
echo urlencode($filename);
echo "<br/>";
echo "https://instructionalseries.tki.org.nz/content/download/42666/478256/file/63827-RTR-Weka%20in%20a%20Flap-web.pdf";
echo "<br/>";
echo $filePath;
$new = str_replace(' ', '%20', $filePath);
echo "<br/>";
echo $new;
?>
<?php
header('Content-type:application/pdf');
header('Content-disposition: inline; filename="'.str_replace(' ', '%20', $filename).'"');
header('content-Transfer-Encoding:binary');
header('Accept-Ranges:bytes');
@ readfile(str_replace(' ', '%20', $filePath));
?>