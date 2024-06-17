<?php
include("adheader.php");
include 'dbconnection.php';

if(!isset($_SESSION[doctorid]))
{
  echo "<script>window.location='doctorlogin.php';</script>";
}
?>

<div class="container-fluid">
  <div class="block-header">
    <h2>Welcome <?php  
    $sql="SELECT * FROM doctor WHERE doctorid='$_SESSION[doctorid]' ";
    $doctortable = mysqli_query($con,$sql);
    $doc = mysqli_fetch_array($doctortable);
    echo 'Dr. '. $doc[doctorname]; 
    ?>
  </h2>
  </div>
</div>

<div class="card">
  <section class="container">
    <div class="row clearfix" style="margin-top: 10px">
      <div class="col-lg-3 col-md-3 col-sm-6">
        <div class="info-box-4 hover-zoom-effect">
          <div class="icon"> <i class="zmdi zmdi-file-plus col-blue"></i> </div>
          <div class="content">
            <div class="text">New Appoiment</div>
            <div class="number"><?php
            $sql = "SELECT * FROM appointment WHERE doctorid= '$_SESSION[doctorid]' AND appointmentdate=' ".date("Y-m-d")."'";
            $qsql = mysqli_query($con,$sql);
            echo mysqli_num_rows($qsql);
            ?></div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-6">
        <div class="info-box-4 hover-zoom-effect">
          <div class="icon"> <i class="zmdi zmdi-account col-cyan"></i> </div>
          <div class="content">
            <div class="text">Number of Patient</div>
            <div class="number"><?php
            $sql = "SELECT * FROM patient WHERE status='Active'";
            $qsql = mysqli_query($con,$sql);
            echo mysqli_num_rows($qsql);
            ?></div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-6">
        <div class="info-box-4 hover-zoom-effect">
          <div class="icon"> <i class="zmdi zmdi-account-circle col-blush"></i> </div>
          <div class="content">
            <div class="text">Today's Appointment</div>
            <div class="number">
              <?php
              $sql = "SELECT * FROM appointment WHERE status='Approved' AND doctorid= '$_SESSION[doctorid]' AND appointmentdate=' ".date("Y-m-d")."'" ;
            $qsql = mysqli_query($con,$sql);
            echo mysqli_num_rows($qsql);
            ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-6">
        <div class="info-box-4 hover-zoom-effect">
          <div class="icon"> <i class="zmdi zmdi-money col-green"></i> </div>
          <div class="content">
            <div class="text">Total Earnings</div>
            <div class="number">$ 
              <?php 
              $sql = "SELECT sum(bill_amount) as total  FROM billing_records WHERE bill_type = 'Consultancy Charge'" ;
              $qsql = mysqli_query($con,$sql);
              while ($row = mysqli_fetch_assoc($qsql))
              { 
               echo $row['total'];
             }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="container" style="margin-top: 20px;">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Brain Tumor Analysis</h3>
        </div>
        <div class="card-body">
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
              <label for="patientName">Patient Name</label>
              <input type="text" class="form-control" id="patientName" name="patientName" required>
            </div>
            <div class="form-group">
              <label for="scanImage">Upload Brain Scan Image</label>
              <input type="file" class="form-control-file" id="scanImage" name="scanImage" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['scanImage'])) {

  $errors = []; // Array to store any errors

  // Validate file size
  if ($_FILES["scanImage"]["size"] > 500000) {
    $errors[] = "File size is too large (max 5 MB)";
  }

  // Validate allowed file types
  $allowed_extensions = ["jpg", "jpeg", "png"];
  $file_extension = pathinfo($_FILES["scanImage"]["name"], PATHINFO_EXTENSION);
  if (!in_array(strtolower($file_extension), $allowed_extensions)) {
    $errors[] = "Only JPG, JPEG, and PNG files allowed";
  }

  // Validate file content (consider using libraries for advanced checks)
  // ... (e.g., image validation library)

  if (empty($errors)) {

    // Secure upload directory (replace with your desired path)
    $upload_dir = "uploads/"; // Change this to your upload directory

    // Generate unique filename
    $new_filename = time() . "_" . basename( $_FILES["scanImage"]["name"]);

    // Move uploaded file securely
    if (move_uploaded_file($_FILES["scanImage"]["tmp_name"], $upload_dir . $new_filename)) {
      echo "<p>The file " . basename( $_FILES["scanImage"]["name"]) . " has been uploaded.</p>";

      // **Prediction based on uploaded image:**

      // 1. Construct the complete upload path using the dynamic variables
      $image_path = $upload_dir . $new_filename;

      // 2. Load the image using the constructed path
      $image = imagecreatefromjpeg($image_path); // Assuming JPEG format, adjust for other formats

      // 3. Process and resize the image (adjust based on your model's requirements)
      $resized_image = imagescale($image, 224, 224); // Assuming your model expects 224x224 image

      // Load the ML model (replace with your loading logic)
      $unpickler = new Unpickler('model_params.pkl'); // Assuming phpickle
      $model = $unpickler->unpickle();

      // Make prediction using the resized image
      $prediction = $model->predict($resized_image);

      // Display results
      echo "<p>Patient Name: " . $_POST['patientName'] . " (if provided)</p>";
      echo "<p>Prediction: " . $prediction . "</p>";

      // Optionally delete the uploaded image after processing
      // unlink($image_path); // Comment out if you want to keep uploads

    } else {
      echo "<p>Sorry, there was an error uploading your file.</p>";
    }
  } else {
    echo "<p><b>Error(s):</b></p>";
    foreach ($errors as $error) {
      echo "<p>" . $error . "</p>";
    }
  }
}
// Include footer (assuming adfooter.php)
include("adfooter.php");
?>