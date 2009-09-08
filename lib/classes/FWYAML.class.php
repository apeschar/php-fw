<?php

/**
 * @package framework
 */

/**
 * Spyc
 */
require_once dirname(__FILE__) . '/../vendor/spyc/spyc.php';

/**
 * YAML class
 *
 * @package framework
 */
class FWYAML
{
  /**
   * Load a YAML file into an array
   *
   * @param string $filename
   * @return array
   */
  public static function load($filename)
  {
    assert('is_string($filename)');
    if(!is_file($filename)) throw new Exception('File not found.');
    return Spyc::YAMLLoad($filename);
  }

  /**
   * Dump an array into a YAML string
   *
   * @param array $document
   * @return string
   */
  public static function dump($document)
  {
    assert('is_array($document)');
    return Spyc::YAMLDump($document);
  }
}

