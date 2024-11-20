<?php
$host = 'localhost';
$dbname = 'ischool';
$username = 'root'; // Database username
$password = ''; // Database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"));

if ($requestMethod == 'POST') {
    if (isset($data->action) && $data->action === 'login') {
        $email = $data->email;
        $password = $data->password;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    }

    if (isset($data->action) && $data->action === 'register') {
        $fullName = $data->fullName;
        $email = $data->email;
        $password = password_hash($data->password, PASSWORD_DEFAULT);
        $role = $data->role;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered!']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, :role)");
        if ($stmt->execute(['full_name' => $fullName, 'email' => $email, 'password' => $password, 'role' => $role])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
