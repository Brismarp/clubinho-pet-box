<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Clubinho Pet Box</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --color-black: #1a1a1a;
            --color-charcoal: #2d2d2d;
            --color-white: #ffffff;
            --color-cream: #f5f4f0;
            --color-beige: #e8e4dc;
            --color-gold: #c9a961;
            --font-primary: 'Inter', sans-serif;
            --font-display: 'Playfair Display', serif;
        }
        
        body {
            font-family: var(--font-primary);
            background-color: var(--color-cream);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .login-container {
            background-color: var(--color-white);
            border: 1px solid var(--color-beige);
            border-radius: 4px;
            padding: 3rem;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-container h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            color: var(--color-black);
            margin-bottom: 0.5rem;
        }
        
        .logo-container p {
            color: var(--color-gold);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-charcoal);
            font-weight: 500;
            font-size: 0.9375rem;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--color-beige);
            border-radius: 4px;
            font-family: var(--font-primary);
            font-size: 1rem;
            color: var(--color-black);
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--color-gold);
            background-color: var(--color-cream);
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background-color: var(--color-black);
            color: var(--color-white);
            border: 2px solid var(--color-black);
            border-radius: 4px;
            font-family: var(--font-primary);
            font-size: 0.9375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background-color: var(--color-white);
            color: var(--color-black);
        }
        
        .error-message {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 0.875rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            font-size: 0.9375rem;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: var(--color-gold);
            text-decoration: none;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            color: var(--color-black);
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 2rem 1.5rem;
            }
            
            .logo-container h1 {
                font-size: 1.75rem;
            }
        }
    </style>
    <!-- CSS Inline Mobile Fix -->
    <style>
    html, body { overflow-x: hidden !important; max-width: 100vw !important; }
    * { max-width: 100% !important; }
    img { max-width: 100% !important; height: auto !important; width: auto !important; display: block !important; }
    @media (max-width: 768px) {
        .container { max-width: 100% !important; padding: 20px !important; }
        table { display: block !important; overflow-x: auto !important; }
        .btn { width: 100% !important; display: block !important; margin-bottom: 10px !important; }
        h1 { font-size: 28px !important; }
        h2 { font-size: 24px !important; }
    }
    </style>
<style>header,.header{height:70px!important;max-height:70px!important;min-height:auto!important;padding:.75rem 0!important;display:flex!important;align-items:center!important}header img,.header img,.logo,img[alt*="logo"],img[alt*="Logo"],img[src*="logo"]{max-height:48px!important;height:auto!important;width:auto!important;max-width:200px!important}header .container,.header .container{display:flex!important;align-items:center!important;justify-content:space-between!important;height:100%!important}@media (max-width:768px){header,.header{height:50px!important;padding:.5rem 0!important}header img,.header img,.logo{max-height:40px!important;max-width:150px!important}}@media (max-width:480px){header,.header{height:45px!important}header img,.header img,.logo{max-height:35px!important;max-width:120px!important}}</style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <h1>Clubinho Pet Box</h1>
            <p>Painel Administrativo</p>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                Usuário ou senha incorretos. Tente novamente.
            </div>
        <?php endif; ?>
        
        <form action="authenticate.php" method="POST">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Entrar no Painel</button>
        </form>
        
        <div class="back-link">
            <a href="../index.html">← Voltar ao Site</a>
        </div>
    </div>
</body>
</html>
