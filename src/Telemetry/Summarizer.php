<?php

namespace Telemetry;

class Summarizer {

  private $summary = [];
  private $ignore;

  public function __construct($ignore=[]) {
    $this->ignore = $ignore;
  }

  public function add($array) {
    $this->summary = self::summarize($array, $this->summary);
  }

  public function getSummary() {
    return $this->summary;
  }

  private function summarize($array, &$summary, $ignore=[]) {
      if (!isset($summary['count'])) {
        $summary['count'] = 1;
      } else {
        $summary['count'] += 1;
      }
      foreach ($array as $key => $value) {
        if (in_array($key, $ignore)) {
          continue;
        }
        if (is_numeric($key) && is_string($value)) {
          if (!isset($summary['values'])) {
            $summary['values'] = [];
          }
          if (!isset($summary['values'][$value])) {
            $summary['values'][$value] = 1;
          } else {
            $summary['values'][$value] += 1;
          }
        } else if (is_array($value)) {
          if (!isset($summary['children'][$key])) {
            $summary['children'][$key] = [];
          }
          $this->summarize($value, $summary['children'][$key]);
        } else {
          if (!isset($summary['keys'])) {
            $summary['keys'] = [];
          }
          if (!isset($summary['keys'][$key])) {
            $summary['keys'][$key] = [
              'count' => 1
            ];
          } else {
            $summary['keys'][$key]['count'] += 1;
          }
          if (is_numeric($value)) {
            // Min/max erfassen.
            if (!isset($summary['keys'][$key]['distribution'])) {
              $summary['keys'][$key]['distribution'] = [
                'count' => 1,
                'sum' => $value,
                'min' => $value,
                'max' => $value
              ];
            } else {
              $summary['keys'][$key]['distribution']['count'] += 1;
              $summary['keys'][$key]['distribution']['sum'] += $value;
              $summary['keys'][$key]['distribution']['min'] = min($value, $summary['keys'][$key]['distribution']['min']);
              $summary['keys'][$key]['distribution']['max'] = max($value, $summary['keys'][$key]['distribution']['max']);
            }
          } else if (is_string($value)) {
            if (!isset($summary['keys'][$key]['values'])) {
              $summary['keys'][$key]['values'] = [];
            }
            if (!isset($summary['keys'][$key]['values'][$value])) {
              $summary['keys'][$key]['values'][$value] = 1;
            } else {
              $summary['keys'][$key]['values'][$value] += 1;
            }
          }
        }
      }
      return $summary;
    }
}
