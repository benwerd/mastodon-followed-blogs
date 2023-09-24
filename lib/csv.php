<?php

  function csv_to_array($filename, $function = false) {
    $results = [];
    $header = [];

    $file_pointer = fopen($filename, 'r');
    if ($file_pointer) {
      $line_in_file = 0;
      while ($line = fgetcsv($file_pointer)) {
        if ($line_in_file === 0) {
          $header = $line;
        } else {
          $result = [];
          foreach($line as $key => $value) {
            $result[$header[$key]] = $value;
          }
          if (is_callable($function)) {
            $result = $function($result);
          }
          $results[] = $result;
        }
        $line_in_file++;
      }
      fclose($file_pointer);
    }

    return $results;
  }

  function array_to_csv($filename, $array, $headers = false) {
    $file_pointer = fopen($filename, 'w');
    if ($file_pointer) {
      if ($headers) {
        fputcsv($file_pointer, $headers);
      }
      foreach($array as $line) {
        fputcsv($file_pointer, $line);
      }
      fclose($file_pointer);
    }
  }