<?php
/**
 * Calculate distance between two lat/lng coordinates using the Haversine formula.
 *
 * @param float $lat1
 * @param float $lon1
 * @param float $lat2
 * @param float $lon2
 * @return float Distance in kilometers
 */
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
   
    $earth_radius = 6371; // in km

    // Convert to radians
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earth_radius * $c;

    return $distance;
}

?>