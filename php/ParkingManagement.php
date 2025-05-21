<?php

/**
 * The ParikingManagaement class will handle the parking systems.
 */
class ParkingManagement extends Database {

  /**
   * The setParking function will set the parking lot and update to the database.
   * 
   * @param $vehicle_type
   * This is the type of vachile.
   * 
   * @param $vehicle_number
   * This is the number of vachile.
   */
  public function setCarParking($vehicle_type, $vehicle_number) {
    try {
      $query = $this->conn->prepare("SELECT slot_number FROM tickets WHERE vehicle_type = ? AND status = 'Booked'");
      $query->execute([$vehicle_type]);
      $booked = $query->fetchAll(PDO::FETCH_COLUMN);

      // Find first available slot
      $allSlots = range(1, 100);
      $available = array_diff($allSlots, $booked);
      $slot_number = reset($available);

      if (!$slot_number) {
        return [
          "status" => FALSE,
          "message" => "Parking Area Full"
        ];
      }
      // Insert ticket
      if ($this->checkCarAlreadyParked($vehicle_number, $vehicle_type)) {
        $stmt = $this->conn->prepare("INSERT INTO tickets (vehicle_number, vehicle_type, slot_number) VALUES (?, ?, ?)");
        $stmt->execute([$vehicle_number, $vehicle_type, $slot_number]);

        //Select the ticket details.
        $ticket_stmt = $this->conn->prepare("SELECT * FROM tickets WHERE vehicle_number = ? AND vehicle_type = ?");
        $ticket_stmt->execute([$vehicle_number, $vehicle_type]);
        $row = $ticket_stmt->fetch(PDO::FETCH_ASSOC);
        return [
          "status" => TRUE,
          "message" => "Parking Booked",
          "result_set" => $row
        ];
      } 
      else {
        return [
          "status" => TRUE,
          "message" => "Car already parked"
        ];
      }
    } 
    catch (PDOException $e) {
      error_log("Error: " . $e->getMessage());
      return [
        "status" => FALSE,
        "message" => "error"
      ];
    }
  }

  /**
   * The releasePark function will set the parking lot and update to the database.
   * 
   * @param $vehicle_type
   * This is the type of vachile.
   * 
   * @param $vehicle_number 
   * This is the number of vachile.
   * 
   * @return array
   */
  public function releasePark($vehicle_type, $vehicle_number): array {
    try {
      //Check the ticket is avilable in ticket area.
      $sql = "SELECT * FROM tickets WHERE vehicle_number = ?";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute([$vehicle_number]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
        try {
          //Check the ticket is avilable for a vehicle type.
          $sql = "SELECT * FROM tickets  WHERE vehicle_number = ? AND vehicle_type = ?";
          $stmt = $this->conn->prepare($sql);
          $stmt->execute([$vehicle_number, $vehicle_type]);
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($row > 0) {
            if ($this->checkCarAlreadyRelease($vehicle_number, $vehicle_type)) {
              try {

                //Update the status released.
                $sql = "UPDATE tickets SET status = ? WHERE vehicle_number = ? AND vehicle_type = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute(["Released", $vehicle_number, $vehicle_type]);

                //Set the Exit time for the ticket
                $stmt2 = $this->conn->prepare("UPDATE tickets SET exit_time = NOW() WHERE vehicle_number = ? AND vehicle_type = ?");
                $stmt2->execute([$vehicle_number, $vehicle_type]);

                //Get the status of ticket.
                $stmt3 = $this->conn->prepare("SELECT id, exit_time ,status FROM tickets WHERE vehicle_number = ? AND vehicle_type = ?");
                $stmt3->execute([$vehicle_number, $vehicle_type]);
                $status = $stmt3->fetch((PDO::FETCH_ASSOC));
                return [
                  "status" => TRUE,
                  "message" => "released",
                  "ticket_id" => $status['id'],
                  "ticket_status" => $status['status'],
                  "ticket_exit_time" => $status['exit_time']
                ];
              } 
              catch (PDOException $e) {
                error_log("Error: " . $e->getMessage());
                return [
                  "status" => FALSE,
                  "message" => "error"
                ];
              }
            } 
            else {
              return [
                "status" => FALSE,
                "message" => "Car is already released"
              ];
            }
          } 
          else {
            return [
              "status" => FALSE,
              "message" => "Not Match with type of vehcile"
            ];
          }
        } 
        catch (PDOException $e) {
          error_log("Error: " . $e->getMessage());
          return [
            "status" => FALSE,
            "message" => "error"
          ];
        }
      } 
      else {
        return [
          "status" => FALSE,
          "message" => "Car is not booked any park yet."
        ];
      }
    } 
    catch (PDOException $e) {
      error_log("Error: " . $e->getMessage());
      return [
        "status" => FALSE,
        "message" => "error"
      ];
    }
  }

  /**
   * The getSlots function will return the number of empty slots.
   * 
   * @return array
   */
  public function getSlots(): array {
    try {
      $sql = "SELECT COUNT(id) AS slots, vehicle_type FROM tickets WHERE status = 'Booked' GROUP BY vehicle_type";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();
      $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $row;
    } 
    catch (PDOException $e) {
      error_log("Error: " . $e->getMessage());
      return [
        "status" => FALSE,
        "message" => "error"
      ];
    }
  }

  /**
   * The checkCarAlreadyRelease funtion will check the car is released or not.
   * 
   * @param $vehicle_number 
   * The vehilcle number of parked car. 
   * 
   * @param $vehcile_type 
   * The vehilce type of which type of car it is.
   * 
   * @return bool
   */
  public function checkCarAlreadyRelease($vehicle_number, $vehicle_type): bool {
    try {
      $sql = "SELECT * FROM tickets  WHERE vehicle_number = ? AND vehicle_type = ? AND status = 'Released'";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute([$vehicle_number, $vehicle_type]);
      if ($stmt->rowCount() > 0) {
        return FALSE;
      } 
      else {
        return TRUE;
      }
    } 
    catch (PDOException $e) {
      error_log("Error: " . $e->getMessage());
      return FALSE;
    }
  }

  /**
   * The checkCarAlreadyParked function will check the car is already parked or not.
   * 
   * @param $vehicle_number 
   * The vehicle number that will check the car is already parked or not.
   * 
   * @param $vehcile_type
   * The vehicle type that will check the car is already parked or not.
   * 
   * @return bool
   */
  public function checkCarAlreadyParked($vehicle_number, $vehcile_type): bool {
    try {
      $sql = "SELECT * FROM tickets WHERE vehicle_number = ? AND vehicle_type = ? AND status = 'Booked'";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute([$vehicle_number, $vehcile_type]);
      if ($stmt->rowCount() > 0) {
        return FALSE;
      } 
      else {
        return TRUE;
      }
    } 
    catch (PDOException $e) {
      error_log("Error: " . $e->getMessage());
      return FALSE;
    }
  }

  /**
   * The getTicket function will get the ticket from the database and return to the user.
   * 
   * @return array
   */
  public function getTicket(): array {
    try {
      $sql = "SELECT * FROM tickets";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();
      $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $row;
    } 
    catch (PDOException $e) {
      error_log("Error: " . $e->getMessage());
      return [
        "status" => FALSE,
        "message" => "error"
      ];
    }
  }
}
?>
