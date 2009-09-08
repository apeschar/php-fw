<html>
<head>
  <title>Internal Server Error</title>
  <style type="text/css">
    body {
      font-family: Verdana;
    }
    
    pre, code {
      font-family: Courier New, monospaced;
      font-size: 9pt;
    }
  </style>
</head>

<body>
  <h1>500 - Internal Server Error</h1>
  <p>An unexpected error occurred.</p>
  <p>
    <?=$errno?><?=$errname?" $errname":''?>: <?=htmlspecialchars($errstr)?>
  </p>
  <p>
    <b>File:</b> <code><?=htmlspecialchars($errfile)?></code><br/>
    <b>Line:</b> <code>#<?=$errline?></code><br/>
  </p>
  
  <h2>Backtrace</h2>
  <pre><?php debug_print_backtrace(); ?></pre>
 
  <!--
  <h2>Context</h2>
  <pre>{print_r_context}</pre>
  -->
</body>
</html>

