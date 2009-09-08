<?php

abstract class fwcmd_plugin
{
  /**
   * Ask for confirmation
   *
   * @param string $question
   * @return bool
   */
  protected function _yesno($question)
  {
    echo "$question [y/n] ";

    // read character
    $char = trim(strtolower(fgets(STDIN)));
    if($char != 'y' && $char != 'n')
    {
      return $this->_yesno($question);
    }
    
    // return
    return $char == 'y';
  }

  /**
   * Use assocative array to call str_replace
   *
   * @param array $search_replace
   * @param string $string
   * @return string
   */
  protected function _replaceAssoc(array $search_replace, $string)
  {
    // fill $search and $replace arrays
    $search = $replace = array();
    foreach($search_replace as $key => $val)
    {
      $search[] = $key;
      $replace[] = $val;
    }

    // execute str_replace
    return str_replace($search, $replace, $string);
  }

  /**
   * Use _replaceAssoc on a file
   *
   * @param array $search_replace
   * @param string $filename
   * @return string
   */
  protected function _replaceAssocFile(array $search_replace, $file)
  {
    if(!is_file($file)) die("`$file' does not exist or is not a file.\n");
    file_put_contents($file, $this->_replaceAssoc($search_replace, file_get_contents($file)));
  }

  /**
   * Prompt for input
   *
   * @param string $question
   * @param string $default
   * @param array $options
   */
  protected function _ask($question, $default = null, array $options = array())
  {
    // output question
    echo $question, " ";
    if($default !== null) echo "[", $default, "] ";
    
    // get input
    $input = rtrim(fgets(STDIN), "\r\n");
    if($default !== null && $input == '') $input = $default;

    // validate input
    if($options && $default !== null && !in_array($default, $options)) $options[] = $default;
    if($options && !in_array($input, $options)) return $this->_ask($question, $default, $options);

    // done
    return $input;
  }

  /**
   * Copy directory
   *
   * @param string $src
   * @param string $dest
   * @param string $output
   */
  protected function _copyDir($src, $dest, $output = false, $exclude = array('.svn'))
  {
    if(!is_dir($src)) die("Source directory `$src' does not exist.\n");
    if(!is_dir($dest)) die("Source directory `$dest' does not exist.\n");

    $stack = scandir($src);

    while(($file = array_pop($stack)) !== null)
    {
      $full_src = $src . '/' . $file;
      $full_dest = $dest . '/' . $file;
      
      // skip '.' and '..' hard-links
      $basename = basename($file);
      if($basename == '.' || $basename == '..') continue;

      // exclude specified files
      if(is_array($exclude) && in_array($basename, $exclude)) continue;

      // output filename
      if($output)
        echo "+ $file\n";

      // copy or descend
      if(is_dir($full_src))
      {
        if(!is_dir($full_dest)) mkdir($full_dest);
        foreach(scandir($full_src) as $new_file)
        {
          $stack[] = $file . '/' . $new_file;
        }
      }
      else
      {
        if(file_exists($full_dest))
        {
          if(is_dir($full_dest))
            rmdir($full_dest);
          else
            unlink($full_dest);
        }

        copy($full_src, $full_dest);
      }
    }
  }

  /**
   * Format and output message
   *
   * @param string $message
   */
  protected function _format($message)
  {
    $message = preg_replace('|\s+|', ' ', str_replace("\n", '', $message));
    echo wordwrap($message, 74), "\n";
  }

  /**
   * Check if current working directory is a project directory
   *
   */
  protected function _checkProjectDir()
  {
    if(!is_file('fw')) die("Current working directory is not a project directory.\n");
  }

  /**
   * Delete a directory
   *
   * @param string $directory
   * @param boolean $output
   */
  protected function _rmdir($directory, $output = false)
  {
    if(!is_dir($directory)) die("Directory `$directory' not found.\n");
    
    foreach(scandir($directory) as $file)
    {
      if($file == '.' || $file == '..') continue;
      $full = $directory . '/' . $file;
      if($output) echo "- $full\n";
      if(is_dir($full) && !is_link($full))
        $this->_rmdir($full, $output);
      else
        unlink($full);
    }

    if($output) echo "- $directory\n";
    rmdir($directory);
  }
}

