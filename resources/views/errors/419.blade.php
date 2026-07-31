<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión expirada</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/LOGO3.jpeg') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0D2F6E;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: #ffffff;
            border-radius: 14px;
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 100%;
            text-align: center;
            border-top: 4px solid #F5C518;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .icono { font-size: 56px; margin-bottom: 0.5rem; }
        .card h1 { color: #0D2F6E; font-size: 20px; font-weight: 600; margin-bottom: 0.5rem; }
        .card p { color: #555E6D; font-size: 14px; line-height: 1.5; margin-bottom: 1.5rem; }
        .contador { color: #93aad4; font-size: 13px; margin-bottom: 1rem; }
        .btn {
            display: inline-block;
            background: #1A4FA8;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.2s;
        }
        .btn:hover { background: #0D2F6E; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icono">&#9203;</div>
        <h1>Tu sesión expiró</h1>
        <p>
            Por seguridad, la sesión se cierra automáticamente después de un tiempo
            sin actividad. No te preocupes, puedes volver a iniciar sesión de inmediato.
        </p>
        <p class="contador">Te redirigiremos al login en <span id="segundos">5</span> segundos…</p>
        <a href="{{ route('login') }}" class="btn">Ir al inicio de sesión</a>
    </div>

    <script>
        (function () {
            let segundos = 5;
            const span = document.getElementById('segundos');
            const intervalo = setInterval(function () {
                segundos--;
                if (span) span.textContent = segundos;
                if (segundos <= 0) {
                    clearInterval(intervalo);
                    window.location.href = "{{ route('login') }}";
                }
            }, 1000);
        })();
    </script>
</body>
</html>
