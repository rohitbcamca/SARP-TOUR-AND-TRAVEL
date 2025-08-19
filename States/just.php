<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>South Goa Exploration Route</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f3f8f6;
      margin: 0;
      padding: 20px;
    }

    .route-container {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      padding: 30px 20px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    h2 {
      text-align: center;
      font-size: 2rem;
      color: #2e7d32;
      position: relative;
      margin-bottom: 30px;
    }

    h2::after {
      content: '';
      position: absolute;
      width: 120px;
      height: 3px;
      background: linear-gradient(to right, #26a69a, #66bb6a);
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
    }

    .pathway {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 15px;
    }

    .step {
      background: #e8f5e9;
      padding: 15px 20px;
      border-radius: 30px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
      font-size: 1rem;
      font-weight: 500;
      color: #2e7d32;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: transform 0.3s ease;
    }

    .step:hover {
      transform: scale(1.05);
    }

    .arrow {
      font-size: 1.5rem;
      color: #26a69a;
    }

    @media (max-width: 768px) {
      .pathway {
        flex-direction: column;
      }

      .arrow {
        transform: rotate(90deg);
      }
    }
  </style>
</head>
<body>

<div class="route-container">
  <h2>🗺 South Goa Exploration Route</h2>
  <div class="pathway">
    <div class="step">✈ Dabolim Airport</div>
    <div class="arrow">➝</div>
    <div class="step">🏖 Palolem Beach</div>
    <div class="arrow">➝</div>
    <div class="step">🎧 Silent Noise Party</div>
    <div class="arrow">➝</div>
    <div class="step">🦋 Butterfly Beach</div>
    <div class="arrow">➝</div>
    <div class="step">🏝 Monkey Island</div>
    <div class="arrow">➝</div>
    <div class="step">🧘‍♂ Yoga Retreats</div>
    <div class="arrow">➝</div>
    <div class="step">🌿 Explore Canacona</div>
    <div class="arrow">➝</div>
    <div class="step">🚉 Canacona Station / Taxi</div>
  </div>
</div>

</body>
</html>