<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado</title>
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
            border-top: 4px solid #C0392B;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .icono { font-size: 56px; margin-bottom: 0.5rem; }
        .card h1 { color: #0D2F6E; font-size: 20px; font-weight: 600; margin-bottom: 0.5rem; }
        .card p { color: #555E6D; font-size: 14px; line-height: 1.5; margin-bottom: 1.5rem; }
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
        <div class="icono">&#128274;</div>
        <h1>Acceso no autorizado</h1>
        <p>
            No tienes permiso para acceder a esta sección. Si crees que es un error,
            contacta al administrador del sistema.
        </p>
        <a href="{{ route('dashboard') }}" class="btn">Ir al dashboard</a>
    </div>
</body>
</html>
