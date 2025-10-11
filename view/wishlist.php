<?php
require_once("../connect/connection.php");
global $connection;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
  $id = intval($_POST["id"]); // ini tuh buat ambil value integer
  $query = "SELECT 
              p.id as idProduct,
              p.name as namaProduct,
              p.price as priceProduct,
              (p.price * w.amount) as totalPrice,
              p.img as imgProduct,
              p.stock as stockProduct,
              w.amount as amountProduct,
              (p.stock - w.amount) as stockNow,
              p.category as categoryProduct,
              w.priority as priorityProduct,
              w.notes as notesProduct,
              w.created_at as created_at,
              w.update_at as update_at
              FROM tabel_products as p
              INNER JOIN tabel_wishlist as w
              ON (w.id_product = p.id)
              WHERE w.id_product = '$id'
              ;";
  $result = mysqli_query($connection, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);

    $hasilGambar = $product["imgProduct"];


    if (strpos($hasilGambar, 'uploads') !== false) {
      $hasilGambar = '../' . $product["imgProduct"];
    } else {
      $hasilGambar = $product["imgProduct"];
    }

    echo '<div class="detail-card">';

    // Kiri
    echo '  <div class="detail-left">';
    echo '    <img src="' . htmlspecialchars($hasilGambar) . '" alt="Product Image">';
    echo '    <h2>' . htmlspecialchars($product["namaProduct"]) . '</h2>';
    echo '    <p class="category">Kategori: ' . htmlspecialchars($product["categoryProduct"]) . '</p>';
    echo '  </div>';

    // Kanan
    echo '  <div class="detail-right">';
    echo '    <table class="detail-table">';
    echo '      <tr><th>Stock</th><td>' . $product["stockProduct"] . '</td></tr>';
    echo '      <tr><th>Amount</th><td>' . $product["amountProduct"] . '</td></tr>';
    echo '      <tr><th>Stock Now</th><td>' . $product["stockNow"] . '</td></tr>';
    echo '      <tr><th>Price</th><td>Rp ' . number_format($product["priceProduct"], 0, ',', '.') . '</td></tr>';
    echo '      <tr><th>Notes</th><td>' . $product["notesProduct"] . '</td></tr>';
    echo '      <tr><th>Total Price</th><td>Rp ' . number_format($product["totalPrice"], 0, ',', '.') . '</td></tr>';
    echo '      <tr><th>Created At</th><td>' . htmlspecialchars($product["created_at"]) . '</td></tr>';
    echo '      <tr><th>Updated At</th><td>' . ($product["update_at"] ? $product["update_at"] : "belum diupdate") . '</td></tr>';
    echo '    </table>';
    echo '  </div>';

    echo '</div>'; // detail-card

  } else {
    echo "Produk tidak ditemukan.";
  }
  exit; // WAJIB agar sisa HTML tidak dikirim
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wihslist Page</title>
  <link rel="stylesheet" href="../styles/wishlist.css?v=2">
  <link rel="stylesheet" href="../styles/navbar.css?v=7">

</head>

<body>


  <nav class="navbar">
    <div class="logo">Khadafi Shop</div>
    <ul class="nav-links">
      <li><a href="../index.php">Products</a></li>
      <li><a href="orders.php">Orders</a></li>
      <li><a href="favorite.php">Favorites</a></li>
      <li><a href="">Wishlist</a></li>
    </ul>
  </nav>

  <div class="container">
    <?php

    // nah nanti disini didek, jika user klik button priority yg paling besr, maka kasih fungis yg
    // select berdasarkan prioity terbesarnya, elsenya biasa aja
    

    if (isset($_POST["wishlist-now"])) {
      createData();
    }
    if (isset($_POST["wishlist-update"])) {
      updateData();
    }
    if (isset($_POST["wishlist-delete"])) {
      deleteData();
    }
    tampilProducts();

    ?>
  </div>
  <!-- ini buat si info -->
  <div class="overlay-wishlist" id="popup-wishlist">
    <div class="popup-box-wishlist">

    </div>
  </div>

  <!-- ini buat si order -->
  <div class="overlay-order-wishlist" id="popup-order-wishlist">
    <div class="popup-box-order-wishlist"></div>
  </div>

  <!-- ini buat si favorite -->
  <div class="overlay-favorite" id="popup-favorite">
    <div class="popup-box-favorite"></div>
  </div>

  <!-- untuk si update boxnyah -->
  <div class="overlay-update-wishlist" id="popup-update-wishlist">
    <div class="popup-box-update-wishlist"></div>
  </div>


  <script src="../scripts/wishlist.js?v=2"></script>
</body>

</html>

<?php

function deleteData()
{
  global $connection;
  if (isset($_POST["wishlist-delete"])) {
    $idProduct = $_POST["idProduct"];
    $created_at = $_POST["created_at"];

    $sql = "DELETE FROM tabel_wishlist WHERE id_product = '$idProduct' AND created_at = '$created_at'";
    $hasil = mysqli_query($connection, $sql);
  }
}

