<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Segoe+UI:400,600&display=swap">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
          h2 {
            text-align: center;
            color: #1abc9c;
            margin-bottom: 20px;
        }
        
        .form-box {
            background: #fff;
            padding: 30px 25px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
        }

        .form-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1abc9c;
        }

        .form-box input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-box button {
            width: 100%;
            padding: 12px;
            background-color: #1abc9c;
            color: white;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-box button:hover {
            background-color: #1abc9c;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #1abc9c;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

      
    </style>
</head>
<body>

<div class="form-box">
    <h2>Forgot Password</h2>
    <form method="post" action="send_reset_link.php">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <div class="back-link">
        <a href="login.php">Back to Login</a>
    </div>
</div>

</body>
</html>
