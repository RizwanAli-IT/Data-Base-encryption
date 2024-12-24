function imageSelected() {
    const fileInput = document.getElementById('avatar');
    const imagePathInput = document.getElementById('imagePath');
    const errorMessage = document.getElementById('errorMessage');

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const validTypes = ['image/png', 'image/jpeg', 'image/webp'];
        
        // Check if the selected file type is valid
        if (validTypes.includes(file.type)) {
            imagePathInput.value = file.name; // Set the file name in the input box
            errorMessage.textContent = ''; // Clear any previous error messages
        } else {
            imagePathInput.value = ''; // Clear the input box
            errorMessage.textContent = 'Please select a valid image (PNG, JPG, or WEBP).'; // Show error
            fileInput.value = ''; // Clear the file input
        }
    } else {
        imagePathInput.value = "No image selected"; // Reset if no file selected
        errorMessage.textContent = ''; // Clear error message
    }
}