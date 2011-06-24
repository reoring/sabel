<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $pageTitle ?></title>
    <meta name="author" content="" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link type="text/css" rel="stylesheet" href="<?php echo linkto("css/default.css") ?>" />
  </head>
  <body>
    <!--[if IE 7]><div class="ie_7"><![endif]-->
    <!--[if IE 6]><div class="ie_6"><![endif]-->
    
    <div id="header">
      <h1>Sabel</h1>
    </div>
    
    <div id="contents">
      <?php echo $contentForLayout ?>
    </div>
      
    <div id="footer">
      <address>
        Copyright(C) 2008 Sabel Development Team. All Rights Reserved.
      </address>
      <p>
        <a href="http://www.sabel.jp/">
          <img src="<?php echo linkto("images/powered-by-sabel.gif") ?>" title="Powered by Sabel" alt="Powered by Sabel" />
        </a>
      </p>
    </div>
    
    <!--[if (IE 6 & IE 7)]></div><![endif]-->
  </body>
</html>
