<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAAP Document Routing - 2FA Verification</title>
    
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
        h2 { margin:0 0 0.5rem; font-size:1.2rem; text-align:center; color: #a8c5e0; letter-spacing: -0.3px; }
        p.subtitle { margin:0 0 1.5rem; text-align:center; color:#94b7d9; font-size: 0.95rem; line-height: 1.4; }
        .info-box { background: rgba(0, 215, 255, 0.1); border: 1px solid rgba(0, 215, 255, 0.3); border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem; color: #a8c5e0; font-size: 0.9rem; line-height: 1.5; }
        .field { margin-bottom:1rem; }
        .field label { display:block; margin-bottom:0.4rem; color:#aac8ff; font-weight:500; font-size: 0.9rem; }
        .otp-input-group { display: flex; gap: 0.5rem; justify-content: center; margin: 1.5rem 0; }
        .otp-input { width: 50px; height: 50px; border: 1px solid rgba(139,171,255,.27); border-radius: 8px; background: rgba(24,40,82,.64); color: #e9f3ff; font-size: 1.5rem; text-align: center; transition: 0.3s; font-weight: 700; }
        .otp-input:focus { outline: none; border-color: #00dbff; box-shadow: 0 0 12px rgba(0,219,255,.2); }
        .single-input { width: 100%; }
        .btn { width:100%; padding:0.85rem; border:none; border-radius:10px; background:linear-gradient(90deg, var(--neon) 0%, var(--neon2) 100%); color:#03121b; font-weight:700; cursor:pointer; transition:.25s; margin-top: 0.5rem; }
        .btn:hover { transform:translateY(-2px); box-shadow: 0 8px 25px rgba(0,240,255,.3); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .helper { margin-top:1rem; text-align:center; font-size:0.85rem; }
        .helper a { color:#86b2e4; text-decoration: none; }
        .helper a:hover { color: var(--neon); }
        .errors { margin:0 0 1rem; color:#ff8ba7; text-align:center; background: rgba(255, 139, 167, 0.1); padding: 0.6rem; border-radius: 8px; font-size: 0.85rem; border: 1px solid rgba(255, 139, 167, 0.2); }
        .small { margin-top:1.2rem; font-size:0.75rem; color:#647b9b; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>🔐 Two-Factor Authentication</h1>
        <h2 class="mb-3">Verification Code</h2>

        <div class="info-box">
            <strong>📱 Enter your 6-digit code</strong> from your authenticator app (Google Authenticator, Microsoft Authenticator, Authy, etc.)
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

        <form method="POST" action="{{ route('2fa.verify.submit') }}" id="2faForm">
            @csrf
            
            <div class="field">
                <label for="2fa_code">Authentication Code</label>
                <input id="2fa_code" name="2fa_code" type="text" placeholder="000000" value="{{ old('2fa_code') }}" maxlength="6" inputmode="numeric" required autofocus pattern="[0-9]{6}">
            </div>

            <button type="submit" class="btn" id="submitBtn">Verify 2FA Code</button>
        </form>

        <div class="helper">
            <p>Didn't receive a code? <a href="{{ route('login') }}">Return to login</a></p>
        </div>

        <div class="small">
            Lost access to your authenticator? Contact your administrator for account recovery.
        </div>
    </div>

    <script>
        // Auto-submit when 6 digits are entered
        const input = document.getElementById('2fa_code');
        input.addEventListener('input', function() {
            if (this.value.length === 6) {
                // Optional: auto-submit after a short delay
                setTimeout(() => {
                    document.getElementById('2faForm').submit();
                }, 300);
            }
        });

        // Only allow numbers
        input.addEventListener('keypress', function(e) {
            if (isNaN(e.key)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>