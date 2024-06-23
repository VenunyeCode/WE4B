<?php
ob_start();
session_start();

require_once ('initialize.php');
require_once ('classes/DBConnection.php');
require_once ('classes/SystemSettings.php');
$db = new DBConnection();
$conn = $db->conn;
function redirect($url = '')
{
    if (!empty($url))
        echo '<script>location.href="' . BASE_URL . $url . '"</script>';
}
function validate_image($file)
{
    if (!empty($file)) {
        // exit;
        $ex = explode("?", $file);
        $file = $ex[0];
        $ts = isset($ex[1]) ? "?" . $ex[1] : '';
        if (is_file(BASE_APP . $file)) {
            return BASE_URL . $file . $ts;
        } else {
            return BASE_URL . 'dist/img/no-image-available.png';
        }
    } else {
        return BASE_URL . 'dist/img/no-image-available.png';
    }
}
function format_num($number = '', $decimal = '')
{
    if (is_numeric($number)) {
        $ex = explode(".", $number);
        $decLen = isset($ex[1]) ? strlen($ex[1]) : 0;
        if (is_numeric($decimal)) {
            return number_format($number, $decimal);
        } else {
            return number_format($number, $decLen);
        }
    } else {
        return "Invalid Input";
    }
}

function formatUsername($username)
{
    $formattedUsername = str_replace(' ', '_', trim($username));

    $formattedUsername = strtolower($formattedUsername);

    //$prefixedUsername = '@' . $formattedUsername;

    return $formattedUsername;
}

function displayUsername($username)
{
    return '@' . $username;
}

function relativeDate($postDate)
{
    $postTimestamp = is_int($postDate) ? $postDate : strtotime($postDate);

    $timeDifference = time() - $postTimestamp;

    $minute = 60;
    $hour = $minute * 60;
    $day = $hour * 24;
    $month = $day * 30;
    $year = $day * 365;

    if ($timeDifference < $minute) {
        return "à l'instant";
    } elseif ($timeDifference < $hour) {
        $minutesAgo = floor($timeDifference / $minute);
        return " il y'a " . $minutesAgo . " minute" . ($minutesAgo > 1 ? "s" : "");
    } elseif ($timeDifference < $day) {
        $hoursAgo = floor($timeDifference / $hour);
        return " il y'a " . $hoursAgo . " heure" . ($hoursAgo > 1 ? "s" : "");
    } elseif ($timeDifference < $month) {
        $daysAgo = floor($timeDifference / $day);
        return " il y'a " . $daysAgo . " jour" . ($daysAgo > 1 ? "s" : "");
    } elseif ($timeDifference < $year) {
        $monthsAgo = floor($timeDifference / $month);
        return " il y'a " . $monthsAgo . " mois" . ($monthsAgo > 1 ? "s" : "");
    } else {
        $yearsAgo = floor($timeDifference / $year);
        return " il y'a " . $yearsAgo . " an" . ($yearsAgo > 1 ? "s" : "");
    }
}

function formatDateMonthYear($month, $year)
{
    $dateStr = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'; // Format YYYY-MM-DD
    $date = new DateTime($dateStr);

    $monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

    $monthIndex = $date->format('n') - 1;
    $monthName = $monthNames[$monthIndex];
    $yearStr = $date->format('Y');

    return $monthName . ' ' . $yearStr;
}

function addHoursToDatetime($datetime, $hours, $withHours)
{
    $datetime = new DateTime($datetime);
    $datetime->add(new DateInterval("PT" . $hours . "H"));

    return $datetime->format('d/m/Y H:i:s');
}

function remainTime($datetime, $hours)
{
    $datetime = new DateTime($datetime);
    $now = new DateTime();
    $datetime->add(new DateInterval("PT" . $hours . "H"));
    $diff = date_diff($datetime, $now,false);
    $hoursDifference = $diff->days * 24 + $diff->h;
    return $hoursDifference;
    //echo $diff->format("%R%a days");
}

function addHoursToDatetime2($datetime, $hours, $withHours)
{
    $datetime = new DateTime($datetime);
    $datetime->add(new DateInterval("PT" . $hours . "H"));

    return $datetime->format('m/d/Y H:i:s');
}

ob_end_flush();
?>