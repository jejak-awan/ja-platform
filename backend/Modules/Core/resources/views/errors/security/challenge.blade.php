<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifying Connection - {{ config('app.name') }}</title>
    
    <!-- Use Tailwind via Play CDN for the standalone challenge page to ensure premium styling even if main CSS isn't loaded -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Instrument Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: 'hsl(var(--primary, 222 73% 40%))',
                        background: 'hsl(var(--background, 0 0% 0%))',
                        foreground: 'hsl(var(--foreground, 0 0% 100%))',
                        card: 'hsl(var(--card, 0 0% 3%))',
                        border: 'hsl(var(--border, 0 0% 12%))',
                        success: 'hsl(var(--success, 142 70% 45%))',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --primary: 222 73% 40%;
            --background: 0 0% 0%;
            --foreground: 0 0% 100%;
            --card: 0 0% 3%;
            --border: 0 0% 12%;
            --success: 142 70% 45%;
        }

        body {
            background-color: #000;
            color: #fff;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Instrument Sans', sans-serif;
            overflow: hidden;
        }

        .glass-morphism {
            background: rgba(10, 10, 10, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .animate-pulse-slow {
            animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.1; }
            50% { opacity: 0.3; }
        }

        .progress-bar {
            transition: width 0.5s ease-out;
        }
    </style>
</head>
<body>
    <!-- Background elements -->
    <div class="fixed inset-0 z-0">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
    </div>

    <!-- Main Card -->
    <div id="challenge-card" class="relative z-10 w-full max-w-md p-8 glass-morphism rounded-3xl opacity-0 translate-y-4 transition-all duration-700 ease-out">
        <div class="flex flex-col items-center text-center">
            <!-- Icon -->
            <div class="p-5 mb-8 rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                <svg id="shield-icon" class="w-12 h-12 text-blue-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>

            <h2 class="text-2xl font-bold tracking-tight mb-2">Verifying Connection</h2>
            <p id="status-text" class="text-gray-400 mb-10 text-sm h-5">Initializing security check...</p>

            <!-- Progress Bar -->
            <div class="w-full h-1.5 bg-white/5 rounded-full overflow-hidden mb-5">
                <div id="progress-bar" class="progress-bar h-full bg-blue-500 w-0"></div>
            </div>

            <div class="flex items-center gap-2 text-[10px] text-gray-500 font-mono uppercase tracking-[0.2em]">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                <span id="percentage">0</span>% PROCESSING
            </div>
        </div>

        <!-- Decorative corner -->
        <div class="absolute top-0 left-0 w-12 h-12 border-t border-l border-white/10 rounded-tl-3xl"></div>
        <div class="absolute bottom-0 right-0 w-12 h-12 border-b border-r border-white/10 rounded-br-3xl"></div>
    </div>

    <form id="challenge-form" method="POST" action="/api/v1/security/verify-connection" style="display: none;">
        @csrf
        <input type="hidden" name="nonce" value="{{ $nonce }}">
        <input type="hidden" name="solution" id="solution-input">
        <input type="hidden" name="redirect_url" value="{{ $redirectTo }}">
    </form>

    <script>
        const nonce = "{{ $nonce }}";
        const difficulty = {{ $difficulty }};
        const target = '0'.repeat(difficulty);
        
        const progressBar = document.getElementById('progress-bar');
        const percentageText = document.getElementById('percentage');
        const statusText = document.getElementById('status-text');
        const card = document.getElementById('challenge-card');
        const shieldIcon = document.getElementById('shield-icon');

        async function updateUI(progress, text) {
            progressBar.style.width = progress + '%';
            percentageText.innerText = Math.round(progress);
            if (text) statusText.innerText = text;
        }

        async function solveChallenge() {
            // Reveal card
            setTimeout(() => {
                card.classList.remove('opacity-0', 'translate-y-4');
            }, 100);

            await updateUI(10, "Analyzing security metrics...");
            
            let solution = 0;
            const maxAttempts = 1000000;
            const encoder = new TextEncoder();
            
            let currentProgress = 10;

            while (solution < maxAttempts) {
                // Process in chunks to keep UI alive
                for (let i = 0; i < 5000; i++) {
                    const data = encoder.encode(nonce + solution);
                    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                    const hashArray = Array.from(new Uint8Array(hashBuffer));
                    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

                    if (hashHex.startsWith(target)) {
                        await finalize(solution);
                        return;
                    }
                    solution++;
                }
                
                currentProgress = Math.min(85, currentProgress + 1);
                await updateUI(currentProgress);
                await new Promise(resolve => setTimeout(resolve, 0));
            }
            
            statusText.innerText = "Verification failed. Please refresh.";
            statusText.classList.add('text-red-500');
        }

        async function finalize(solution) {
            await updateUI(95, "Finalizing verification...");
            
            // Post solution
            setTimeout(async () => {
                try {
                    const response = await fetch('/api/v1/security/verify-connection', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nonce: nonce,
                            solution: String(solution)
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success && result.data.verified) {
                        await updateUI(100, "Verified! Redirecting...");
                        shieldIcon.classList.remove('text-blue-500', 'animate-pulse');
                        shieldIcon.classList.add('text-green-500');
                        statusText.classList.remove('text-gray-400');
                        statusText.classList.add('text-green-500');
                        
                        setTimeout(() => {
                            // Use cache-busting reload to force Android/Legacy browsers to fetch the fresh 200 OK response
                            const url = new URL(window.location.href);
                            url.searchParams.set('sh_ts', Date.now());
                            window.location.href = url.toString();
                        }, 800);
                    } else {
                        throw new Error("Invalid");
                    }
                } catch (e) {
                    statusText.innerText = "Shield failure. Retrying...";
                    window.location.reload();
                }
            }, 500);
        }

        // Execution entry
        if (window.crypto && window.crypto.subtle) {
            solveChallenge();
        } else {
            statusText.innerText = "Browser not supported. Please upgrade.";
        }
    </script>
</body>
</html>
