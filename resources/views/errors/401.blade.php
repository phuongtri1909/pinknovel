<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 - Unauthorized</title>
    <style>
        /* styles.css */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            overflow: hidden;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .bubble {
            position: absolute;
            bottom: -50px;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: rise 10s infinite ease-in-out;
        }

        .bubble:nth-child(1) {
            left: 10%;
            width: 40px;
            height: 40px;
            animation-duration: 8s;
            animation-delay: 0s;
        }

        .bubble:nth-child(2) {
            left: 30%;
            width: 20px;
            height: 20px;
            animation-duration: 6s;
            animation-delay: 2s;
        }

        .bubble:nth-child(3) {
            left: 50%;
            width: 50px;
            height: 50px;
            animation-duration: 12s;
            animation-delay: 4s;
        }

        .bubble:nth-child(4) {
            left: 70%;
            width: 30px;
            height: 30px;
            animation-duration: 10s;
            animation-delay: 6s;
        }

        .bubble:nth-child(5) {
            left: 90%;
            width: 25px;
            height: 25px;
            animation-duration: 7s;
            animation-delay: 8s;
        }

        @keyframes rise {
            0% {
                transform: translateY(0) scale(1);
                opacity: 0.7;
            }

            50% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100vh) scale(1.2);
                opacity: 0;
            }
        }

        .container {
            position: relative;
            z-index: 2;
        }

        .error-animation {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }

        .circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: #fff;
            position: absolute;
            top: 0;
            left: 0;
            animation: circlePulse 1.5s infinite;
        }

        .cross {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .cross span {
            position: absolute;
            width: 60%;
            height: 10%;
            background: #ff3d3d;
            border-radius: 5px;
        }

        .cross span:first-child {
            transform: rotate(45deg);
            animation: crossMove 1.5s infinite;
        }

        .cross span:last-child {
            transform: rotate(-45deg);
            animation: crossMove 1.5s infinite reverse;
        }

        h1 {
            font-size: 4rem;
            margin-bottom: 10px;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background: #ff3d3d;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #e32f2f;
        }

        @keyframes circlePulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        @keyframes crossMove {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }
    </style>
</head>

<body>
    <div class="background">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>
    <div class="container">
        <div class="error-animation">
            <div class="circle"></div>
            <div class="cross">
                <span></span>
                <span></span>
            </div>
        </div>
        <h1>401</h1>
        <p>Oops! You are not authorized to access this page.</p>
    </div>
</body>

</html>
