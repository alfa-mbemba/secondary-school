<?php
// SMS Alert Configuration File
// To enable actual SMS, get API from Twilio, TextLocal, MSG91, etc.

function sendSMS($phone_number, $message) {
    // ======================================================
    // OPTION 1: Using TextLocal API (India)
    // ======================================================
    /*
    $apiKey = 'YOUR_TEXTLOCAL_API_KEY';
    $sender = 'SCHOOL';
    $url = "https://api.textlocal.in/send/";
    $data = array('apikey' => $apiKey, 'numbers' => $phone_number, 'sender' => $sender, 'message' => $message);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
    */
    
    // ======================================================
    // OPTION 2: Using Twilio
    // ======================================================
    /*
    require_once 'vendor/autoload.php';
    use Twilio\Rest\Client;
    $sid = 'YOUR_TWILIO_SID';
    $token = 'YOUR_TWILIO_TOKEN';
    $twilio = new Client($sid, $token);
    $message = $twilio->messages->create($phone_number, ['from' => 'YOUR_TWILIO_NUMBER', 'body' => $message]);
    return $message->sid;
    */
    
    // ======================================================
    // DEMO MODE: Log to file (for testing)
    // ======================================================
    $log = "[".date('Y-m-d H:i:s')."] To: $phone_number | Message: $message\n";
    file_put_contents('sms_log.txt', $log, FILE_APPEND);
    return true;
}

// Function to send absence alert to parent
function sendAbsenceAlert($student_id, $date) {
    global $conn;
    
    // Get student and parent details
    $student = $conn->query("SELECT s.*, p.phone as parent_phone, p.full_name as parent_name 
                              FROM students s 
                              JOIN parent_students ps ON s.id = ps.student_id 
                              JOIN parents p ON ps.parent_id = p.id 
                              WHERE s.id = $student_id")->fetch_assoc();
    
    if($student && $student['parent_phone']){
        $message = "Dear {$student['parent_name']}, your child {$student['full_name']} (Adm: {$student['admission_no']}) was ABSENT on {$date}. Please check with the school. - School Management System";
        sendSMS($student['parent_phone'], $message);
        return true;
    }
    return false;
}

// Auto-trigger on marking absent - call this from mark_attendance.php
// Add this line when saving attendance: 
// if($status == 'Absent'){ sendAbsenceAlert($student_id, $date); }
?>