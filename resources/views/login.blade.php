<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAAP Document Routing - Login</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #007bff;
            --bg: #071230;
            --panel: rgba(10, 14, 38, .85);
            --neon: #00f0ff;
            --neon2: #b400ff;
            --text: #eff3ff;
        }
        * { box-sizing: border-box; }
        body {
            margin:0;
            min-height:100vh;
            font-family:'Poppins', 'Inter', sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top left, rgba(0,240,255,.16), transparent 34%),
                        radial-gradient(circle at bottom right, rgba(180,0,255,.14), transparent 32%),
                        linear-gradient(140deg, #060c28 0%, #091644 55%, #040a21 100%);
            display:grid;
            place-items:center;
        }
        .wrapper { width:min(440px, 92vw); padding: 1.8rem; background: var(--panel); border:1px solid rgba(70,157,255,.22); border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,.35); }
        h1 { margin:0 0 0.8rem; font-size:1.8rem; text-align:center; color: #c6e5ff; letter-spacing: -0.5px; }
        p.subtitle { margin:0 0 1.5rem; text-align:center; color:#94b7d9; font-size: 0.95rem; line-height: 1.4; }
        .field { margin-bottom:1rem; }
        .field label { display:block; margin-bottom:0.4rem; color:#aac8ff; font-weight:500; font-size: 0.9rem; }
        .field input { width:100%; padding:0.85rem; border:1px solid rgba(139,171,255,.27); border-radius:10px; background:rgba(24,40,82,.64); color:#e9f3ff; transition: 0.3s; }
        .field input:focus { outline:none; border-color:#00dbff; box-shadow:0 0 12px rgba(0,219,255,.2); }
        .btn { width:100%; padding:0.85rem; border:none; border-radius:10px; background:linear-gradient(90deg, var(--neon) 0%, var(--neon2) 100%); color:#03121b; font-weight:700; cursor:pointer; transition:.25s; margin-top: 0.5rem; }
        .btn:hover { transform:translateY(-2px); box-shadow: 0 8px 25px rgba(0,240,255,.3); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .helper { margin-top:1rem; text-align:right; font-size:0.85rem; }
        .helper a { color:#86b2e4; text-decoration: none; }
        .helper a:hover { color: var(--neon); }
        .errors { margin:0 0 1rem; color:#ff8ba7; text-align:center; background: rgba(255, 139, 167, 0.1); padding: 0.6rem; border-radius: 8px; font-size: 0.85rem; border: 1px solid rgba(255, 139, 167, 0.2); }
        .small { margin-top:1.2rem; font-size:0.75rem; color:#647b9b; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem; }
        .info-box { background: rgba(0, 215, 255, 0.1); border: 1px solid rgba(0, 215, 255, 0.2); border-radius: 8px; padding: 0.8rem; margin-bottom: 1.5rem; font-size: 0.8rem; color: #a8c5e0; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>NAAP Document Routing</h1>
        <p class="subtitle">Secure document routing system for your organization</p>

        <div class="info-box">
            <strong style="color: #00f0ff;">🔒 Password Requirements:</strong><br>
            • Minimum 12 characters • Uppercase, lowercase, numbers & special chars<br>
            • 2FA enabled for maximum security
        </div>

        {{-- Success/Error Alerts --}}
        @if(session('error'))
            <div class="errors">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="errors">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
            @csrf
            <div class="field">
                <label for="username">Username or Email</label>
                <input id="username" name="username" type="text" placeholder="admin or admin@naap.org" value="{{ old('username') }}" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••••" required>
            </div>
            <button type="submit" class="btn" id="submitBtn">Sign In</button>
        </form>

        <div class="helper"><a href="#">Forgot Password?</a></div>
        <div class="small">© 2024 NAAP. All rights reserved.</div>
    </div>

    <script>
        // Prevent multiple clicks which can cause CSRF token mismatch
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        loginForm.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Authenticating...';
        });
    </script>
</body>
</html>