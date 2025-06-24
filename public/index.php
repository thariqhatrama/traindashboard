<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Kereta</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <h1>🚦 Monitoring Jalur Kereta</h1>
  <div class="track-container">
    <!-- Peron Stasiun -->
    <div class="station">
      <div class="checkpoint" id="SU">SU</div>
      <div class="checkpoint" id="SS">SS</div>
    </div>
    
    <!-- Jalur CP1 - CP5 -->
    <div class="track">
      <div class="checkpoint" id="CP1">CP1</div>
      <div class="checkpoint" id="CP2">CP2</div>
      <div class="checkpoint" id="CP3">CP3</div>
      <div class="checkpoint" id="CP4">CP4</div>
      <div class="checkpoint" id="CP5">CP5</div>
    </div>
  </div>
  
  <div id="last-update">Last update: -</div>

  <script src="script.js"></script>
</body>
</html>
