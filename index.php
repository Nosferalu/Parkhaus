<?php
// logic to fetch carpark information from database and display it on the homepage
?>
<html>
<head>
    <script src="https://kit.fontawesome.com/60f1874be9.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/stylesheet.css">
    <title>Car Park Homepage</title>
</head>
<body>

<div class="sign">
    <h1><span class="fast-flicker">Wel</span>come<span class="flicker"> to </span>Carpark !</h1>
</div>
<div class="wrapper">
<div class="container">
    <i class="fas fa-smile-beam"></i>
    <span class="num" data-val="340">000</span>
    <span class="text">Current Free Spots</span>
</div>
</div>

</body>
</html>
<script>
    let valueDisplays = document.querySelectorAll(".num");
    let interval = 400;

    valueDisplays.forEach((valueDisplay) => {
        let startValue = 0;
        let endValue = parseInt(valueDisplay.getAttribute("data-val"));
        let duration = Math.floor(interval / endValue);
        let counter = setInterval(function () {
            startValue += 1;
            valueDisplay.textContent = startValue;
            if (startValue == endValue) {
                clearInterval(counter);
            }
        }, duration);
    });
</script>