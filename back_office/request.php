<?php
// Include the necessary file for database connection
include('db.php'); // Make sure this file connects to the database

// Fetch the requests data from the database, joining necessary tables
$query = "
    SELECT r.id_request, u.name AS beneficiary_name, u.adress AS beneficiary_adress, u.phone_number AS beneficiary_phone, 
           d.name AS equipment_name, r.date_demande, r.approved, r.dateReponse
    FROM requests r
    INNER JOIN users u ON r.user_id = u.id
    INNER JOIN dons_equipment d ON r.id_equipment = d.id_equipment
";

$result = $conn->query($query);

// Check if the query was successful
if (!$result) {
    die("Error in query: " . $conn->error);
}

// Handle the status update
if (isset($_POST['update_status'])) {
    $id_request = $_POST['id_request'];
    $status = $_POST['status'];
    $dateReponse = date('Y-m-d H:i:s'); // Current date and time

    // Update the status and dateReponse in the database
    $update_query = "UPDATE requests SET approved = ?, dateReponse = ? WHERE id_request = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ssi', $status, $dateReponse, $id_request);
    $stmt->execute();
    
    // Redirect back to the same page to see the updated status
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle the delete request
if (isset($_POST['delete_request'])) {
    $id_request = $_POST['id_request'];

    // Delete the request from the database
    $delete_query = "DELETE FROM requests WHERE id_request = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $id_request);
    $stmt->execute();

    // Redirect back to the same page after deleting the request
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Page</title>

    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">

    <!-- Bootstrap CSS (if not already included) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <div class="container my-4">
        <h2>Requests for Equipment</h2>
        <table id="requestTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Beneficiary Name</th>
                    <th>Beneficiary Address</th>
                    <th>Phone Number</th>
                    <th>Equipment Name</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Date Response</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id_request']; ?></td>
                        <td><?php echo $row['beneficiary_name']; ?></td>
                        <td><?php echo $row['beneficiary_adress']; ?></td>
                        <td><?php echo $row['beneficiary_phone']; ?></td>
                        <td><?php echo $row['equipment_name']; ?></td>
                        <td><?php echo $row['date_demande']; ?></td>
                        <td><?php echo $row['approved']; ?></td>
                        <td><?php echo $row['dateReponse']; ?></td>
                        <td>
                            <!-- Status Update Form -->
                            <form method="POST" action="" class="mb-2">
                                <input type="hidden" name="id_request" value="<?php echo $row['id_request']; ?>">
                                <select name="status" class="form-select" required>
                                    <option value="En cours" <?php echo ($row['approved'] == 'En cours') ? 'selected' : ''; ?>>En cours</option>
                                    <option value="Acceptée" <?php echo ($row['approved'] == 'Acceptée') ? 'selected' : ''; ?>>Acceptée</option>
                                    <option value="Rejetée" <?php echo ($row['approved'] == 'Rejetée') ? 'selected' : ''; ?>>Rejetée</option>
                                    <option value="En attente" <?php echo ($row['approved'] == 'En attente') ? 'selected' : ''; ?>>En attente</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-2">Update Status</button>
                            </form>

                            <!-- Delete Request Form -->
                            <form method="POST" action="" class="mb-2" onsubmit="return confirmDelete()">
    <input type="hidden" name="id_request" value="<?php echo $row['id_request']; ?>">
    <button type="submit" name="delete_request" class="btn btn-danger btn-sm">Delete</button>
</form>

<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this request?");
    }
</script>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Include jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    <script>
        // Initialize the DataTable
        $(document).ready(function() {
            $('#requestTable').DataTable();
        });
    </script>
</body>
</html>