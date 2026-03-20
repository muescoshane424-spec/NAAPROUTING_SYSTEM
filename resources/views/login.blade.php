<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAAP Document Routing - Login</title>
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
        h1 { margin:0 0 0.8rem; font-size:2rem; text-align:center; color: #c6e5ff; }
        p.subtitle { margin:0 0 1rem; text-align:center; color:#94b7d9; }
        .field { margin-bottom:0.85rem; }
        .field label { display:block; margin-bottom:0.35rem; color:#aac8ff; font-weight:500; }
        .field input { width:100%; padding:0.78rem 0.85rem; border:1px solid rgba(139,171,255,.27); border-radius:10px; background:rgba(24,40,82,.64); color:#e9f3ff; }
        .field input:focus { outline:none; border-color:#00dbff; box-shadow:0 0 0 2px rgba(0,219,255,.17); }
        .btn { width:100%; padding:0.78rem; border:none; border-radius:10px; background:linear-gradient(90deg, var(--neon) 0%, var(--neon2) 100%); color:#03121b; font-weight:700; cursor:pointer; transition:.25s; }
        .btn:hover { transform:translateY(-1px); box-shadow: 0 10px 20px rgba(0,240,255,.25); }
        .helper { margin-top:0.65rem; text-align:right; color:#86b2e4; font-size:0.87rem; }
        .warning { margin:0.85rem 0 0; text-align:center; font-size:0.9rem; color:#ffd25f; }
        .errors { margin:0.85rem 0 0; color:#ff8ba7; text-align:center; }
        .small { margin-top:0.45rem; font-size:0.8rem; color:#a8bde5; }
        .small a { color:#7fe2ff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>NAAP Document Routing</h1>
        <p class="subtitle">Secure admin login for document tracking & routing</p>

        @if(session('error'))
            <div class="errors">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="field">
                <label for="email">Email Address</label>
                <input id="email" name="email" type="email" placeholder="admin@naap.edu" value="{{ old('email') }}" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••••" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="helper"><a href="#">Forgot Password?</a></div>
        <div class="small">Authorized personnel only. All activity logs are recorded.</div>
    </div>
</body>
</html>