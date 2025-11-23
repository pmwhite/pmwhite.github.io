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
  [ "Apr 26", 10.00, [ 1, 9, 43 ], 1346 ],
  [ "Apr 27", 10.09, [ 1, 0, 0 ], 1346 ],
  [ "Apr 29", 9.86, [ 0, 48, 8 ], 1345 ],
  [ "May 3", 10.04, [ 1, 1, 15 ], 1345 ],
  [ "May 8", 5.06, [ 0, 23, 11 ], 670 ],
  [ "May 16", 5.01, [ 0, 27, 52 ], 682 ],
  [ "May 18", 4.68, [ 0, 26, 1 ], 392 ],
  [ "May 20", 5.02, [ 0, 23, 7 ], 678 ],
  [ "May 23", 5.03, [ 0, 27, 25 ], 680 ],
  [ "Jun 8", 2.68, [ 0, 19, 49 ], 348 ],
  [ "Jun 11", 4.90, [ 0, 26, 23 ], 680 ],
  [ "Jun 12", 5.00, [ 0, 21, 17 ], 659 ],
  [ "Jun 19", 9.81, [ 0, 49, 25 ], 1276 ],
  [ "Jul 3", 5.33, [ 0, 30, 56 ], 722 ],
  [ "Jul 6", 10.67, [ 1, 3, 0 ], 998 ],
  [ "Jul 8", 6.16, [ 0, 38, 52 ], 810 ],
  [ "Jul 16", 7.55, [ 0, 27, 53 ], 79 ],
  [ "Jul 17", 9.11, [ 0, 30, 24 ], 27 ],
  [ "Jul 20", 5.02, [ 0, 20, 13 ], 677 ],
  [ "Aug 1", 5.01, [ 0, 20, 23 ], 678 ],
  [ "Aug 3", 5.00, [ 0, 20, 0 ], 680 ],
  [ "Aug 9", 5.25, [ 0, 33, 44 ], 669 ],
  [ "Aug 10", 5.03, [ 0, 28, 54 ], 674 ],
  [ "Aug 11", 5.05, [ 0, 19, 35 ], 679 ],
  [ "Aug 25", 4.98, [ 0, 20, 17 ], 678 ],
  [ "Aug 30", 4.25, [ 0, 25, 24 ], 560 ],
  [ "Aug 30", 5.07, [ 0, 30, 30 ], 683 ],
  [ "Aug 31", 5.02, [ 0, 25, 41 ], 679 ],
  [ "Sep 1", 1.36, [ 0, 9, 8 ], 187 ],
  [ "Sep 1", 4.96, [ 0, 20, 18 ], 676 ],
  [ "Sep 3", 4.95, [ 0, 19, 59 ], 666 ],
  [ "Sep 4", 4.96, [ 0, 19, 16 ], 666 ],
  [ "Sep 5", 4.98, [ 0, 19, 12 ], 653 ],
  [ "Sep 6", 4.96, [ 0, 20, 9 ], 656 ],
  [ "Sep 13", 5.37, [ 0, 32, 16 ], 699 ],
  [ "Sep 20", 4.96, [ 0, 19, 48 ], 669 ],
  [ "Sep 21", 3.66, [ 0, 18, 57 ], 541 ],
  [ "Oct 1", 5.02, [ 0, 27, 5 ], 686 ],
  [ "Oct 7", 4.96, [ 0, 27, 46 ], 679 ],
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
