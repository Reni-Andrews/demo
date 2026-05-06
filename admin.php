<?php
// admin.php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$msg = ""; $msg_type = "";

// --- ACTIONS ---

// 1. Add Subject
if (isset($_POST['add_subject'])) {
    $sub = $_POST['subject_name'];
    $yl = $_POST['year_level'];
    $conn->query("INSERT INTO subjects (subject_name, year_level) VALUES ('$sub', '$yl')");
    $msg = "Subject '$sub' added for $yl."; $msg_type = "success";
}

// 2. Add Assessment (Sub-component)
if (isset($_POST['add_assessment'])) {
    $sub_id = $_POST['subject_id'];
    $assess_name = $_POST['assessment_name'];
    $conn->query("INSERT INTO assessments (subject_id, assessment_name) VALUES ('$sub_id', '$assess_name')");
    $msg = "Assessment '$assess_name' added."; $msg_type = "success";
}

// 3. Save Bulk Marks
if (isset($_POST['save_bulk_marks'])) {
    $assessment_id = $_POST['assessment_id'];
    $marks_array = $_POST['marks']; // Array [reg_no => score]

    foreach ($marks_array as $reg_no => $score) {
        if ($score !== "") { // Only save if not empty
            // Check existing
            $check = $conn->query("SELECT id FROM marks WHERE student_reg_no='$reg_no' AND assessment_id='$assessment_id'");
            if ($check->num_rows > 0) {
                $conn->query("UPDATE marks SET score='$score' WHERE student_reg_no='$reg_no' AND assessment_id='$assessment_id'");
            } else {
                $conn->query("INSERT INTO marks (student_reg_no, assessment_id, score) VALUES ('$reg_no', '$assessment_id', '$score')");
            }
        }
    }
    $msg = "Marks saved for all students!"; $msg_type = "success";
}

