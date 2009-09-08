<?php

class ErrorController extends FWController
{
  public function execute404()
  {
    header("404 Not Found HTTP/1.0");
    echo "Error: 404.\n";
  }
}

