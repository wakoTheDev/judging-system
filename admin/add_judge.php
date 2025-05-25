<?php
/**
 * Admin Panel - Add New Judge
 * LAMP Stack Judging System
 */
require_once '../config/database.php';
$page_title = 'Add New Judge - Admin Panel';
$base_url = '../';
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $bar_number = trim($_POST['bar_number'] ?? '');
    $license_state = trim($_POST['license_state'] ?? '');
    $years_experience = trim($_POST['years_experience'] ?? '');
    $specializations = $_POST['specializations'] ?? [];
    $court_assignments = $_POST['court_assignments'] ?? [];
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $emergency_phone = trim($_POST['emergency_phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    $errors = [];
    
    // Required field validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters';
    }
    
    if (empty($display_name)) {
        $errors[] = 'Display name is required';
    } elseif (strlen($display_name) < 2 || strlen($display_name) > 100) {
        $errors[] = 'Display name must be between 2 and 100 characters';
    }
    
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!preg_match('/^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/', $phone)) {
        $errors[] = 'Please enter a valid phone number';
    }
    
    if (empty($bar_number)) {
        $errors[] = 'Bar number is required';
    }
    
    if (empty($license_state)) {
        $errors[] = 'License state is required';
    }
    
    if (!empty($years_experience) && (!is_numeric($years_experience) || $years_experience < 0)) {
        $errors[] = 'Years of experience must be a valid number';
    }
    
    // Check for duplicate username and email
    if (empty($errors)) {
        $conn = getDatabaseConnection();
        
        // Check username
        $check_sql = "SELECT id FROM judges WHERE username = ?";
        $result = executeQuery($conn, $check_sql, [$username], 's');
        if ($result && $result->num_rows > 0) {
            $errors[] = 'Username already exists. Please choose a different username.';
        }
        
        // Check email
        $check_sql = "SELECT id FROM judges WHERE email = ?";
        $result = executeQuery($conn, $check_sql, [$email], 's');
        if ($result && $result->num_rows > 0) {
            $errors[] = 'Email already exists. Please use a different email address.';
        }
        
        closeDatabaseConnection($conn);
    }
    
    // Insert new judge if no errors
    if (empty($errors)) {
        $conn = getDatabaseConnection();
        
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Insert judge
            $insert_sql = "INSERT INTO judges (username, display_name, first_name, last_name, email, phone, 
                          address, city, state, zip_code, bar_number, license_state, years_experience, 
                          emergency_contact, emergency_phone, notes, is_active, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $result = executeQuery($conn, $insert_sql, [
                $username, $display_name, $first_name, $last_name, $email, $phone,
                $address, $city, $state, $zip_code, $bar_number, $license_state, 
                $years_experience ?: null, $emergency_contact, $emergency_phone, $notes, $is_active
            ], 'ssssssssssssisssi');
            
            if ($result) {
                $judge_id = $conn->insert_id;
                
                // Insert specializations
                if (!empty($specializations)) {
                    $spec_sql = "INSERT INTO judge_specializations (judge_id, specialization) VALUES (?, ?)";
                    foreach ($specializations as $specialization) {
                        executeQuery($conn, $spec_sql, [$judge_id, $specialization], 'is');
                    }
                }
                
                // Insert court assignments
                if (!empty($court_assignments)) {
                    $court_sql = "INSERT INTO judge_court_assignments (judge_id, court_name) VALUES (?, ?)";
                    foreach ($court_assignments as $court) {
                        executeQuery($conn, $court_sql, [$judge_id, $court], 'is');
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                $success_message = "Judge '{$display_name}' has been successfully added to the system!";
                
                // Clear form data
                $username = $display_name = $first_name = $last_name = $email = $phone = '';
                $address = $city = $state = $zip_code = $bar_number = $license_state = '';
                $years_experience = $emergency_contact = $emergency_phone = $notes = '';
                $specializations = $court_assignments = [];
                $is_active = 1;
                
            } else {
                throw new Exception("Failed to insert judge record");
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error adding judge: " . $e->getMessage();
        }
        
        closeDatabaseConnection($conn);
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Initialize form data if not set
$username = $username ?? '';
$display_name = $display_name ?? '';
$first_name = $first_name ?? '';
$last_name = $last_name ?? '';
$email = $email ?? '';
$phone = $phone ?? '';
$address = $address ?? '';
$city = $city ?? '';
$state = $state ?? '';
$zip_code = $zip_code ?? '';
$bar_number = $bar_number ?? '';
$license_state = $license_state ?? '';
$years_experience = $years_experience ?? '';
$specializations = $specializations ?? [];
$court_assignments = $court_assignments ?? [];
$emergency_contact = $emergency_contact ?? '';
$emergency_phone = $emergency_phone ?? '';
$notes = $notes ?? '';
$is_active = $is_active ?? 1;

// Specialization options
$specialization_options = [
    'Criminal Law',
    'Civil Law',
    'Family Law',
    'Traffic Court',
    'Small Claims',
    'Juvenile Court',
    'Probate Court',
    'Administrative Law',
    'Appeals Court'
];

// Court options
$court_options = [
    'District Court A',
    'District Court B',
    'Circuit Court',
    'Family Court',
    'Traffic Court',
    'Municipal Court',
    'Appeals Court'
];

// US States
$us_states = [
    'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
    'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
    'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
    'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
    'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-section h2 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        
        .form-section h2 i {
            color: #2563eb;
        }
        
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        
        .success-message {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="judges.php" class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                        Back to Judges
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Add New Judge</h1>
                <div class="w-32"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto p-6">
        <!-- Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Account Information -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-user-circle"></i>
                    Account Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Username *
                        </label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter username" required>
                        <p class="mt-1 text-xs text-gray-500">Letters, numbers, and underscores only</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Display Name *
                        </label>
                        <input type="text" name="display_name" value="<?php echo htmlspecialchars($display_name); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter display name" required>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-user"></i>
                    Personal Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            First Name *
                        </label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter first name" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name *
                        </label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter last name" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Years of Experience
                        </label>
                        <input type="number" name="years_experience" value="<?php echo htmlspecialchars($years_experience); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter years of experience" min="0">
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-envelope"></i>
                    Contact Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address *
                        </label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter email address" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number *
                        </label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="(555) 123-4567" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter street address">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            City
                        </label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter city">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            State
                        </label>
                        <select name="state" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select State</option>
                            <?php foreach ($us_states as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo ($state === $st) ? 'selected' : ''; ?>>
                                    <?php echo $st; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ZIP Code
                        </label>
                        <input type="text" name="zip_code" value="<?php echo htmlspecialchars($zip_code); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="12345">
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-balance-scale"></i>
                    Professional Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Bar Number *
                        </label>
                        <input type="text" name="bar_number" value="<?php echo htmlspecialchars($bar_number); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter bar number" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            License State *
                        </label>
                        <select name="license_state" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select License State</option>
                            <?php foreach ($us_states as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo ($license_state === $st) ? 'selected' : ''; ?>>
                                    <?php echo $st; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Specializations
                        </label>
                        <div class="checkbox-grid">
                            <?php foreach ($specialization_options as $spec): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="specializations[]" value="<?php echo htmlspecialchars($spec); ?>" 
                                           <?php echo in_array($spec, $specializations) ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700"><?php echo htmlspecialchars($spec); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Court Assignments
                        </label>
                        <div class="checkbox-grid">
                            <?php foreach ($court_options as $court): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="court_assignments[]" value="<?php echo htmlspecialchars($court); ?>" 
                                           <?php echo in_array($court, $court_assignments) ? 'checked' : ''; ?>
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700"><?php echo htmlspecialchars($court); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-phone"></i>
                    Emergency Contact
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Emergency Contact Name
                        </label>
                        <input type="text" name="emergency_contact" value="<?php echo htmlspecialchars($emergency_contact); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter emergency contact name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Emergency Contact Phone
                        </label>
                        <input type="tel" name="emergency_phone" value="<?php echo htmlspecialchars($emergency_phone); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="(555) 123-4567">
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-sticky-note"></i>
                    Additional Information
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Notes
                        </label>
                        <textarea name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Enter any additional notes or comments about this judge..."><?php echo htmlspecialchars($notes); ?></textarea>
                    </div>

                    <div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?php echo $is_active ? 'checked' : ''; ?>
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Account is active</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-4 pt-6">
                <a href="judges.php" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    Add Judge
                </button>
            </div>
        </form>
    </div>

    <script>
        // Form validation and UX enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Add loading state to submit button
            form.addEventListener('submit', function() {
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Judge...';
                submitButton.disabled = true;
            });
            
            // Auto-format phone numbers
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(function(input) {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 6) {
                        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                    } else if (value.length >= 3) {
                        value = value.replace(/(\d{3})(\d{3})/, '($1) $2');
                    }
                    e.target.value = value;
                });
            });
            
            // Username validation
            const usernameInput = document.querySelector('input[name="username"]');
            if (usernameInput) {
                usernameInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^a-zA-Z0-9_]/g, '');
                });
            }
        });
    </script>
</body>
</html>