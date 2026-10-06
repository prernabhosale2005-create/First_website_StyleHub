<?php
require_once "config.php";
header("Content-Type: application/json");

$action = $_POST["action"] ?? "";

if ($action === "register") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $phone === "" || $password === "") {
        echo json_encode(["success" => false, "message" => "Please fill all fields."]); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Enter a valid email address."]); exit;
    }
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        echo json_encode(["success" => false, "message" => "Phone number must contain 10 digits."]); exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]); exit;
    }
    if ($password !== $confirm) {
        echo json_encode(["success" => false, "message" => "Passwords do not match."]); exit;
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email is already registered."]); exit;
    }
    $check->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        $userId = (int)$stmt->insert_id;
        session_regenerate_id(true);
        $_SESSION["user_id"] = $userId;
        $_SESSION["user_name"] = $name;
        $_SESSION["user_email"] = $email;
        create_remember_token($conn, $userId);
        echo json_encode(["success" => true, "message" => "Registration successful."]); exit;
    }
    echo json_encode(["success" => false, "message" => "Registration failed."]); exit;
}

if ($action === "login") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            create_remember_token($conn, (int)$user["id"]);
            echo json_encode(["success" => true, "message" => "Login successful."]); exit;
        }
    }

    echo json_encode(["success" => false, "message" => "Invalid email or password."]); exit;
}

echo json_encode(["success" => false, "message" => "Invalid request."]);
