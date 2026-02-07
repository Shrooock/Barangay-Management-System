<?php include "bootstrap/index.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "templates/header.php"; ?>
    <title>Calendar of Events - Barangay Services Management System</title>
    <!-- FullCalendar CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet" />
    <style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
        .fc-event {
            cursor: pointer;
        }
        /* Color Picker Styling */
        input[type="color"] {
            -webkit-appearance: none;
            border: none;
            width: 50px;
            height: 50px;
            padding: 0;
            overflow: hidden;
            border-radius: 50%;
            cursor: pointer;
        }
        input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 0;
        }
        input[type="color"]::-webkit-color-swatch {
            border: none;
            border-radius: 50%;
            padding: 0;
        }
    </style>
</head>
<body>
    <?php include "templates/loading_screen.php"; ?>
    <div class="wrapper">
        <?php include "templates/main-header.php"; ?>
        <?php include "templates/sidebar.php"; ?>

        <div class="main-panel">
            <div class="content">
                <div class="panel-header bg-primary-gradient">
                    <div class="page-inner">
                        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                            <div>
                                <h2 class="text-white fw-bold">Calendar of Events</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="page-inner">
                    <div class="row mt--2">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Events Schedule</div>
                                        <div class="card-tools">
                                            <button class="btn btn-info btn-border btn-round btn-sm" data-toggle="modal" data-target="#addEventModal">
                                                <i class="fa fa-plus"></i> Add Event
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include "templates/main-footer.php"; ?>
        </div>
    </div>
    <?php include "templates/footer.php"; ?>

    <!-- Add/Edit Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Manage Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="eventForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="eventId">
                        <div class="form-group">
                            <label>Event Title</label>
                            <input type="text" class="form-control" name="title" id="eventTitle" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="start" id="eventStart" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="end" id="eventEnd">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="eventDescription"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Color</label>
                            <br>
                            <input type="color" name="color" id="eventColor" value="#3788d8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">Delete</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveEventBtn">Save Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src="assets/js/plugin/moment/moment.min.js"></script>
    <script src="assets/js/plugin/fullcalendar/fullcalendar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
        $(document).ready(function() {
            var calendar = $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                editable: true,
                events: 'model/fetch_events.php',
                selectable: true,
                selectHelper: true,
                select: function(start, end) {
                    $('#eventId').val('');
                    $('#eventForm')[0].reset();
                    $('#eventStart').val(moment(start).format('YYYY-MM-DDTHH:mm'));
                    $('#eventEnd').val(moment(end).format('YYYY-MM-DDTHH:mm'));
                    $('#deleteEventBtn').hide();
                    $('#eventModalLabel').text('Add Event');
                    $('#eventModal').modal('show');
                },
                eventClick: function(event) {
                    if (event.isBlotter) {
                        Swal.fire({
                            title: event.title,
                            html: `<div class="text-left">
                                    <p><strong>Date:</strong> ${moment(event.start).format('LLL')}</p>
                                    <p><strong>Details:</strong><br>${event.description}</p>
                                    <p class="text-muted small">Manage this record in the Blotter section.</p>
                                   </div>`,
                            icon: 'info',
                            confirmButtonText: 'Close'
                        });
                        return;
                    }

                    $('#eventId').val(event.id);
                    $('#eventTitle').val(event.title);
                    $('#eventStart').val(moment(event.start).format('YYYY-MM-DDTHH:mm'));
                    $('#eventEnd').val(event.end ? moment(event.end).format('YYYY-MM-DDTHH:mm') : '');
                    $('#eventDescription').val(event.description);
                    $('#eventColor').val(event.color);
                    $('#deleteEventBtn').show();
                    $('#eventModalLabel').text('Edit Event');
                    $('#eventModal').modal('show');
                }
            });

            $('#eventForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'model/save_event.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            $('#eventModal').modal('hide');
                            calendar.fullCalendar('refetchEvents');
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            });

            $('#deleteEventBtn').on('click', function() {
                var id = $('#eventId').val();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'model/remove_event.php',
                            type: 'POST',
                            data: { id: id },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status == 'success') {
                                    $('#eventModal').modal('hide');
                                    calendar.fullCalendar('refetchEvents');
                                    Swal.fire('Deleted!', response.message, 'success');
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            }
                        });
                    }
                })
            });
            
            // Link Add Button to Modal
            $('[data-target="#addEventModal"]').click(function() {
                $('#eventId').val('');
                $('#eventForm')[0].reset();
                var now = moment().format('YYYY-MM-DDTHH:mm');
                $('#eventStart').val(now);
                $('#eventEnd').val(now);
                $('#deleteEventBtn').hide();
                $('#eventModalLabel').text('Add Event');
                $('#eventModal').modal('show');
            });
        });
    </script>
</body>
</html>
