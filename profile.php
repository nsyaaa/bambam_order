<?php
// ===============================
// Bambam Burger - User Profile
// ===============================
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Prevent caching to ensure logout redirect works on back button
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include 'db.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $userId = $_SESSION['user_id'];

    // Handle File Upload
    $profilePicPath = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowed)) {
            $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destPath)) {
                $profilePicPath = $destPath;
            }
        }
    }

    try {
        $sql = "UPDATE users SET name = ?, gmail = ?";
        $params = [$name, $email];

        if (!empty($pass)) {
            $sql .= ", password = ?";
            $params[] = password_hash($pass, PASSWORD_DEFAULT);
        }

        if ($profilePicPath) {
            $sql .= ", profile_pic = ?";
            $params[] = $profilePicPath;
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $_SESSION['user_name'] = $name; // Update session
        echo "<script>
            localStorage.setItem('current_user_name', '" . addslashes($name) . "');
            alert('Profile updated successfully!');
            window.location.href = 'profile.php';
        </script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error updating profile: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Determine Profile Picture
$defaultPic = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2NjYyI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00czLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPjwvc3ZnPg==";
$profilePic = (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) 
    ? $user['profile_pic'] 
    : $defaultPic;

include 'header.php';
?>

<style>
    /* High-End Tech-Forward Aesthetic - Light Mode */
    /* High-End Tech-Forward Aesthetic */
    body {
        background: url('images/ch.png') no-repeat center center fixed !important;
        background-size: cover !important;
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
    }
    .profile-container {
        max-width: 500px;
        margin: 100px auto 40px; /* Top margin to clear fixed header */
        background: rgba(0, 0, 0, 0.85); /* Dark background to separate from wall */
        padding: 30px;
        border: 3px solid #ff5100; /* Bold Orange Border */
        border-radius: 25px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.6);
    }
    
    /* Header Bar inside Profile Card */
    .profile-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        margin-bottom: 30px;
        color: #ffffff;
    }
    .back-btn, .settings-btn {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 20px;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        opacity: 0.7;
        transition: opacity 0.3s;
    }
    .back-btn:hover, .settings-btn:hover { opacity: 1; }

    .profile-title {
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        margin: 0;
        letter-spacing: 2px;
        color: #cccccc; /* Muted Grey */
    }

    /* Profile Info */
    .profile-info {
        text-align: center;
        padding: 0 0 40px;
    }
    .profile-pic-wrapper {
        position: relative;
        width: 100px; /* Reduced size */
        height: 100px; /* Reduced size */
        margin: 0 auto 20px;
    }
    .profile-pic {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #333; /* Darker border for contrast */
        background: #111;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    }
    .upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #ff5100; /* Brand Orange */
        color: white;
        border-radius: 50%;
        width: 30px; /* Adjusted for smaller pic */
        height: 30px; /* Adjusted for smaller pic */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        font-size: 14px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        transition: transform 0.2s;
    }
    .upload-btn:hover { transform: scale(1.1); }

    .user-name { 
        font-size: 28px; 
        color: #ffffff; 
        margin: 0 0 5px 0; 
        font-weight: 700; 
        letter-spacing: -0.5px;
    }
    .user-username { 
        font-size: 14px; 
        color: #cccccc; /* Muted Grey */
        margin: 0 0 25px 0; 
        font-weight: 400;
        letter-spacing: 0.5px;
    }

    .edit-btn {
        background: transparent; 
        border: 1px solid rgba(255,255,255,0.3); 
        color: #ffffff;
        padding: 12px 35px; 
        border-radius: 50px; /* Pill shape */
        font-weight: 600;
        font-size: 13px;
        letter-spacing: 1px;
        text-transform: uppercase;
        cursor: pointer; 
        transition: all 0.3s ease;
    }
    .edit-btn:hover { 
        background: #ff5100; 
        border-color: #ff5100; 
        color: white;
        box-shadow: 0 5px 15px rgba(193, 140, 93, 0.2);
        transform: translateY(-2px);
    }

    /* Menu List */
    .profile-menu { display: flex; flex-direction: column; gap: 15px; }
    .menu-item {
        display: flex; align-items: center; padding: 20px 25px;
        background: #ffffff;
        border: 1px solid #f0f0f0;
        border-radius: 16px;
        color: #333333; 
        text-decoration: none;
        transition: all 0.3s ease; 
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); /* Light shadow */
    }
    .menu-item:hover { 
        background: #1a1a1a;
        color: #ffffff;
        transform: translateY(-3px);
        border-color: #ff5100;
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }
    .menu-item:hover .menu-icon { color: #ff5100; }
    .menu-item:hover .menu-arrow { color: #ffffff; }
    .menu-icon { width: 30px; font-size: 18px; text-align: center; margin-right: 20px; color: #ff5100; }
    .menu-text { flex: 1; font-size: 15px; font-weight: 500; letter-spacing: 0.5px; }
    .menu-arrow { color: #ccc; font-size: 12px; }
    
    /* Language List */
    .lang-list, .branch-list { 
        display: none; 
        background: #ffffff; 
        margin-top: -10px; 
        margin-bottom: 10px; 
        border-radius: 0 0 16px 16px; 
        border: 1px solid #f0f0f0; 
        border-top: none;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .lang-option, .branch-option { 
        padding: 15px 25px; 
        padding-left: 75px; /* Align with text */
        font-size: 14px; 
        color: #666; 
        cursor: pointer; 
        border-bottom: 1px solid #f9f9f9; 
    }
    .lang-option:last-child, .branch-option:last-child { border-bottom: none; }
    .lang-option:hover, .branch-option:hover { background: #f9f9f9; color: #ff5100; }

    /* Logout Button */
    .logout-btn {
        display: block; width: 100%; padding: 18px; 
        background: transparent; 
        color: #e74c3c; /* Muted Red */
        text-align: center; 
        border: 1px solid rgba(231, 76, 60, 0.3); 
        border-radius: 50px; 
        font-weight: 600;
        margin-top: 40px; 
        cursor: pointer; 
        transition: 0.3s;
        font-size: 14px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .logout-btn:hover { background: rgba(231, 76, 60, 0.05); border-color: #e74c3c; }

    /* Edit Profile Modal */
    .modal {
        display: none; 
        position: fixed; 
        z-index: 2000; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background-color: #ffffff;
        color: #333;
        padding: 30px;
        border-radius: 15px;
        width: 90%;
        max-width: 400px;
        position: relative;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        border: none;
        max-height: 90vh;
        overflow-y: auto;
    }
    .close { position: absolute; top: 15px; right: 20px; font-size: 28px; color: #aaa; cursor: pointer; }
    .close:hover { color: #333; }
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #666; font-size: 14px; }
    .form-group input { 
        width: 100%; padding: 12px; 
        background: #f9f9f9; border: 1px solid #ddd; 
        border-radius: 8px; color: #333; outline: none; 
    }
    .form-group input:focus { border-color: #ff5100; background: #fff; }
    .save-btn { 
        width: 100%; padding: 15px; 
        background: #ff5100; color: white; 
        border: none; border-radius: 50px; 
        font-size: 14px; font-weight: bold; 
        cursor: pointer; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px;
    }
    .save-btn:hover { background: #e04600; }
</style>

<div class="profile-container animate-fade-up">
    <!-- Top Bar -->
    <div class="profile-header-bar">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <h3 class="profile-title"><?php echo $t['my_profile']; ?></h3>
        <button class="settings-btn"><i class="fas fa-cog"></i></button>
    </div>

    <!-- Profile Picture & Name -->
    <div class="profile-info">
        <div class="profile-pic-wrapper">
            <!-- Placeholder image using a data URI for immediate display -->
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" class="profile-pic" id="profileImage">
            <label for="uploadPic" class="upload-btn"><i class="fas fa-camera"></i></label>
            <input type="file" id="uploadPic" style="display:none;" accept="image/*" onchange="document.getElementById('profileImage').src = window.URL.createObjectURL(this.files[0])">
        </div>
        <h2 class="user-name" id="display-name"><?php echo htmlspecialchars($user['name']); ?></h2>
        <p class="user-username"><?php echo htmlspecialchars($user['phone']); ?></p>
        <button class="edit-btn" onclick="openModal()"><?php echo $t['edit_profile']; ?></button>
    </div>

    <!-- Menu Items -->
    <div class="profile-menu">
        <a href="favorites.php" class="menu-item">
            <i class="far fa-heart menu-icon"></i>
            <span class="menu-text"><?php echo $t['favourites']; ?></span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>
        
        <div class="menu-item" onclick="toggleLangList()">
            <i class="fas fa-globe menu-icon"></i>
            <span class="menu-text" id="currentLangDisplay"><?php echo $t['language']; ?></span>
            <i class="fas fa-chevron-down menu-arrow"></i>
        </div>
        <div id="langList" class="lang-list">
            <div class="lang-option" onclick="selectLanguage('ms')">Malay (Bahasa Melayu)</div>
            <div class="lang-option" onclick="selectLanguage('en')">English</div>
            <div class="lang-option" onclick="selectLanguage('zh')">Chinese (中文)</div>
            <div class="lang-option" onclick="selectLanguage('th')">Siamese (ภาษาไทย)</div>
            <div class="lang-option" onclick="selectLanguage('ta')">Tamil (தமிழ்)</div>
        </div>

        <div class="menu-item" onclick="toggleBranchList()">
            <i class="fas fa-map-marker-alt menu-icon"></i>
            <span class="menu-text"><?php echo $t['location']; ?></span>
            <i class="fas fa-chevron-down menu-arrow"></i>
        </div>
        <div id="branchList" class="branch-list">
            <div class="branch-option" onclick="openMap('Kangar')">BamBam Burger Kangar</div>
            <div class="branch-option" onclick="openMap('Jejawi')">BamBam Burger Jejawi</div>
            <div class="branch-option" onclick="openMap('Arau')">BamBam Burger Arau</div>
            <div class="branch-option" onclick="openMap('Kuala Perlis')">BamBam Burger Kuala Perlis</div>
            <div class="branch-option" onclick="openMap('Beseri')">BamBam Burger Beseri</div>
        </div>

        <a href="address.php" class="menu-item">
            <i class="fas fa-map-pin menu-icon"></i>
            <span class="menu-text"><?php echo $t['delivery_address'] ?? 'Delivery Address'; ?></span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>

        <a href="history.php" class="menu-item">
            <i class="fas fa-history menu-icon"></i>
            <span class="menu-text"><?php echo $t['history']; ?></span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>

        <a href="payment_methods.php" class="menu-item">
            <i class="fas fa-credit-card menu-icon"></i>
            <span class="menu-text"><?php echo $t['payment_method'] ?? 'Payment Method'; ?></span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>
    </div>
    
    <div style="padding: 20px;"><button class="logout-btn" onclick="handleLogout()">Log Out</button></div>
</div>

<!-- Edit Profile Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 style="color:#ff5100; margin-top:0; font-size:20px;"><?php echo $t['edit_profile']; ?></h2>
        <form id="profileForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
                <label>Profile Picture</label>
                <input type="file" name="profile_pic" accept="image/*">
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="edit-name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly style="background:#f0f0f0; color:#777; border-color:#ddd;">
                <small style="color:#999;">Phone number cannot be changed.</small>
            </div>
            <div class="form-group">
                <label>Email Address (Optional)</label>
                <input type="email" name="email" id="edit-email" value="<?php echo htmlspecialchars($user['gmail']); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="edit-pass" placeholder="Leave blank to keep current">
            </div>
            <button type="button" class="save-btn" onclick="saveProfile()">Save Changes</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Name is now rendered by PHP, no need to overwrite from localStorage
});

function openModal() {
    document.getElementById('editModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

function saveProfile() {
    const newPass = document.getElementById('edit-pass').value;

    // Password Verification Logic
    if (newPass) {
        // Simulate OTP
        const otp = Math.floor(100000 + Math.random() * 900000);
        alert(`SECURITY CHECK:\nWe have sent a verification code to your phone.\n\nYour Code: ${otp}`);
        
        const enteredOtp = prompt("Please enter the verification code to confirm password change:");
        
        if (enteredOtp != otp) {
            alert("Incorrect verification code! Password not changed.");
            return;
        }
    }

    // Submit the form to PHP
    document.getElementById('profileForm').submit();
}

function toggleLangList() {
    const list = document.getElementById('langList');
    list.style.display = (list.style.display === 'block') ? 'none' : 'block';
}

function selectLanguage(langCode) {
    // Set cookie for 30 days
    document.cookie = "site_lang=" + langCode + "; path=/; max-age=" + (60*60*24*30);
    // Reload page to apply changes
    location.reload();
}

function toggleBranchList() {
    const list = document.getElementById('branchList');
    list.style.display = (list.style.display === 'block') ? 'none' : 'block';
}

function openMap(branch) {
    // Open Google Maps with the branch name + Perlis for accuracy
    const query = encodeURIComponent("BamBam Burger " + branch + ", Perlis");
    window.open(`https://www.google.com/maps?q=${query}`, '_blank');
}

function handleLogout() {
    if(confirm('Are you sure you want to logout?')) {
        // Redirect to login page with logout action to clear PHP session
        window.location.href = 'login.php?action=logout';
    }
}
</script>

<?php include 'footer.php'; ?>