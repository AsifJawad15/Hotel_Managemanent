<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_hotel.php");

$hid = (int)$_SESSION['hotel_id'];
$id = (int)($_GET['id'] ?? 0);
$serviceTypes = ['Spa','Restaurant','Room Service','Transport','Laundry','Other'];

$service = $conn->query("SELECT * FROM services WHERE service_id=$id AND hotel_id=$hid")->fetch_assoc();
if (!$service) {
    header("Location: hotel_services.php");
    exit();
}

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
        $query = "UPDATE services SET
                    service_name = '$name',
          service_type = '$type',
                    description = $descriptionSql,
          price = $priceSql,
          is_active = $isActive,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE service_id=$id AND hotel_id=$hid";
        if ($conn->query($query)) {
            header("Location: hotel_services.php");
            exit();
        } else {
            $error = "Failed to update service: " . $conn->error;
        }
    } else {
        $error = "Service name is required.";
    }
}

$priceDisplay = $service['price'] !== null ? $service['price'] : '';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../../css/style.css">
  <title>Edit Service</title>
</head>
<body>
<div class="header">
  <div>Edit Service</div>
  <div class="nav"><a href="hotel_services.php">Back</a></div>
</div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group">
      <label>Service Name*</label>
      <input type="text" name="service_name" value="<?= htmlspecialchars($service['service_name']) ?>" required>
    </div>
    <div class="form-group">
      <label>Service Type</label>
      <select name="service_type">
        <?php foreach ($serviceTypes as $typeOption): ?>
          <option value="<?= $typeOption ?>" <?= ($service['service_type'] ?? 'Other') === $typeOption ? 'selected' : '' ?>><?= $typeOption ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Price</label>
      <input type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars($priceDisplay) ?>">
    </div>
    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="4"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>
        <input type="checkbox" name="is_active" <?= !empty($service['is_active']) ? 'checked' : '' ?>>
        Service is Active
      </label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" name="save">Update Service</button>
      <a href="hotel_services.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
</body>
</html>
