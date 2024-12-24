
const cheaking = document.getElementById('aftershow');
addEventListener('click', () => {
    setTimeout(() => {    
    cheaking.style.filter = 'invert(30%)'; 
    },1000)
})


  function imageSelected() {
    var fileInput = document.getElementById('avatar');
    var imagePath = document.getElementById('imagePath');
    
    // Check if a file has been selected
    if (fileInput.files && fileInput.files[0]) {
      imagePath.value = fileInput.files[0].name; // Set the file name in the text input
    } else {
      imagePath.value = 'No image selected'; // Reset if no file is selected
    }
  }
  
            // Function to toggle password visibility
function togglePasswordVisibility(passwordFieldId, hideIcon, showIcon) {
  const passwordInput = document.getElementById(passwordFieldId);

  if (passwordInput.type === "password") {
    passwordInput.type = "text"; // Show password
    hideIcon.style.display = 'none'; // Hide the 'hide' icon
    showIcon.style.display = 'inline'; // Show the 'show' icon
  } else {
    passwordInput.type = "password"; // Hide password
    hideIcon.style.display = 'inline'; // Show the 'hide' icon
    showIcon.style.display = 'none'; // Hide the 'show' icon
  }
}

// Get all hide and show icons
const hideIcons = document.querySelectorAll('.toggle-password');
const showIcons = document.querySelectorAll('.show-password');

// Add event listeners for hide icons
hideIcons.forEach(hideIcon => {
  hideIcon.addEventListener('click', function () {
    const passwordFieldId = hideIcon.getAttribute('data-password');
    const showIcon = document.querySelector(`.show-password[data-password="${passwordFieldId}"]`);
    togglePasswordVisibility(passwordFieldId, hideIcon, showIcon);
  });
});

// Add event listeners for show icons
showIcons.forEach(showIcon => {
  showIcon.addEventListener('click', function () {
    const passwordFieldId = showIcon.getAttribute('data-password');
    const hideIcon = document.querySelector(`.toggle-password[data-password="${passwordFieldId}"]`);
    togglePasswordVisibility(passwordFieldId, hideIcon, showIcon);
  });
});

function toggleOptions() {
    const options = document.getElementById('gender-options');
    options.style.display = options.style.display === 'none' ? 'block' : 'none';
}

function selectGender(gender) {
    document.getElementById('gender').value = gender; // Gender ko input mein daal rahe hain
    document.getElementById('gender-options').style.display = 'none'; // Options ko hide karna
}
