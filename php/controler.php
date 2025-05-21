<?php

require_once 'Database.php';
require_once 'ParkingManagement.php';

$parkingManagement = new ParkingManagement();

//Entry the parking.
if (isset($_POST['entry-vehicle-number']) && isset($_POST['vehicle_type'])) {
    $vehicle_number = htmlspecialchars($_POST['entry-vehicle-number']);
    $vehicle_type = $_POST['vehicle_type'];
    $result = $parkingManagement->setCarParking($vehicle_type, $vehicle_number);
    echo json_encode($result);
}

//Release the parking.
if (isset($_POST['release-vehicle-number']) && isset($_POST['vehicle_type'])) {
    $vehicle_number = htmlspecialchars($_POST['release-vehicle-number']);
    $vehicle_type = $_POST['vehicle_type'];
    $result = $parkingManagement->releasePark($vehicle_type, $vehicle_number);
    echo json_encode($result);
}

//Get the empty slots.
if (isset($_POST['getSlots'])) {
    $result = $parkingManagement->getSlots();
    echo json_encode($result);
}

//Get the tickets.
if (isset($_POST['getTickets'])) {
    $result = $parkingManagement->getTicket();
    echo json_encode($result);
}
?>
