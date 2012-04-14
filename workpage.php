<?php include_once('header.php'); ?>
<?php include_once('functions.php'); ?>

<!--This is the default page that is loaded when the website loads, it includes
links to all of the javascript files necessary and has the default html that
loads when the page loads.  After the page fully loads it runs the default
javascript-->

<script type="text/javascript" src="http://www.jasonkoebler.com/jquery-1.4.2.js"></script>
<script type="text/JavaScript" src="http://www.jasonkoebler.com/cloud-carousel.1.0.5.js"></script>
<script type="text/javascript" src="http://www.jasonkoebler.com/javascript.js"></script>
<div id= 'connectnavbar'></div>
<div id='maincontainer'>
<?php echo buildDefaultPage("jason_koebler"); ?>
</div>

<script type="text/javascript">
window.onload = pageinit;
</script>


