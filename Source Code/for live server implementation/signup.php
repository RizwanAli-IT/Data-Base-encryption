<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost"; # host name
$username = ""; # database username
$password = ""; # database password
$dbname = ""; # database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Encryption settings
define('ENCRYPTION_KEY', 'enter secret key'); # enter your secret key (fJ8Y9x!rDqL3vX2@kM5c*B#Z1t7A)

function encrypt_data($data) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function validate_input($data) {
    return htmlspecialchars(stripslashes(trim($data ?? '')));  // Sanitize input
}

function generate_user_id() {
    return substr(str_shuffle("0123456789"), 0, 10);  // Generate random user ID
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $errors = [];
    // Validate and sanitize inputs
    $username = validate_input($_POST['username']);
    $email = validate_input($_POST['email']);
    $number = validate_input($_POST['number']);
    $gender = validate_input($_POST['gender']);
    $password = validate_input($_POST['password']);
    $confirm_password = validate_input($_POST['confirm-password']);
    $address = validate_input($_POST['address']);

    // Check if username already exists
    $username_check = $conn->prepare("SELECT * FROM login WHERE username = ?");
    $username_check->bind_param("s", $username);
    $username_check->execute();
    $username_check_result = $username_check->get_result();
    if ($username_check_result->num_rows > 0) {
        $errors[] = "Username already exists.";
    }

    // Check if email already exists
    $email_check = $conn->prepare("SELECT * FROM login WHERE email = ?");
    $email_check->bind_param("s", $email);
    $email_check->execute();
    $email_check_result = $email_check->get_result();
    if ($email_check_result->num_rows > 0) {
        $errors[] = "Email already exists.";
    }

    // Check if phone number already exists
    $number_check = $conn->prepare("SELECT * FROM login WHERE number = ?");
    $number_check->bind_param("s", $number);
    $number_check->execute();
    $number_check_result = $number_check->get_result();
    if ($number_check_result->num_rows > 0) {
        $errors[] = "Phone number already exists.";
    }
   
    // Validation
    if (!preg_match('/^[a-zA-Z0-9]{3,15}$/', $username)) {
        $errors[] = "Username must be 3-15 characters long and contain only letters and numbers.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $errors[] = "Email must be a valid @gmail.com address.";
    }
    if (!preg_match('/^\+92[0-9]{10}$/', $number)) {
        $errors[] = "Phone number must start with +92 and contain 13 digits in total.";
    }
    if (!in_array($gender, ['Male', 'Female', 'Other'])) {
        $errors[] = "Gender must be Male, Female, or Other.";
    }
    if (strlen($password) < 6 || strlen($password) > 50) {
        $errors[] = "Password must be between 6 and 50 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!preg_match('/^[a-zA-Z0-9 ,]{10,50}$/', $address)) {
        $errors[] = "Address must be 10-50 characters and contain only letters, numbers, spaces, and commas.";
    }

    // Handle avatar upload
    if (isset($_FILES['avatar_image']) && $_FILES['avatar_image']['error'] == 0) {
        $avatar = $_FILES['avatar_image']['name'];
        $target_dir = "users/images/";
        $target_file = $target_dir . basename($avatar);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!getimagesize($_FILES['avatar_image']['tmp_name'])) {
            $errors[] = "The uploaded file is not a valid image.";
        } elseif (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG, and WEBP files are allowed.";
        } elseif (!move_uploaded_file($_FILES['avatar_image']['tmp_name'], $target_file)) {
            $errors[] = "Error occurred while uploading the avatar.";
        }
    } else {
        $avatar = "default_avatar.webp";
    }

    $device_ip = $_SERVER['REMOTE_ADDR'];
    $device_version = $_SERVER['HTTP_USER_AGENT'];

    // If no errors, proceed to insert into database
    if (empty($errors)) {
        $user_id = generate_user_id();
        $encrypted_username = encrypt_data($username);
        $encrypted_email = encrypt_data($email);
        $encrypted_number = encrypt_data($number);
        $encrypted_address = encrypt_data($address);
        $encrypted_password = encrypt_data($password);
$created_at = date('Y-m-d H:i:s'); // Get the current timestamp in 'YYYY-MM-DD HH:MM:SS' format
$stmt = $conn->prepare("INSERT INTO login (user_id, username, email, number, gender, password, address, avatar_image, device_ip, device_version, block, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Bind parameters
$stmt->bind_param('ssssssssssss', $user_id, $encrypted_username, $encrypted_email, $encrypted_number, $gender, $encrypted_password, $encrypted_address, $avatar, $device_ip, $device_version, $block, $created_at);
        if ($stmt->execute()) {
            // Redirect after successful form submission to avoid resubmission on reload
            header("Location: success_page.php");
            exit();
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        // Return errors to be shown in JavaScript
        echo "<script>";
        echo "var errors = " . json_encode($errors) . ";";
        echo "window.location.href = '#error-popup';"; // Automatically scroll to the popup
        echo "</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://www.trivenzo.online/" />
  <meta name="msapplication-TileColor" content="#fede4d" />
  <meta name="msapplication-config" content="none" />

  <!-- Icons -->
  <link rel="icon" href="https://trivenzo.online/lexo/frontend/home/assets/images/logo/favicon-16x16.webp" sizes="16x16" />
  <link rel="icon" href="https://trivenzo.online/lexo/frontend/home/assets/images/logo/favicon-32x32.webp" sizes="32x32" />
  <link rel="apple-touch-icon" href="https://trivenzo.online/lexo/frontend/home/assets/images/logo/favicon-192x192.webp" sizes="192x192" />

  <!-- Meta Description -->
  <meta name="description" content="Become a member of Lexo ᴾᴷ! Sign up to explore an exquisite range of meals tailored just for you. Enjoy convenient food ordering, exclusive discounts, and a seamless dining experience at your fingertips. Your culinary adventure begins here!" />
  <meta name="keywords" content="Lexo, Sign Up, food delivery, join Lexo, gourmet meals, exclusive offers, convenient ordering, personalized dining, food experience, culinary adventure" />
  <meta property="og:title" content="Sign Up for Lexo ᴾᴷ | Unlock Delicious Deals!" />
  <meta property="og:description" content="Join Lexo ᴾᴷ today and enjoy exclusive access to gourmet meals, special offers, and a personalized dining experience that brings deliciousness to your doorstep." />
  <meta property="og:url" content="https://trivenzo.online/signup" />

  <title>Sign Up for Lexo ᴾᴷ | Join the Delicious Food Experience!</title>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../assets/css/signup.css" />
  <link rel="stylesheet" href="../assets/css/signup-2.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
</head>

<body>
  <div class="containers">

<!-- Popup -->
<div class="popup" id="error-popup" style="display:none;">
    <div class="icon" id="close-popup"><i class="fas fa-times"></i></div>
    <span id="error-message"></span> <!-- This is where the errors will be shown -->
</div>




 <!-- Popup -->

    <div class="contents">
      <div class="parents">
        <!-- Left Section -->
        <div class="left-sides">
          <div class="father">
            <h3>Start for free</h3>
            <div class="link">
              <span>Already have an account?</span>
              <a href="../public/login.html">Login</a>
            </div>
          </div>
          <div class="sub">
            <h1>Create Your Lexo Account</h1>
            <p>Sign up today and unlock special offers on delicious meals delivered straight to your door!</p>
            <div class="card">
              <p>I hope you won't have any issues signing up.</p>
              <div class="profile">
                <img src="../assets/images/profile.webp" alt="Profile" />
                <div class="cat">
                  <h3>Rizwan Ali</h3>
                  <span>MERN Full Stack Web Developer</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      
      <!-- Center Section -->
<div class="center-content">
  <form action="signup.php" method="POST" enctype="multipart/form-data">
           <div class="email">
                    <span>Enter your Username</span>
                    <input name="username" type="text" class="input" id="username" oninput="validateUsername()" required>
            </div>
     <div class="email">
    <span>Enter your Email</span>
    <input name="email" type="text" class="input" id="email" oninput="validateEmail()" required>
</div>
<div class="email">
    <span>Enter your number</span>
    <input name="number" type="text" value="+92" class="input" id="phone" oninput="validatePhone()" maxlength="13" required>
</div>

<div class="email">
    <div class="first">
        <span>Enter your Gender</span>
        <div class="gender-input-container">
<input type="text" class="input" id="gender" readonly onclick="toggleOptions()" name="gender" placeholder="Select Gender" required>
            <div class="options" id="gender-options" style="display: none;">
                <div class="option" onclick="selectGender('Male')">Male</div>
                <div class="option" onclick="selectGender('Female')">Female</div>
                <div class="option" onclick="selectGender('Other')">Other</div>
            </div>
        </div>
    </div>
</div>
<div class="email">
  <span>Enter your Password</span>
    <div class="input-container">
  <input name="password" type="password" class="input" id="password" oninput="validatePassword()" required>
  <!--hide password-->
  <img src="../assets/images/hide.webp" class="toggle-password" data-password="password">
  <!--show password-->
  <img src="../assets/images/show.webp" class="show-password" data-password="password" style="display: none;">
</div>
  </div>
<div class="email">
  <span>Confirm your Password</span>
  <div class="input-container">
    <input type="password" class="input" id="confirm-password" name="confirm-password" oninput="validateConfirmPassword()" required>
    <!--hide password-->
    <img src="../assets/images/hide.webp" class="toggle-password" data-password="confirm-password">
    <!--show password-->
    <img src="../assets/images/show.webp" class="show-password" data-password="confirm-password" style="display: none;">
  </div>
</div>
   
           <div class="email">
    <span>Enter your Address</span>
    <input name="address" type="text" class="input" id="address" oninput="validateAddress()" required>
</div>


<div class="email">
  <span>Upload your Avatar</span>
  <div class="input-wrapper">
    <input name="avatar_image" type="file" accept=".png, .jpg, .jpeg, .webp" class="input" id="avatar" style="display: none;" onchange="imageSelected()" required>
    <input type="text" id="imagePath" class="input" readonly placeholder="No image selected" required>
    <img src="../assets/images/upload.webp" alt="Upload Avatar" class="upload-icon" id="aftershow" onclick="document.getElementById('avatar').click();">
  </div>
</div>

            <button class="create" type="submit" id="submit" name="register" oninput="validateForm">Create Account</button>
          </form>
</div>

      </div>
    </div>
  </div>

 
<script>
   // Function to display the error popup one by one
function displayErrorPopup(errors) {
    const popup = document.getElementById('error-popup');
    const errorMessage = document.getElementById('error-message');

    let index = 0; // Start with the first error
    const delayBetweenErrors = 2000; // 2 seconds delay between each error

    // Add a default error message to the beginning of the errors array
    const defaultError = "Please fill all the fields correctly";
    errors.unshift(defaultError); // Adds the default error as the first item in the errors array

    // Function to show errors one by one
    function showNextError() {
        if (index < errors.length) {
            errorMessage.innerHTML = errors[index]; // Display the current error
            popup.style.display = 'block'; // Show the popup

            // After the error is shown, increase the index and call showNextError after a delay
            index++;
            setTimeout(showNextError, delayBetweenErrors); // Call itself recursively
        } else {
            // Close the popup after all errors have been shown
            setTimeout(function() {
                popup.style.display = 'none';
            }, 1000); // Close the popup after 1 second
        }
    }

    // Start showing the errors one by one
    showNextError();
}

// Function to validate the form on submit
function validateForm(event) {
    const errors = [];
    let formValid = true;

    // Validate all fields
    const email = document.getElementById('email').value;
    const name = document.getElementById('username').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const address = document.getElementById('address').value;

    // If any field is empty, add the default error message
    if (!name || !email || !phone || !password || !confirmPassword || !address) {
        formValid = false;
        errors.push("Please fill all the fields correctly");
    }

    // Validate specific fields like email
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (email && !emailPattern.test(email)) {
        formValid = false;
        errors.push("Please enter a valid email address");
    }

    // Validate phone number
    const phonePattern = /^\+92[0-9]{10}$/;
    if (phone && !phonePattern.test(phone)) {
        formValid = false;
        errors.push("Phone number must be in the format +923XXXXXXXXX (total 13 characters)");
    }

    // Validate password
    if (password && password.length < 6) {
        formValid = false;
        errors.push("Password must be at least 6 characters long");
    }

    // Check if passwords match
    if (password !== confirmPassword) {
        formValid = false;
        errors.push("Passwords do not match");
    }

    // Validate address
    if (address && address.length < 10) {
        formValid = false;
        errors.push("Address must be at least 10 characters long");
    }

    // Check if there are any errors to show
    if (errors.length > 0) {
        event.preventDefault(); // Prevent form submission
        displayErrorPopup(errors); // Show the errors in the popup
    }

    return formValid;
}

// Add event listener to form submit
const form = document.getElementById('myForm');
if (form) {
    form.addEventListener('submit', validateForm);
}

// Function to validate username
function validateUsername() {
    const usernameInput = document.getElementById("username");
    const popup = document.querySelector(".popup");
    const errorPopupText = document.getElementById("error-message");
    const regex = /^[a-zA-Z0-9]{6,}$/;

    if (usernameInput.value === "") {
        errorPopupText.textContent = "Username cannot be empty";
        popup.style.display = "flex";
        return false;
    } else if (!regex.test(usernameInput.value)) {
        errorPopupText.textContent = "Username must be at least 6 characters long and can only contain letters and numbers (a-z, 0-9)";
        popup.style.display = "flex";
        return false;
    } else {
        popup.style.display = "none"; // Hide the popup if valid
        return true;
    }
}

// Function to validate email
function validateEmail() {
    const emailInput = document.getElementById("email");
    const popup = document.querySelector(".popup");
    const errorPopupText = document.getElementById("error-message");
    const regex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;

    if (emailInput.value === "") {
        errorPopupText.textContent = "Email cannot be empty";
        popup.style.display = "flex";
        return false;
    } else if (!regex.test(emailInput.value)) {
        errorPopupText.textContent = "Only @gmail.com addresses are allowed";
        popup.style.display = "flex";
        return false;
    } else {
        popup.style.display = "none"; // Hide the popup if valid
        return true;
    }
}

// Function to validate phone number
function validatePhone() {
    const phoneInput = document.getElementById("phone");
    const popup = document.querySelector(".popup");
    const errorPopupText = document.getElementById("error-message");
    const regex = /^\+92[0-9]{10}$/;

    if (phoneInput.value === "") {
        errorPopupText.textContent = "Phone number cannot be empty";
        popup.style.display = "flex";
        return false;
    } else if (!regex.test(phoneInput.value)) {
        errorPopupText.textContent = "Phone number must be in the format +923XXXXXXXXX (total 13 characters)";
        popup.style.display = "flex";
        return false;
    } else {
        popup.style.display = "none"; // Hide the popup if valid
        return true;
    }
}

// Function to validate password
function validatePassword() {
    const passwordInput = document.getElementById("password");
    const popup = document.querySelector(".popup");
    const errorPopupText = document.getElementById("error-message");

    if (passwordInput.value === "") {
        errorPopupText.textContent = "Password cannot be empty";
        popup.style.display = "flex";
        return false;
    } else if (passwordInput.value.length < 6) {
        errorPopupText.textContent = "Password must be at least 6 characters long";
        popup.style.display = "flex";
        return false;
    } else {
        popup.style.display = "none"; // Hide the popup if valid
        return true;
    }
}

// Function to validate confirm password
function validateConfirmPassword() {
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm-password");
    const popup = document.querySelector(".popup");
    const errorPopupText = document.getElementById("error-message");

    if (confirmPasswordInput.value === "") {
        errorPopupText.textContent = "Confirm Password cannot be empty";
        popup.style.display = "flex";
        return false;
    } else if (confirmPasswordInput.value !== passwordInput.value) {
        errorPopupText.textContent = "Passwords do not match";
        popup.style.display = "flex";
        return false;
    } else {
        popup.style.display = "none"; // Hide the popup if valid
        return true;
    }
}

// Function to validate address
function validateAddress() {
    const addressInput = document.getElementById("address");
    const popup = document.querySelector(".popup");
    const errorPopupText = document.getElementById("error-message");

    if (addressInput.value === "") {
        errorPopupText.textContent = "Address cannot be empty";
        popup.style.display = "flex";
        return false;
    } else if (addressInput.value.length < 10) {
        errorPopupText.textContent = "Address must be at least 10 characters long";
        popup.style.display = "flex";
        return false;
    } else {
        popup.style.display = "none"; // Hide the popup if valid
        return true;
    }
}

// Function to validate all fields before submission
function validateForm() {
    const isValidUsername = validateUsername();
    const isValidEmail = validateEmail();
    const isValidPhone = validatePhone();
    const isValidPassword = validatePassword() && validateConfirmPassword();
    const isValidAddress = validateAddress();

    // Check if all fields are valid
    if (isValidUsername && isValidEmail && isValidPhone && isValidPassword && isValidAddress) {
        return true; // Allow form submission
    } else {
        return false; // Prevent form submission
    }
}

// Password visibility toggle
document.querySelectorAll('.toggle-password').forEach(function(element) {
    element.addEventListener('click', function() {
        const passwordField = document.getElementById(element.dataset.password);
        const isPasswordVisible = passwordField.type === 'password';
        passwordField.type = isPasswordVisible ? 'text' : 'password';

        // Toggle the visibility of the password toggle buttons
        element.style.display = isPasswordVisible ? 'none' : 'block';
        element.nextElementSibling.style.display = isPasswordVisible ? 'block' : 'none';
    });
});

// Close popup manually if needed
document.getElementById('close-popup').addEventListener('click', function() {
    document.getElementById('error-popup').style.display = 'none';
});

</script>

</body>
 <script src="/lexo/frontend/login/assets/js/main.js"></script>
</html>
