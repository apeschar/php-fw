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
    Uncaught <?=get_class($exception)?> <code>#<?=$exception->getCode()?></code>:<br/>
  </p>
  <pre><?=htmlspecialchars($exception->getmessage())?></pre>
  <p>
    <b>File:</b> <code><?=htmlspecialchars($exception->getFile())?></code><br/>
    <b>Line:</b> <code>#<?=$exception->getLine()?></code><br/>
  </p>
  
  <h2>Backtrace</h2>
  <pre><?=htmlentities($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8')?></pre>
</body>
</html>

