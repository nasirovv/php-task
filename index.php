<?php
include "connection.php";

// Define STDIN to read data from PHP
if (!defined("STDIN")) {
    define("STDIN", fopen('php://stdin', 'r'));
}

echo "Enter the number of cabinet: ";
$cabinetNumber = (int)trim(fread(STDIN, 5));
echo "Enter your arrive date in format d-m-Y H:i:s  : ";
$arrivedDate = trim(fread(STDIN, 30));

$d = DateTime::createFromFormat('d-m-Y H:i:s', $arrivedDate);
if ($d === false) {
    die("Incorrect date string");
}
// "24-08-2022 11:57:58"
$timestamp = $d->getTimestamp();

$urlCabinet = "SELECT * FROM cabinets WHERE unique_number = ?";
$stmtCabinet = $conn->prepare($urlCabinet);
$stmtCabinet->execute([$cabinetNumber]);
$dataCabinet = $stmtCabinet->fetch();

// Query
$url = "
        SELECT user_id, cabinet_id, arrived_date, departure_date
        FROM user_cabinets
        WHERE cabinet_id = ?
        AND arrived_date <= ?
        AND departure_date >= ?
        ";

$stmt = $conn->prepare($url);
$stmt->execute([(int)$dataCabinet['id'], $timestamp, $timestamp]);
$data = $stmt->fetch();


if (empty($data)) {
    echo "room is empty, so you can book it, Are you want to book it Y/N: ";

    $checkBooking = trim(fread(STDIN, 5));

    if (in_array($checkBooking, ['y', 'Y', 'yes', 'Yes'])) {

        echo "please enter your full name: ";
        $full_name = trim(fread(STDIN, 30));

        echo "please enter your email: ";
        $email = trim(fread(STDIN, 30));

        echo "please enter your phone: ";
        $phone = trim(fread(STDIN, 30));

        echo "please enter your departure date in format d-m-Y H:i:s  : ";
        $departureDate = trim(fread(STDIN, 30));

        $d = DateTime::createFromFormat('d-m-Y H:i:s', $departureDate);
        if ($d === false) {
            die("Incorrect date string");
        }
        // "24-08-2022 11:57:58"
        $timestampDeparture = $d->getTimestamp();

        $stmtForDeparture = $conn->prepare($url);
        $stmtForDeparture->execute([(int)$dataCabinet['id'], $timestampDeparture, $timestampDeparture]);
        $dataForDeparture = $stmtForDeparture->fetch();

        if (!empty($dataForDeparture)) {
            die('Please change your departure date, it is booked');
        }

        $sqlForFind = "SELECT * FROM users 
                        WHERE full_name = ?
                        AND email = ?
                        AND phone = ?
                        ";

        $stmtForFind = $conn->prepare($sqlForFind);
        $stmtForFind->execute([$full_name, $email, $phone]);
        $dataForFind = $stmtForFind->fetch();
        $user_id = $dataForFind['id'];

        if (empty($dataForFind)) {
            $sqlForInsert = "INSERT INTO users (full_name, email, phone) VALUES (?,?,?)";
            $stmtForInsert = $conn->prepare($sqlForInsert);
            $stmtForInsert->execute([$full_name, $email, $phone]);
            $user_id = $conn->lastInsertId();
        }

        $sqlInsertForPivot = "INSERT INTO user_cabinets (user_id, cabinet_id, arrived_date, departure_date) VALUES (?,?,?,?)";
        $stmtForPivot = $conn->prepare($sqlInsertForPivot);
        $stmtForPivot->execute([$user_id, (int)$dataCabinet['id'], $timestamp, $timestampDeparture]);

        echo "Congratulations, Successfully ordered";
    } else {
        die('The end!');
    }
} else {
    print_r(
        [
            'message' => "Room booked!!!",
            [
                'user_id'        => $data['user_id'],
                'arrived_date'   => date('d-m-Y H:i:s', $data['arrived_date']),
                'departure_date' => date('d-m-Y H:i:s', $data['departure_date']),
            ]
        ]
    );
}

fclose(STDIN);
