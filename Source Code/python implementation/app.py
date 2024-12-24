from flask import Flask, request, redirect, render_template, session, flash
import mysql.connector
import os
import base64
import random
from cryptography.fernet import Fernet
from datetime import datetime

app = Flask(__name__)
app.secret_key = 'your_secret_key'  # Change this to a random secret key (fJ8Y9x!rDqL3vX2@kM5c*B#Z1t7A)

# Database connection
def get_db_connection():
    connection = mysql.connector.connect(
        host='localhost',
        user='trivenzo_rizwan',
        password='SJS8NdNZ^aa$',
        database='trivenzo_all_in_one_data'
    )
    return connection

# Encryption settings
def encrypt_data(data):
    key = Fernet.generate_key()  # Generate a key
    cipher = Fernet(key)
    encrypted = cipher.encrypt(data.encode())
    return base64.urlsafe_b64encode(encrypted).decode()  # Encode for storage

def validate_input(data):
    return data.strip()  # Basic sanitation

def generate_user_id():
    return ''.join(random.choices('0123456789', k=10))

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        errors = []
        username = validate_input(request.form['username'])
        email = validate_input(request.form['email'])
        number = validate_input(request.form['number'])
        gender = validate_input(request.form['gender'])
        password = validate_input(request.form['password'])
        confirm_password = validate_input(request.form['confirm-password'])
        address = validate_input(request.form['address'])

        # Database connection
        conn = get_db_connection()
        cursor = conn.cursor()

        # Check for existing username
        cursor.execute("SELECT * FROM login WHERE username = %s", (username,))
        if cursor.fetchone():
            errors.append("Username already exists.")
        
        # Check for existing email
        cursor.execute("SELECT * FROM login WHERE email = %s", (email,))
        if cursor.fetchone():
            errors.append("Email already exists.")
        
        # Check for existing phone number
        cursor.execute("SELECT * FROM login WHERE number = %s", (number,))
        if cursor.fetchone():
            errors.append("Phone number already exists.")

        # Validation
        if not (3 <= len(username) <= 15) or not username.isalnum():
            errors.append("Username must be 3-15 characters long and contain only letters and numbers.")
        if not (email.endswith('@gmail.com')):
            errors.append("Email must be a valid @gmail.com address.")
        if not (number.startswith('+92') and len(number) == 13):
            errors.append("Phone number must start with +92 and contain 13 digits in total.")
        if gender not in ['Male', 'Female', 'Other']:
            errors.append("Gender must be Male, Female, or Other.")
        if len(password) < 6 or len(password) > 50:
            errors.append("Password must be between 6 and 50 characters.")
        if password != confirm_password:
            errors.append("Passwords do not match.")
        if not (10 <= len(address) <= 50):
            errors.append("Address must be 10-50 characters long.")

        # Handle avatar upload
        avatar = "default_avatar.webp"
        if 'avatar_image' in request.files:
            file = request.files['avatar_image']
            if file and allowed_file(file.filename):
                avatar = secure_filename(file.filename)
                file.save(os.path.join('users/images/', avatar))
            else:
                errors.append("Only JPG, JPEG, PNG, and WEBP files are allowed.")

        # If no errors, proceed to insert into database
        if not errors:
            user_id = generate_user_id()
            encrypted_username = encrypt_data(username)
            encrypted_email = encrypt_data(email)
            encrypted_number = encrypt_data(number)
            encrypted_address = encrypt_data(address)
            encrypted_password = encrypt_data(password)
            created_at = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

            cursor.execute("""
                INSERT INTO login (user_id, username, email, number, gender, password, address, avatar_image, created_at) 
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)""",
                (user_id, encrypted_username, encrypted_email, encrypted_number, gender, encrypted_password, encrypted_address, avatar, created_at))
            conn.commit()
            cursor.close()
            conn.close()
            return redirect('/success_page')

        else:
            for error in errors:
                flash(error)

    return render_template('index.html')

def allowed_file(filename):
    allowed_extensions = {'jpg', 'jpeg', 'png', 'webp'}
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in allowed_extensions

if __name__ == '__main__':
    app.run(debug=True)