function updateData()
{
  global $connection;

  if (isset($_POST['wishlist-update'])) {
    $product_id = $_POST['product_id_baru'];
    $product_id_lama = $_POST['product_id_lama'];
    $amount_product = $_POST['amount-order'];
    $priority = $_POST['priority'];
    $notes = $_POST['note'];
    $format_date = $_POST['format-date'];
    $waktu_product = $_POST['waktu-product'];

    $sql = "
      UPDATE tabel_wishlist 
      SET id_product = '$product_id',
          amount = '$amount_product',
          priority = '$priority',
          notes = '$notes',
          update_at = '$format_date'
      WHERE created_at = '$waktu_product' AND id_product = '$product_id_lama';
    ";

    $hasilnya = mysqli_query($connection, $sql);
  }

}


function createData()
{
  global $connection;

  if (isset($_POST["wishlist-now"])) {
    $id = intval($_POST["product_id"]);
    $amount = $_POST["amount-wishlist"];
    $note = $_POST["note-wishlist"];
    $priority = $_POST["priority-wishlist"];
    $sql = "
        INSERT INTO tabel_wishlist (id_product, amount, priority, notes)
        VALUES ('$id', '$amount', '$priority', '$note');
    ";
    $hasil = mysqli_query($connection, $sql);
  }

}



function tampilProducts()
{
  global $connection;
  $query = "SELECT 
              p.id as idProduct,
              p.name as namaProduct,
              p.img as imgProduct,
              w.amount as amountProduct,
              p.price * w.amount as priceProduct,
              w.priority as priorityProduct,
              w.notes as notesProduct,
              w.created_at as created_at
              FROM tabel_products as p
              INNER JOIN tabel_wishlist as w
              ON (w.id_product = p.id)
              LEFT JOIN tabel_orders AS o ON o.id_product = p.id 
              WHERE o.id_product IS NULL
              ORDER BY priorityProduct DESC
              ;";
  // jadi hanya ingin barnag yg tidak ada di order, klao ada maka ga mau

  // jadinya pas kita order, maka kita hapus dari wishlist
  $hasil = mysqli_query($connection, $query);
  // di cek dulu nih
  if (!$hasil) {
    echo "Query gagal: " . mysqli_error($connection);
    return;
  }
  while ($row = mysqli_fetch_assoc($hasil)) {
    $product_id = $row["idProduct"];
    $product_name = $row["namaProduct"];
    $product_price = $row["priceProduct"];
    $product_priority = $row["priorityProduct"];
    $product_notes = $row["notesProduct"];
    $product_amount = $row["amountProduct"];
    $product_img = $row["imgProduct"];
    $created_at = $row["created_at"];

    $hasilGambar = $row["imgProduct"];


    if (strpos($hasilGambar, 'uploads') !== false) {
      $hasilGambar = '../' . $row["imgProduct"];
    } else {
      $hasilGambar = $row["imgProduct"];
    }

    echo '<div class="product-card">';
    echo '<img src="' . $hasilGambar . '" alt="Product">';
    echo '<h3>' . $product_priority . ' %</h3>';
    echo '<h3>' . htmlspecialchars($product_name) . '</h3>';
    echo '<p>Rp ' . number_format($product_price, 0, ',', '.') . '</p>';
    echo '<div class="btn-group">';
    // tombol lain
    echo '<button type="submit" name="order-now" class="btn order" onclick="buatOrderWishlist(' . $product_id . ')">Order Now</button>';
    echo '<button type="submit" name="add-to-favorites" class="btn order" onclick="buatFavorite(' . $product_id . ')">Add to Favorites</button>';
    echo '<button id="info" onclick="pasBukaInfoWishlist(' . $product_id . ')" class="btn order" >Detail Wishlist</button>';

    echo "<div id='group-button-update-delete' style='display:flex; gap:8px; margin-top:6px;'>";

    // ✅ Tombol Update dengan warna #379a6dff
    echo '<button id="button-order-update" 
                 style="background-color:#379a6dff; color:#fff; border:none; padding:6px 20px; border-radius:4px; cursor:pointer;" 
                 onclick="updateWishlist('
      . $product_id . ', '
      . "'" . htmlspecialchars($product_priority) . "', "
      . "'" . htmlspecialchars($product_notes) . "', "
      . $product_amount . ', '
      . "'" . htmlspecialchars($created_at) . "'"
      . ')">Update</button>';

    // ✅ Tombol Delete dengan warna #f44336
    echo "<form action='' method='POST' style='margin:0;'> 
              <input type='hidden' name='created_at' value='$created_at'>
              <input type='hidden' name='idProduct' value='$product_id'>
              <button type='submit' id='button-order-delete' name='wishlist-delete' 
                      style='background-color:#f44336; color:#fff; border:none; padding:6px 18px; border-radius:4px; cursor:pointer;'>
                      Delete
              </button>
          </form>";

    echo "</div>"; // si group button
    echo '</div>'; // si btn-group
    echo '</div>'; // si product-card
  }

}
?>