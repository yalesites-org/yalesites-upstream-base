<?php

namespace Drupal\ys_localist\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Process the Timezone offset into a compatible timezone name.
 *
 * @MigrateProcessPlugin(
 *   id = "timezone_offset"
 * )
 */
class TimeZoneFromOffset extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $offset = $value;

    list($hours, $minutes) = explode(':', $offset);
    $seconds = $hours * 60 * 60 + $minutes * 60;
    // Get timezone name from seconds
    $tz = timezone_name_from_abbr('', $seconds, 1);
    // Workaround for bug #44780
    if($tz === false) $tz = timezone_name_from_abbr('', $seconds, 0);

    return $tz;
  }

}
