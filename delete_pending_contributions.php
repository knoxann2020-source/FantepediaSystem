<?php
require 'admin/config/database.php';

$query = "DELETE FROM fante_dictionary WHERE status = 'pending'";
$result = mysqli_query($connection, $query);

if ($result) {
    $affected_rows = mysqli_affected_rows($connection);
    echo "Successfully deleted $affected_rows pending contributions.";
} else {
    echo "Error deleting pending contributions: " . mysqli_error($connection);
}

mysqli_close($connection);
?>
