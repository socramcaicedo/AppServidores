<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0D2F6E;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
            width: 100%;
            max-width: 400px;
            padding: 1rem;
        }

        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .logo-circulo {
            width: 64px;
            height: 64px;
            background: #F5C518;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .login-logo h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 600;
            text-align: center;
        }

        .login-logo p {
            color: #93aad4;
            font-size: 13px;
            text-align: center;
        }

        .login-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 2rem;
            width: 100%;
            border-top: 4px solid #F5C518;
        }

        .login-card h2 {
            font-size: 18px;
            font-weight: 600;
            color: #0D2F6E;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #3a4255;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 13px;
            border: 1px solid #D1DCF0;
            border-radius: 7px;
            font-size: 14px;
            color: #1a1a2e;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus { border-color: #1A4FA8; }

        .input-error { border-color: #C0392B !important; }

        .error-msg {
            color: #C0392B;
            font-size: 12px;
            margin-top: 4px;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .checkbox-row label {
            margin: 0;
            font-weight: 400;
            color: #555E6D;
            font-size: 13px;
        }

        .btn-login {
            width: 100%;
            padding: 11px;
            background: #1A4FA8;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-login:hover { background: #0D2F6E; }

        .login-footer {
            color: #93aad4;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-logo">
        <div class="logo-circulo">&#9765;</div>
        <h1>Gestión de Servidores</h1>
        <p>Sistema de coordinación de cultos</p>
    </div>

    <div class="login-card">
        <h2>Iniciar Sesión</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    value="{{ old('usuario') }}"
                    placeholder="Tu nombre de usuario"
                    class="{{ $errors->has('usuario') ? 'input-error' : '' }}"
                    autocomplete="username"
                >
                @error('usuario')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Tu contraseña"
                    class="{{ $errors->has('password') ? 'input-error' : '' }}"
                    autocomplete="current-password"
                >
                @error('password')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordar sesión</label>
            </div>

            <button type="submit" class="btn-login">Ingresar al sistema</button>
        </form>
    </div>

    <p class="login-footer">Solo personal autorizado de la iglesia</p>
</div>

</body>
</html>