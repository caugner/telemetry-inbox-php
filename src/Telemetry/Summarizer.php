<?php

namespace Telemetry;

class Summarizer {

  private $summary = [];
  private $ignore;

  public function __construct($ignore=[]) {
    $this->ignore = $ignore;
  }

  public function add($array) {
    self::summarize((array)$array, $this->summary);
  }

  public function getSummary() {
    return $this->summary;
  }

  private function summarize($array, &$summary, $ignore=[]) {
      // Count path..
      if (!isset($summary['count'])) {
        $summary['count'] = 1;
      } else {
        $summary['count'] += 1;
      }

      // Add key-value pairs to summary.
      foreach ($array as $key => $value) {
        if (in_array($key, $this->ignore)) {
          // Key is on ignore list.
          continue;
        }

        if (is_numeric($key) && is_string($value)) {
          // Item of an array.
          if (!isset($summary['values'])) {
            $summary['values'] = [];
          }
          $values = &$summary['values'];
          if (!isset($values[$value])) {
            $values[$value] = 1;
          } else {
            $values[$value] += 1;
          }
        } else if (is_array($value)) {
          if (!isset($summary['children'])) {
            $summary['children'] = [];
          }
          $children =& $summary['children'];
          if (!isset($children[$key])) {
            $children[$key] = [];
          }
          $this->summarize((array)$value, $children[$key]);
        } else {
          if (!isset($summary['keys'])) {
            $summary['keys'] = [];
          }
          $keys = &$summary['keys'];
          if (!isset($keys[$key])) {
            $keys[$key] = [
              'count' => 1
            ];
          } else {
            $keys[$key]['count'] += 1;
          }

          $current_key = &$keys[$key];


          if (is_numeric($value) && !is_string($value)) {
            // Numeric values => summarize distribution.

            if (!isset($current_key['distribution'])) {
              $current_key['distribution'] = [
                'count' => 1,
                'sum' => $value,
                'min' => $value,
                'max' => $value
              ];
            } else {
              $distribution = &$current_key['distribution'];
              $distribution['count'] += 1;
              $distribution['sum'] += $value;
              $distribution['min'] = min($value, $distribution['min']);
              $distribution['max'] = max($value, $distribution['max']);
            }
          } else if (is_string($value)) {
            // Non-numeric string values => count values.
            if (!isset($current_key['values'])) {
              $current_key['values'] = [];
            }
            $values = &$current_key['values'];

            if (!isset($values[$value])) {
              $values[$value] = 1;
            } else {
              $values[$value] += 1;
            }
          }
        }
      }
    }
}
