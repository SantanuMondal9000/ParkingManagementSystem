$(document).ready(function () {

  //Entery the parking.
  $('#entryForm').on('submit', function (e) {
    e.preventDefault();
    console.log($(this).serialize());
    $.ajax({
      url: './php/controler.php',
      type: 'POST',
      dataType: 'json',
      data: $(this).serialize(),
      success: function (response) {
        console.log(response);
        $("#parking-status").text(response.message);
        $("input").val("");
        getEmptySlots();
        if (response.message != "Car already parked") {
          addTicket(response.result_set);
        }
      },
      error: function (xhr, status, error) {
        console.log("AJAX Error: " + status + " - " + error);
        console.log(xhr.responseText);
      }
    });
  });

  //Release the parking.
  $('#release-form').on('submit', function (e) {
    e.preventDefault();
    console.log($(this).serialize());
    $.ajax({
      url: './php/controler.php',
      type: 'POST',
      dataType: 'json',
      data: $(this).serialize(),
      success: function (response) {
        console.log(response);
        $("#release-status").text(response.message);
        getEmptySlots();
        updateTicketStatus(response.ticket_id, response.ticket_status, response.ticket_exit_time);
      },
      error: function (xhr, status, error) {
        console.log("AJAX Error: " + status + " - " + error);
        console.log(xhr.responseText);
      }
    });
  });

  //Get the avleable slot.
  getEmptySlots();

  //Get the tickets.
  getTickets()
});

//Get the empty slots avilable.
function getEmptySlots() {
  $.ajax({
    url: './php/controler.php',
    type: 'POST',
    dataType: 'json',
    data: { getSlots: '' },
    success: function (response) {
      let available = {
        "2wheeler": 100,
        "4wheeler": 100
      };
      response.forEach(function (item) {
        if (item.vehicle_type === "2wheeler" || item.vehicle_type === "4wheeler") {
          available[item.vehicle_type] = 100 - item.slots;
        }
      });
      $("#two-wheeler-status").text(available["2wheeler"]);
      $("#four-wheeler-status").text(available["4wheeler"]);
    },
    error: function (xhr, status, error) {
      console.log("AJAX Error: " + status + " - " + error);
      console.log(xhr.responseText);
    }
  });
}

//Get the Tickets.
function getTickets() {
  $.ajax({
    url: './php/controler.php',
    type: 'POST',
    dataType: 'json',
    data: { getTickets: '' },
    success: function (response) {
      console.log(response);
      response.forEach(function (ticket) {
        addTicket(ticket);
      })
    },
    error: function (xhr, status, error) {
      console.log("AJAX Error: " + status + " - " + error);
      console.log(xhr.responseText);
    }
  });
}

//Function will set the ticket to the table.
function addTicket(ticket) {
  const row = `<tr> 
          <td>${ticket.id}</td>
          <td>${ticket.vehicle_number}</td>
          <td>${ticket.vehicle_type}</td>
          <td>${ticket.slot_number}</td>
          <td>${ticket.entry_time}</td>
          <td id="ticket-exit-time-${ticket.id}">${ticket.exit_time ? ticket.exit_time : '__'}</td>
          <td id="ticket-status-${ticket.id}">${ticket.status}</td>
        </tr > `;
  $("#ticket-table").append(row);
}

//Function to update the status of ticket.
function updateTicketStatus(ticket_id, new_status, exit_time) {
  $(`#ticket-status-${ticket_id}`).text(new_status);
  $(`#ticket-exit-time-${ticket_id}`).text(exit_time);
}
