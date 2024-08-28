<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
            margin: 0; /* Remove default margin */
        }
        .task-manager {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
            color: #343a40;
        }
        #taskInput {
            margin-bottom: 15px;
        }
        .btn {
            margin-right: 5px;
            margin-top: 10px;
        }
        .task-table thead th {
            background-color: #343a40;
            color: white;
        }
        .task-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="task-manager">
        <h1>Task Manager</h1>
        <div class="form-group">
            <input type="text" class="form-control" id="taskInput" placeholder="Enter task...">
        </div>
        <div class="d-flex justify-content-center mb-3">
            <button class="btn btn-primary" id="addTaskBtn">Add Task</button>
            <button class="btn btn-secondary" id="showAllTasksBtn">Show All Tasks</button>
        </div>
        <div id="taskList">
            <table class="table table-bordered task-table">
                <thead>
                    <tr>
                        <th>S no</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="taskTableBody">
                    <!-- Rows will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            let showingAllTasks = false;
            // Function to fetch and display tasks
            function fetchTasks() {
                $.get('/showtasks', function(tasks) {
                    let taskTableBody = $('#taskTableBody');
                    taskTableBody.empty(); // Clear existing tasks

                    console.log(tasks);

                    tasks.forEach(function(task, index) {
                        taskTableBody.append(`
                            <tr>
                           
                                <td>${index + 1}</td>
                                <td>${task.title}</td>
                                <td>${task.completed ? 'Completed' : 'Pending'}</td>
                                <td>
                                    <input type="checkbox" class=" btn btn-primary complete-checkbox" data-id = "${task.id}"  ${task.completed ? 'checked' : ''}>
                                    <button class="btn btn-danger btn-sm delete-btn delete-task-btn" data-id="${task.id}" >Delete</button>
                                </td>
                            </tr>
                        `);
                    });
                }).fail(function(xhr) {
                    alert('Failed to fetch tasks');
                });
            }

            function fetchAllTasks() {
                $.get('/alltasks', function (tasks) {
                    let taskTableBody = $('#taskTableBody');
                    taskTableBody.empty(); // Clear existing tasks

                    tasks.forEach(function (task, index) {
                        taskTableBody.append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${task.title}</td>
                                <td>${task.completed ? 'Completed' : 'Pending'}</td>
                                <td>
                                    <input type="checkbox" class="complete-checkbox" data-id="${task.id}" ${task.completed ? 'checked' : ''}>
                                    <button class="btn btn-danger btn-sm delete-task-btn" data-id="${task.id}">Delete</button>
                                </td>
                            </tr>
                        `);
                    });
                }).fail(function (xhr) {
                    alert('Failed to fetch all tasks');
                });
            }

                // Toggle "Show All Tasks" functionality
                $('#showAllTasksBtn').on('click', function () {
                    if (showingAllTasks) {
                        fetchTasks(); // Fetch only incomplete tasks
                        $(this).text('Show All Tasks'); // Update button text
                    } else {
                        fetchAllTasks(); // Fetch all tasks
                        $(this).text('Show Incomplete Tasks'); // Update button text
                    }
                    showingAllTasks = !showingAllTasks; // Toggle the state
                });


                // Add new task
                $('#addTaskBtn').on('click', function () {
                    const taskTitle = $('#taskInput').val().trim();
                    if (taskTitle) {
                        $.post('/tasks', { title: taskTitle, _token: '{{ csrf_token() }}' }, function () {
                            fetchTasks(); // Refresh the task list
                            $('#taskInput').val(''); // Clear the input field
                        }).fail(function (xhr) {
                            alert(xhr.responseJSON.message);
                        });
                    } else {
                        alert('Task cannot be empty!');
                    }
                });
                

                $('#taskTableBody').on('change', '.complete-checkbox', function() {
                let taskId = $(this).data('id'); // Retrieve task ID from the checkbox
                let completed = $(this).is(':checked'); // Get the new status
                let row = $(this).closest('tr'); // Get the closest <tr> element

                $.ajax({
                    url: `/tasks/${taskId}`,
                    type: 'POST',
                    data: { completed: completed, _token: '{{ csrf_token() }}' },
                    success: function() {
                        if (completed) {
                            row.remove(); // Remove the task row from the table if marked as completed
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to update task:', xhr.responseText);
                        alert('Failed to update task');
                    }
                });
            });



            $('#taskTableBody').on('click', '.delete-task-btn', function() {
                    let taskId = $(this).attr("data-id");
                    let row = $(this).closest('tr');

                    if (confirm('Are you sure you want to delete this task?')) {
                        $.ajax({
                            url: `/tasks/${taskId}`,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                row.remove(); // Remove the task row from the table
                                console.log('Task deleted:', response);
                            },
                            error: function(xhr, status, error) {
                                console.error('Failed to delete task:', xhr.responseText);
                                alert('Failed to delete task');
                            }
                        });
                    }
                });


            // Fetch tasks initially
            fetchTasks();
        });
    </script>
</body>
</html>
