<?php

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}


include 'db.php';
include 'header.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['id'];
    $donateur_sql = "SELECT id_donateur FROM donateur WHERE user_id = '$user_id'";
    $donateur_result = mysqli_query($conn, $donateur_sql);

    if (!$donateur_result) {
        $message = "Erreur dans la requête SQL : " . mysqli_error($conn);
    } elseif ($donateur_row = mysqli_fetch_assoc($donateur_result)) {
        $id_donateur = $donateur_row['id_donateur'];


        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $quantite = $_POST['quantite'];
        $etat = $_POST['etat'];
        $type_equipment = $_POST['type_equipment'];

        if ($type_equipment === 'autre' && !empty($_POST['autre_type'])) {
            $type_equipment = $_POST['autre_type'];
        }

        $description = $_POST['description'];
        $image_path = "";

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = "uploads/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $image_name = basename($_FILES['image']['name']);
            $image_path = $upload_dir . $image_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $message = "Erreur lors de l'upload du fichier.";
                $image_path = "";
            }
        }

        // Insert into 'dons_equipment' table
        $sql = "INSERT INTO dons_equipment (id_donateur, name, quantite, etat, date_don, image_path, type_equipment, description) 
                VALUES ('$id_donateur', '$name', '$quantite', '$etat', NOW(), '$image_path', '$type_equipment', '$description')";

        if (mysqli_query($conn, $sql)) {
            $message = "Équipement ajouté avec succès.";
        } else {
            $message = "Erreur lors de l'ajout de l'équipement : " . mysqli_error($conn);
        }
    } else {
        $message = "Erreur : Vous n'êtes pas enregistré comme donateur.";
    }
}

// Close the connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un équipement médical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // Toggle the input field for 'autre' type
        function toggleAutreField() {
            const typeEquipement = document.getElementById('type_equipment');
            const autreInput = document.getElementById('autre_input');
            if (typeEquipement.value === 'autre') {
                autreInput.style.display = 'block';
            } else {
                autreInput.style.display = 'none';
            }
        }
    </script>
</head>

<body class="">
    <div class="d-lg-flex">
        <div class="col-lg-4 ">
            <img src="../assets/images/bg/equipment.png" alt="" class="bg-image d-none d-lg-block img-fluid position-fixed col-lg-4">
            <img src="../assets/images/bg/equipment2.png" alt="" class="bg-image2 d-lg-none img-fluid w-sm-50">

        </div>
        <div class="col-lg-8 px-5 pt-5">


            <h1 class="" id="title">Ajouter un équipement médical</h1>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label fw-b">Nom:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="type_equipment" class="form-label fw-b">Type d'équipement:</label>
                    <select id="type_equipment" name="type_equipment" class="form-control" onchange="toggleAutreField()" required>
                        <option value="béquilles">Béquilles</option>
                        <option value="chaise roulante">Chaise roulante</option>
                        <option value="prothese jambe">Prothèse jambe</option>
                        <option value="prothese main">Prothèse main</option>
                        <option value="bandage">Bandage</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="etat" class="form-label fw-b">État:</label>
                    <select id="etat" name="etat" class="form-control" required>
                        <option value="neuf">Neuf</option>
                        <option value="occasion">Occasion</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="quantite" class="form-label fw-b">Quantité:</label>
                    <input type="number" id="quantite" name="quantite" class="form-control" required>
                </div>

                <div class="mb-3" id="autre_input" style="display: none;">
                    <label for="autre_type" class="form-label fw-b">Veuillez préciser:</label>
                    <input type="text" id="autre_type" name="autre_type" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label fw-b">Description:</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label fw-b">Image (facultative):</label>
                    <input type="file" id="image" name="image" class="form-control">
                </div>                <input type="submit" value="Annuler"class="btn" id="btn1"> 

                <input type="submit" value="Ajouter"class="btn" id="btn2"> </form>
            </form>
        </div>
    </div>
</body>

</html>