// --- DATA FETCHING ---
// Fetch Subjects for dropdowns
$subjects = $conn->query("SELECT * FROM subjects ORDER BY year_level, subject_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        :root { --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); --glass: rgba(255,255,255,0.95); --primary: #667eea; }
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--bg-gradient); min-height: 100vh; padding: 20px; color: #333; }
        
        .container { max-width: 1000px; margin: 0 auto; display: grid; gap: 20px; }
        .card { background: var(--glass); padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        h2, h3 { color: #444; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        /* Forms */
        .row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; flex: 1; min-width: 200px; }
        button { padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        button:hover { opacity: 0.9; }
        
        /* Student List Table */
        .student-list { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .student-list th { background: var(--primary); color: white; text-align: left; padding: 10px; }
        .student-list td { padding: 10px; border-bottom: 1px solid #eee; background: white; }
        .mark-input { width: 80px; text-align: center; font-weight: bold; border: 2px solid #eee; }
        .mark-input:focus { border-color: var(--primary); outline: none; }

        .alert { padding: 10px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 10px; }
        .nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; color: white; }
        .logout { background: #ff4b5c; text-decoration: none; padding: 8px 15px; border-radius: 6px; color: white; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        
        <?php if($msg) echo "<div class='alert'>$msg</div>"; ?>

        <div class="card">
            <h3>1. Add Subjects & Assessments</h3>
            <form method="post" class="row" style="margin-bottom: 20px;">
                <input type="text" name="subject_name" placeholder="Subject Name (e.g. Maths)" required>
                <select name="year_level" required>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                </select>
                <button type="submit" name="add_subject">Add Subject</button>
            </form>

            <form method="post" class="row">
                <select name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php 
                    $sub_list = $conn->query("SELECT * FROM subjects ORDER BY year_level");
                    while($s = $sub_list->fetch_assoc()) { echo "<option value='{$s['id']}'>[{$s['year_level']}] {$s['subject_name']}</option>"; }
                    ?>
                </select>
                <input type="text" name="assessment_name" placeholder="Sub-component (e.g. CA1, Quiz)" required>
                <button type="submit" name="add_assessment" style="background: #28a745;">Add Component</button>
            </form>
            
            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <h4 style="font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 10px;">STUDENT SHARED FOLDERS</h4>
                <a href="department_files.php?folder=Student" class="btn-link" style="display: inline-flex; align-items: center; gap-8px; padding: 12px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: all 0.3s hover:scale-[1.02]">
                    <i class="fas fa-folder-open mr-2"></i> Manage Student Materials
                </a>
                <p style="font-size: 0.75rem; color: #999; mt: 8px;">Upload files here to make them visible on the Student Dashboard "Study Resources" section.</p>
            </div>
        </div>


        <div class="card">
            <h3>2. Enter Marks (Bulk Entry)</h3>
            
            <form method="get" class="row">
                <select name="year" onchange="this.form.submit()">
                    <option value="">-- Select Year --</option>
                    <option value="1st Year" <?php if(isset($_GET['year']) && $_GET['year']=='1st Year') echo 'selected'; ?>>1st Year</option>
                    <option value="2nd Year" <?php if(isset($_GET['year']) && $_GET['year']=='2nd Year') echo 'selected'; ?>>2nd Year</option>
                    <option value="3rd Year" <?php if(isset($_GET['year']) && $_GET['year']=='3rd Year') echo 'selected'; ?>>3rd Year</option>
                </select>
            
                <?php if(isset($_GET['year'])): ?>
                    <select name="subject" onchange="this.form.submit()">
                        <option value="">-- Select Subject --</option>
                        <?php
                        $y = $_GET['year'];
                        $res = $conn->query("SELECT * FROM subjects WHERE year_level='$y'");
                        while($row = $res->fetch_assoc()) {
                            $sel = (isset($_GET['subject']) && $_GET['subject'] == $row['id']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' $sel>{$row['subject_name']}</option>";
                        }
                        ?>
                    </select>
                <?php endif; ?>

                <?php if(isset($_GET['subject'])): ?>
                    <select name="assessment" onchange="this.form.submit()">
                        <option value="">-- Select Assessment --</option>
                        <?php
                        $sid = $_GET['subject'];
                        $res = $conn->query("SELECT * FROM assessments WHERE subject_id='$sid'");
                        while($row = $res->fetch_assoc()) {
                            $sel = (isset($_GET['assessment']) && $_GET['assessment'] == $row['id']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' $sel>{$row['assessment_name']}</option>";
                        }
                        ?>
                    </select>
                <?php endif; ?>
            </form>

            <?php if(isset($_GET['year']) && isset($_GET['assessment'])): 
                $year = $_GET['year'];
                $aid = $_GET['assessment'];
                
                // Fetch all students in that year
                $students = $conn->query("SELECT * FROM users WHERE year_level='$year' ORDER BY reg_no");
            ?>
                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="assessment_id" value="<?php echo $aid; ?>">
                    
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="student-list">
                            <thead>
                                <tr>
                                    <th>Reg No</th>
                                    <th>Name</th>
                                    <th>Enter Mark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($std = $students->fetch_assoc()): 
                                    // Fetch existing mark if any
                                    $reg = $std['reg_no'];
                                    $m_q = $conn->query("SELECT score FROM marks WHERE student_reg_no='$reg' AND assessment_id='$aid'");
                                    $val = ($m_q->num_rows > 0) ? $m_q->fetch_assoc()['score'] : '';
                                ?>
                                <tr>
                                    <td><?php echo $std['reg_no']; ?></td>
                                    <td><?php echo $std['name']; ?></td>
                                    <td>
                                        <input type="number" 
                                               name="marks[<?php echo $std['reg_no']; ?>]" 
                                               value="<?php echo $val; ?>" 
                                               class="mark-input" 
                                               placeholder="-">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" name="save_bulk_marks" style="width: 100%; font-size: 1.1rem;">💾 Save All Marks</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html