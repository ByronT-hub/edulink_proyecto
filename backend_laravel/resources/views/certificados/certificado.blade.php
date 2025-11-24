<!DOCTYPE html>
<html>
<head>
  <style>
    body {
      font-family: Arial;
      background: #f0f7f5;
      text-align: center;
      padding: 40px;
    }
    .box {
      border: 4px solid #2b5f57;
      border-radius: 20px;
      background: #fff;
      padding: 40px;
    }
    h1 { color: #1d403a; }
    h2 { color: #2b5f57; }
    .firma {
      margin-top: 40px;
      font-style: italic;
    }
    .code {
      margin-top: 25px;
      font-size: 14px;
      color: #444;
    }
  </style>
</head>
<body>

  <div class="box">
    <h1>CERTIFICADO DE FINALIZACIÓN</h1>
    <h2>EduLink Academy</h2>

    <p style="font-size:20px; margin-top:25px;">
      Se otorga el presente certificado a:
    </p>

    <h1>{{ $nombre }}</h1>

    <p style="font-size:18px;">
      Por haber completado satisfactoriamente el curso:
    </p>

    <h2>“{{ $curso }}”</h2>

    <p style="margin-top:20px;">
      Fecha: <strong>{{ $fecha }}</strong>
    </p>

    <div class="code">
      Código de Verificación: <strong>{{ $codigo }}</strong>
    </div>

    <div class="firma">
      ______________________________ <br>
      Dirección Académica – EduLink
    </div>
  </div>

</body>
</html>
