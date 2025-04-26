<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="styles.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>2025 Cycling log.</title>
  </head>
  <body>
    <h1>2025 Cycling log.</h1>

    <p>My goal this year is to hit a thousand miles on my bike. Below is the
    list of all my rides this year.</p>

    <table class="cycling-log-table">
      <tr>
        <th>Date</th>
        <th>Miles</th>
        <th>Time (h:m:s)</th>
        <th>Avg. Mph</th>
        <th>Ascent (ft)</th>
      </tr>
<?php
$data = [
  [ "Feb 1", 4.97, [ 0, 34, 51 ], 728 ],
  [ "Feb 2", 5.00, [ 0, 30, 38 ], 732 ],
  [ "Mar 1", 14.81, [ 1, 15, 50 ], 2264 ],
  [ "Mar 2", 14.79, [ 1, 14, 48 ], 2188 ],
  [ "Mar 18", 9.89, [ 0, 49, 41 ], 1355 ],
  [ "Mar 22", 9.89, [ 0, 49, 25 ], 1385 ],
  [ "Mar 23", 9.88, [ 0, 47, 06 ], 1375 ],
  [ "Mar 29", 21.68, [ 1, 35, 8 ], 1421 ],
  [ "Mar 30", 16.21, [ 1, 32, 44 ], 2057 ],
  [ "Apr 1", 4.99, [ 0, 23, 30 ], 705 ],
  [ "Apr 5", 26.83, [ 1, 45, 42 ], 1598 ],
  [ "Apr 12", 6.3, [ 0, 30, 8 ], 0 ],
  [ "Apr 13", 13.43, [ 1, 13, 32 ], 1398 ],
  [ "Apr 14", 14.81, [ 1, 13, 32 ], 2021 ],
  [ "Apr 18", 10.11, [ 1, 2, 37 ], 1346 ],
  [ "Apr 19", 10.06, [ 0, 53, 15 ], 1347 ],
  [ "Apr 20", 10.05, [ 0, 57, 19 ], 1345 ],
  [ "Apr 22", 10.98, [ 1, 1, 25 ], 1472 ],
  [ "Apr 26", 10.00, [ 1, 9, 43 ], 1346 ]
];

$total_miles = 0;
$total_seconds = 0;
$total_ascent = 0;

foreach ($data as $item) {
  $date = $item[0];
  $miles = $item[1];
  $total_miles += $miles;
  $hours = $item[2][0];
  $total_seconds += $hours * 60 * 60;
  $minutes = $item[2][1];
  $total_seconds += $minutes * 60;
  $seconds = $item[2][2];
  $total_seconds += $seconds;
  $fractional_hours = $item[2][0] + $item[2][1] / 60 + $item[2][2] / 60 / 60;
  $ascent = $item[3];
  $total_ascent += $ascent;
?>
      <tr>
        <td><?php echo $date ?></td>
        <td><?php echo $miles ?></td>
        <td><?php echo $hours ?>:<?php echo $minutes ?>:<?php echo $seconds ?></td>
        <td><?php echo floor($miles / $fractional_hours * 100 + 0.5) / 100 ?></td>
        <td><?php echo $ascent ?></td>
      </tr>
<?php
} 
$total_fractional_hours = $total_seconds / 60 / 60;
$total_hours = floor($total_fractional_hours);
$total_seconds -= $total_hours * 60 * 60;
$total_minutes = floor($total_seconds / 60);
$total_seconds -= $total_minutes * 60;
?>
    </table>

    <p>Below are the cumulative stats.</p>

    <table class="cycling-log-table">
      <tr>
        <th>Total Miles</th>
        <td><?php echo $total_miles ?></th>
      </tr>
      <tr>
        <th>Total Time (h:m:s)</th>
        <td><?php echo $total_hours ?>:<?php echo $total_minutes ?>:<?php echo $total_seconds ?></th>
      </tr>
      <tr>
        <th>Avg. Mph Overall</th>
        <td><?php echo floor($total_miles / $total_fractional_hours * 100 + 0.5) / 100 ?></th>
      </tr>
      <tr>
        <th>Total Ascent (ft)</th>
        <td><?php echo $total_ascent ?></th>
      </tr>
    </table>
  </body>
</html>
