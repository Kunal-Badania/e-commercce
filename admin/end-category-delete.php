<?php require_once('header.php'); ?>

<?php
// Preventing the direct access of this page.
if (!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    // Check the id is valid or not
    $statement = $pdo->prepare("SELECT * FROM tbl_end_category WHERE ecat_id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if ($total == 0) {
        header('location: logout.php');
        exit;
    }
}
?>

<?php
// Initialize the array to prevent null errors
$p_ids = [];

// Getting all product ids associated with the end category
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE ecat_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
    $p_ids[] = $row['p_id'];
}

// Check if there are any products to delete
if (!empty($p_ids)) {
    for ($i = 0; $i < count($p_ids); $i++) {
        // Getting photo ID to unlink from folder
        $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
        $statement->execute(array($p_ids[$i]));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
        foreach ($result as $row) {
            $p_featured_photo = $row['p_featured_photo'];
            if (file_exists('../assets/uploads/' . $p_featured_photo)) {
                unlink('../assets/uploads/' . $p_featured_photo);
            }
        }

        // Getting other photo ID to unlink from folder
        $statement = $pdo->prepare("SELECT * FROM tbl_product_photo WHERE p_id=?");
        $statement->execute(array($p_ids[$i]));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
        foreach ($result as $row) {
            $photo = $row['photo'];
            if (file_exists('../assets/uploads/product_photos/' . $photo)) {
                unlink('../assets/uploads/product_photos/' . $photo);
            }
        }

        // Delete from tbl_product
        $statement = $pdo->prepare("DELETE FROM tbl_product WHERE p_id=?");
        $statement->execute(array($p_ids[$i]));

        // Delete from tbl_product_photo
        $statement = $pdo->prepare("DELETE FROM tbl_product_photo WHERE p_id=?");
        $statement->execute(array($p_ids[$i]));

        // Delete from tbl_product_size
        $statement = $pdo->prepare("DELETE FROM tbl_product_size WHERE p_id=?");
        $statement->execute(array($p_ids[$i]));

        // Delete from tbl_product_color
        $statement = $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id=?");
        $statement->execute(array($p_ids[$i]));

        // Delete from tbl_rating
        $statement = $pdo->prepare("DELETE FROM tbl_rating WHERE p_id=?");
        $statement->execute(array($p_ids[$i]));

        // Delete from tbl_payment
        $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE product_id=?");
        $statement->execute(array($p_ids[$i]));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
        foreach ($result as $row) {
            $statement1 = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
            $statement1->execute(array($row['payment_id']));
        }

        // Delete from tbl_order
        $statement = $pdo->prepare("DELETE FROM tbl_order WHERE product_id=?");
        $statement->execute(array($p_ids[$i]));
    }
}

// Delete from tbl_end_category
$statement = $pdo->prepare("DELETE FROM tbl_end_category WHERE ecat_id=?");
$statement->execute(array($_REQUEST['id']));

header('location: end-category.php');
?>