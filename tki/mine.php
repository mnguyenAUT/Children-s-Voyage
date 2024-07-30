<html>
<body>
<center>

<form action="mine.php" method="post">
TKI URL: <input type="text" name="url" size="100" >
<input type="submit">
</form>
<h2>
Results:
</h2>

<?php
if(!empty($_POST['url'])) {
$urlPosted = $_POST['url'];
?>

<?php
$content = file_get_contents($urlPosted);
$doc = new DOMDocument();
$doc->loadHTML($content);
$xpath = new DOMXPath($doc);
?>

<ul>
<li>URL: <?php echo $urlPosted; ?></li>
<li>MP3 file: https://instructionalseries.tki.org.nz<?php $src = $xpath->evaluate("string(//source/@src)"); echo $src; ?></li>
<li>PDF file: https://instructionalseries.tki.org.nz<?php $src = $xpath->evaluate("string(//a[@class='pdf-ico']/@href)"); echo $src; ?></li>
<li>Image file: https://instructionalseries.tki.org.nz<?php $src = $xpath->evaluate("string(//div[@class='frame']//img/@src)"); echo $src; ?></li>
</ul>

<p>
<img src="https://instructionalseries.tki.org.nz<?php $src = $xpath->evaluate("string(//div[@class='frame']//img/@src)"); echo $src; ?>" height="75%">
</p>

<?php
}
?>

</center>
</body>
</html>
