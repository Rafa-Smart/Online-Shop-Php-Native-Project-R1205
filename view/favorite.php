<?php
require_once("../connect/connection.php");
global $connection;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
  $id = intval($_POST["id"]); // ini tuh buat ambil value integer
  $query = "SELECT 
              p.id as idProduct,
              p.name as namaProduct,
              p.price as priceProduct,
              (p.price * f.amount) as totalPrice,
              p.img as imgProduct,
              p.stock as stockProduct,
              f.amount as amountProduct,
              (p.stock - f.amount) as stockNow,
              p.category as categoryProduct,
              f.created_at as created_at,
              f.update_at as update_at
              FROM tabel_products as p
              INNER JOIN tabel_favorites as f
              ON (f.id_product = p.id)
              WHERE p.id = $id;";
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
  <title>favorite Page</title>
  <link rel="stylesheet" href="../styles/favorite.css?v=7">
  <link rel="stylesheet" href="../styles/navbar.css?v=9">

</head>

<body>


  <nav class="navbar">
    <div class="logo">Khadafi Shop</div>
    <ul class="nav-links">
      <li><a href="../index.php">Products</a></li>
      <li><a href="orders.php">Orders</a></li>
      <li><a href="">Favorites</a></li>
      <li><a href="wishlist.php">Wishlist</a></li>
    </ul>
  </nav>

  <div class="container">
    <?php

    // nah nanti disini didek, jika user klik button priority yg paling besr, maka kasih fungis yg
    // select berdasarkan prioity terbesarnya, elsenya biasa aja
    

    if (isset($_POST["favorite-now"])) {
      createData();
    }
    if (isset($_POST["favorite-update"])) {
      updateData();
    }
    if (isset($_POST["favorite-delete"])) {
      deleteData();
    }
    tampilProducts();

    ?>
  </div>
  <!-- ini buat si info -->
  <div class="overlay-favorite" id="popup-favorite">
    <div class="popup-box-favorite">

    </div>
  </div>

  <!-- ini buat si order -->
  <div class="overlay-order-favorite" id="popup-order-favorite">
    <div class="popup-box-order-favorite"></div>
  </div>



  <!-- untuk si update boxnyah -->
  <div class="overlay-update-favorite" id="popup-update-favorite">
    <div class="popup-box-update-favorite"></div>
  </div>

  <!-- buat si wishlistnya -->
  <div class="overlay-wishlist-favorite" id="popup-wishlist-favorite">
    <div class="popup-box-wishlist-favorite"></div>
  </div>


  <script src="../scripts/favorite.js?v=7"></script>
</body>

</html>

<?php

function deleteData()
{
  global $connection;
  if (isset($_POST["favorite-delete"])) {
    $idProduct = $_POST["idProduct"];
    $created_at = $_POST["created_at"];

    $sql = "DELETE FROM tabel_favorites WHERE id_product = '$idProduct' AND created_at = '$created_at'";
    $hasil = mysqli_query($connection, $sql);
  }
}

function updateData()
{
  global $connection;

  if (isset($_POST['favorite-update'])) {
    $product_id = $_POST['product_id_baru'];
    $product_id_lama = $_POST['product_id_lama'];
    $amount_product = $_POST['amount-order'];
    $format_date = $_POST['format-date'];
    $waktu_product = $_POST['waktu-product'];

    $sql = "
      UPDATE tabel_favorites
      SET id_product = '$product_id',
          amount = '$amount_product',
          update_at = '$format_date'
      WHERE created_at = '$waktu_product' AND id_product = '$product_id_lama';
    ";

    $hasilnya = mysqli_query($connection, $sql);
  }

}


function createData()
{
  global $connection;

  if (isset($_POST["favorite-now"])) {
    $id = intval($_POST["product_id"]);
    $amount = $_POST["amount-favorite"];
    $sql = "
        INSERT INTO tabel_favorites (id_product, amount)
        VALUES ('$id', '$amount');
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
              p.price as priceProduct,
              p.img as imgProduct,
              f.amount as amountProduct,
              f.created_at as created_at
              FROM tabel_products as p
              INNER JOIN tabel_favorites as f
              ON (f.id_product = p.id)
              LEFT JOIN tabel_orders AS o ON o.id_product = p.id 
              WHERE o.id_product IS NULL
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
    $product_amount = $row["amountProduct"];
    $product_img = $row["imgProduct"];
    $created_at = $row["created_at"];

    $hasilGambar = $product_img;


    if (strpos($hasilGambar, 'uploads') !== false) {
      $hasilGambar = '../' . $product_img;
    } else {
      $hasilGambar = $product_img;
    }

    echo '<div class="product-card">';
    echo '<img src="' . $hasilGambar . '" alt="Product">';
    echo '<h3>' . htmlspecialchars($product_name) . '</h3>';
    echo '<p>Rp ' . number_format($product_price, 0, ',', '.') . '</p>';
    echo '<div class="btn-group">';
    // tombol lain
    echo '<button type="submit" name="order-now" class="btn order" onclick="buatOrderFavorite(' . $product_id . ')">Order Now</button>';
    echo '<button type="button" name="add-to-wishlist" class="btn order" onclick="buatWishlist(' . $product_id . ')">Add to Wishlist</button>';
    echo '<button id="info" onclick="pasBukaInfoFavorite(' . $product_id . ')" class="btn order">Detail Favorite</button>';

    echo "<div id='group-button-update-delete' style='display:flex; gap:8px; margin-top:6px;'>";

    // ✅ Tombol Update dengan warna #03dac6
    echo '<button id="button-order-update"
                 style="background-color:#379a6dff; color:#fff; border:none; padding:6px 20px; border-radius:4px; cursor:pointer;"
                 onclick="updateFavorite('
      . $product_id . ', '
      . $product_amount . ', '
      . htmlspecialchars(json_encode($created_at), ENT_QUOTES, 'UTF-8')
      . ')">Update</button>';

    // ✅ Tombol Delete dengan warna #f44336
    echo "<form action='' method='POST' style='margin:0;'>
              <input type='hidden' name='created_at' value='$created_at'>
              <input type='hidden' name='idProduct' value='$product_id'>
              <button type='submit' id='button-order-delete' name='favorite-delete'
                      style='background-color:#f44336; color:#fff; border:none; padding:6px 18px; border-radius:4px; cursor:pointer;'>
                      Delete
              </button>
          </form>";

    echo "</div>"; // group-button
    echo '</div>'; // btn-group
    echo '</div>'; // product-card
  }

}
?>
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->
<!-- test -->