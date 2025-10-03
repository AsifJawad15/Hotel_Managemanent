<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_hotel.php");

$hid = (int)$_SESSION['hotel_id'];
$serviceTypes = ['Spa','Restaurant','Room Service','Transport','Laundry','Other'];

if (isset($_POST['save'])) {
  $nameRaw = trim($_POST['service_name'] ?? '');
  $name = esc($nameRaw);

  $typeInput = trim($_POST['service_type'] ?? 'Other');
  if (!in_array($typeInput, $serviceTypes, true)) {
    $typeInput = 'Other';
  }
  $type = esc($typeInput);

  $descriptionRaw = trim($_POST['description'] ?? '');
  $descriptionEsc = esc($descriptionRaw);

  $priceInput = trim($_POST['price'] ?? '');
  $priceValue = $priceInput === '' ? 0 : max(0, (float)$priceInput);
  $priceSql = sprintf('%.2f', $priceValue);

  $isActive = isset($_POST['is_active']) ? 1 : 0;

  if ($nameRaw !== '') {
    $descriptionSql = $descriptionRaw !== '' ? "'$descriptionEsc'" : "NULL";
    $query = "INSERT INTO services (hotel_id, service_name, description, price, service_type, is_active)
          VALUES ($hid, '$name', $descriptionSql, $priceSql, '$type', $isActive)";
        if ($conn->query($query)) {
            header("Location: hotel_services.php");
            exit();
        } else {
            $error = "Failed to add service: " . $conn->error;
        }
    } else {
        $error = "Service name is required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../../css/style.css">
  <title>Add Service</title>
</head>
<body>
<div class="header">
  <div>Add Service</div>
  <div class="nav"><a href="hotel_services.php">Back</a></div>
</div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group">
      <label>Service Name*</label>
      <input type="text" name="service_name" required>
    </div>
    <div class="form-group">
      <label>Service Type</label>
      <select name="service_type">
        <?php foreach ($serviceTypes as $typeOption): ?>
          <option value="<?= $typeOption ?>"><?= $typeOption ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Price</label>
      <input type="number" step="0.01" name="price" min="0" placeholder="Leave blank for 0.00">
    </div>
    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="4" placeholder="Short description of the service"></textarea>
    </div>
    <div class="form-group">
      <label>
        <input type="checkbox" name="is_active" checked>
        Service is Active
      </label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" name="save">Add Service</button>
      <a href="hotel_services.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
</body>
</html>
