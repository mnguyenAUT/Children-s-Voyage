<?php
if (!isset($_GET['u']) && !isset($_GET['src'])) {
    header("Location: gallery.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Audio Landing Page - Click here to control</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">  
</head>
<body>
  <!-- Your content here -->

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-KKMMN3C');</script>
<!-- End Google Tag Manager -->


<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KKMMN3C"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<center>
<?php
if (isset($_GET['u'])) {
  $tkiURL = $_GET['u'];;
}
if (isset($_GET['url'])) {
  $tkiURL = $_GET['url'];;
}
if (isset($_GET['src'])) {
  $imageURL = $_GET['src'];;
}


$arr = array("RRPP" => "Ready-to-Read-Phonics-Plus","RRCW" => "Ready-to-Read-Colour-Wheel","JJ" => "Junior-Journal","SJ" => "School-Journal");

$tkiURL = strtr($tkiURL,$arr);

?>

<?php
//https://instructionalseries.tki.org.nz/Instructional-Series/
$content = file_get_contents($tkiURL);
// True because $a is empty
if (empty($content)) {
  $content = file_get_contents("https://instructionalseries.tki.org.nz/Instructional-Series/".$tkiURL);
}

$doc = new DOMDocument();
$doc->loadHTML($content);
$xpath = new DOMXPath($doc);

//MP3 file
$src = $xpath->evaluate("string(//source/@src)");
$mp3File = $src;
echo "<br/>";

//PDF file
$src = $xpath->evaluate("string(//a[@class='pdf-ico']/@href)");
$pdfFile = $src;
if ($pdfFile == "") {
	$src = $xpath->evaluate("string(//a[@class='tsm-ico']/@href)");
	$pdfFile = $src;
}

//JPG file
$src = $xpath->evaluate("string(//div[@class='frame']//img/@src)");
$jpgFile = $src;

?>


<p>
<?php if (!empty($mp3File)) { ?>

<div id="loadingIcon" class="loading">Loading audio file now. Please wait...</div>

  <div id="audioContainer" style="display: none;">
    <a href="read.php?url=https://instructionalseries.tki.org.nz<?php echo $pdfFile; ?>" target="_blank">
      <audio id="audioPlayer" controls autoplay onplay="myFunction()" onended="window.top.close();">
        <source src="https://instructionalseries.tki.org.nz<?php echo $mp3File; ?>" type="audio/mpeg">
        Your browser does not support the audio element.
      </audio>
    </a>
  </div>

  <script>
    document.getElementById('audioPlayer').addEventListener('canplaythrough', function() {
      document.getElementById('loadingIcon').style.display = 'none';
      document.getElementById('audioContainer').style.display = 'block';
    });

    function myFunction() {
      // Custom functionality when audio starts playing
    }
  </script>
<br/>
<p>
<button class="btn btn-secondary rounded-pill" style="display:inline-block;" onclick="window.close();">
      Go Back
  </button>

  <button id="pdfButton" class="btn btn-primary rounded-pill" onclick="window.open('read.php?url=https://instructionalseries.tki.org.nz<?php echo $pdfFile; ?>', '_blank');">
      Load PDF
  </button>
  <hr/>
  <p>Click Play button, the PDF file will be loaded automatically! If not, click 'Load PDF'.</p>
<?php } else { ?>
  <!-- Display "Read PDF now" button if MP3 file doesn't exist -->
<!-- Go Back Button -->
  <button class="btn btn-secondary rounded-pill" style="display:inline-block;" onclick="window.close();">
      Go Back
  </button>


<a href="read.php?url=https://instructionalseries.tki.org.nz<?php echo $pdfFile; ?>" target="_parent">
  <button class="btn btn-primary rounded-pill">No Audio, Only PDF available</button>
</a>

<?php } ?>

<p>
  <?php if(isset($imageURL) && !empty($imageURL)): ?>
    <img src="<?php echo $imageURL; ?>" style="width: 75%;">
  <?php else: ?>
    <img src="https://instructionalseries.tki.org.nz<?php echo $jpgFile; ?>" height="100%">
  <?php endif; ?>
</p>


</p>
</center>
<iframe src="read.php?url=https://instructionalseries.tki.org.nz<?php echo $pdfFile; ?>" style="display:none"></iframe>
<script>
function myFunction() {
  window.open('read.php?url=https://instructionalseries.tki.org.nz<?php echo $pdfFile; ?>','_blank').focus();  
}
</script>

</body>
</html>
