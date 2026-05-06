<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize Input
    $regno = strtoupper(trim($_POST['regno']));
    $password = trim($_POST['password']);

    // 2. Validate Password (Must be same as Reg No)
    if ($regno !== $password) {
        header("Location: index.php?error=Invalid Password. Password is your Reg No.");
        exit();
    }

    // 3. Logic to check Student Ranges
    $isValid = false;
    $studentYear = "";

    // Parse the Register Number
    // Format: BU (Prefix) + 23 (Year) + 17 (Dept) + 13 (Roll)
    
    if (strlen($regno) === 8) {
        
        $prefix = substr($regno, 0, 2); // 'BU'
        $batch  = substr($regno, 2, 2); // '23', '24', '25'
        $dept   = substr($regno, 4, 2); // '17' (Department Code)
        $roll   = intval(substr($regno, 6, 2)); // The number at the end

        // CHECK 1: Must be 'BU' and Dept '17'
        if ($prefix === 'BU' && $dept === '17') {
            
            // --- 3rd Year Check (Batch 23) ---
            // Your Rule: Starts from 13 to 50
            if ($batch === '23') {
                if ($roll >= 13 && $roll <= 50) {
                    $isValid = true;
                    $studentYear = "3rd Year";
                }
            }
            
            // --- 2nd Year Check (Batch 24) ---
            // Your Rule: Starts from 01 to 55
            elseif ($batch === '24') {
                if ($roll >= 1 && $roll <= 55) {
                    $isValid = true;
                    $studentYear = "2nd Year";
                }
            }

            // --- 1st Year Check (Batch 25) ---
            // Your Rule: Starts from 01 to 55
            elseif ($batch === '25') {
                if ($roll >= 1 && $roll <= 55) {
                    $isValid = true;
                    $studentYear = "1st Year";
                }
            }
        }
    }

    // 4. Final Result
    if ($isValid) {
        $_SESSION['user'] = $regno;
        $_SESSION['year'] = $studentYear;
        header("Location: student_dashboard.php");
        exit();
    } else {
        // Failed
        header("Location: student_login.html?error=Access Denied: ID not found in Department 17 records.");
        exit();
    }

} else {
    header("Location: student_login.html");
    exit();
}
?>