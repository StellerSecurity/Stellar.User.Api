<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmation code</title>
    <style>
        /* Reset some default styles */
        body, p {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        table {
            border-collapse: collapse;
        }
        /* Container */
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        /* Header */
        .header {
            background-color: #3498db;
            color: #fff;
            text-align: center;
            padding: 10px;
        }
        /* Content */
        .content {
            padding: 20px;
        }
        /* Button */
        .button {
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Reset Password</h1>
    </div>
    <div class="content">
        <p>You have sent a request to reset your password for your Stellar ID.</p>

        <h4>Confirmation code</h4>
        <h2><?php echo e($data['confirmation_code']); ?></h2>

        The confirmation code will expire in 24 hours.<hr><hr>

        If this was not you, you can just ignore this email.
    </div>
</div>
</body>
</html>
<?php /**PATH /home/bb/PhpstormProjects/Stellar.User.Api/resources/views/mails/resetpassword-confirmationcode.blade.php ENDPATH**/ ?>