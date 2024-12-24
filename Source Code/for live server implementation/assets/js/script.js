// Popup error display function
function showPopupError(errorMessage) {
    const errorPopup = document.getElementById("errors-popup");
    errorPopup.textContent = errorMessage; // Update popup message
    document.querySelector(".popup").style.display = "block"; // Show the popup
}

// Close popup function
function closePopup() {
    document.querySelector(".popup").style.display = "none"; // Hide the popup
}

// Close popup when clicking on the close icon
document.getElementById("close-popup").addEventListener("click", closePopup);

// Toggle Password Visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById("password");
    const togglePassword = document.getElementById("togglePassword");

    if (passwordInput.type === "password") {
        passwordInput.type = "text"; // Show password
        togglePassword.src = "/lexo/frontend/login/assets/images/show.webp"; // Change to show image
    } else {
        passwordInput.type = "password"; // Hide password
        togglePassword.src = "/lexo/frontend/login/assets/images/hide.webp"; // Change back to hide image
    }
}

// Toggle Gender Dropdown
function toggleOptions() {
    const options = document.getElementById("gender-options");
    options.style.display = options.style.display === "none" ? "block" : "none";
}

// Select Gender
function selectGender(gender) {
    document.getElementById("gender").value = gender; // Set the selected gender
    document.getElementById("gender-options").style.display = "none"; // Hide the options
    validateGender(); // Validate the gender selection
}

// Close the gender dropdown if clicking outside
window.onclick = function(event) {
    const options = document.getElementById("gender-options");
    if (!event.target.matches('.input') && !event.target.closest('.gender-input-container')) {
        options.style.display = "none";
    }
};

// Validate Gender
function validateGender() {
    const gender = document.getElementById("gender").value;
    if (!gender) {
        showPopupError("Please select a gender.");
        return false;
    }
    closePopup(); // Close popup if no error
    return true;
}

// Validate Username
function validateUsername() {
    const username = document.getElementById("username").value;
    if (!/^[a-z0-9]{6,50}$/.test(username)) {
        showPopupError("Username must be 6-50 characters long and contain only lowercase letters or digits.");
        return false;
    }
    closePopup();
    return true;
}

// Validate Email (only Gmail allowed)
function validateEmail() {
    const email = document.getElementById("email").value;
    if (!/^[\w-]+@gmail\.com$/.test(email)) {
        showPopupError("Only Gmail addresses (ending with @gmail.com) are acceptable.");
        return false;
    }
    closePopup();
    return true;
}

// Validate Phone Number (+92 followed by 10 digits)
function validateNumber() {
    const number = document.getElementById("number").value;
    if (!/^\+92[0-9]{10}$/.test(number)) {
        showPopupError("Number must start with +92 and be followed by 10 digits (0-9).");
        return false;
    }
    closePopup();
    return true;
}

// Validate Password (6-40 characters)
function validatePassword() {
    const password = document.getElementById("password").value;
    if (password.length < 6 || password.length > 40) {
        showPopupError("Password must be 6-40 characters long.");
        return false;
    }
    closePopup();
    return true;
}

// Validate Confirm Password
function validateConfirmPassword() {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm-password").value;
    if (confirmPassword !== password) {
        showPopupError("Passwords must match.");
        return false;
    }
    closePopup();
    return true;
}

// Validate Address (10-50 characters, letters/numbers/commas/periods)
function validateAddress() {
    const address = document.getElementById("address").value;
    const addressRegex = /^[A-Za-z0-9\s,\.]{10,50}$/;

    if (address.length < 10) {
        showPopupError("Address must be at least 10 characters long.");
        return false;
    } else if (address.length > 50) {
        showPopupError("Address must be no more than 50 characters long.");
        return false;
    } else if (!addressRegex.test(address)) {
        showPopupError("Address can only contain letters, numbers, spaces, commas, and periods.");
        return false;
    }
    closePopup();
    return true;
}

// Validate Avatar (check if file is uploaded)
function validateAvatar() {
    const avatar = document.getElementById("avatar");
    if (!avatar.files.length) {
        showPopupError("Avatar is required.");
        return false;
    }
    closePopup();
    return true;
}

// Validate all fields
function validateAllFields() {
    const validations = [
        validateUsername,
        validateEmail,
        validateNumber,
        validateGender,
        validatePassword,
        validateConfirmPassword,
        validateAddress,
        validateAvatar
    ];

    let allValid = true;

    validations.forEach(validate => {
        if (!validate()) {
            allValid = false;
        }
    });

    return allValid;
}

// Create account after validation
function createAccount() {
    const allFieldsValid = validateAllFields(); // Validate all fields
    
    if (allFieldsValid) {
        alert("Account created successfully!"); // Replace with actual account creation logic
    } else {
        showPopupError("Please correct the errors in the form before proceeding.");
    }
}
