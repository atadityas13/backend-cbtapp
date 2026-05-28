<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Verifikasi Keamanan</title>
  <style>
    body {
      background-color: #000;
      color: #00ff00;
      font-family: 'Courier New', Courier, monospace;
      margin: 0;
      padding: 0;
    }

    #terminal-container {
      padding: 10px;
      overflow-x: auto;
    }

    #terminal {
      display: block;
      white-space: pre;
      font-size: 3vw;
      line-height: 1.2;
    }

    #countdown {
      margin-top: 10px;
      font-size: 4vw;
      text-align: center;
      color: #ffdf00;
    }

    #footer {
      margin-top: 20px;
      text-align: center;
      font-size: 3vw;
      color: #999;
      padding-bottom: 30px;
    }

    .terminal-link {
      display: block !important;
      color: #ffffff !important;
      background-color: #ff0000 !important;
      padding: 5px 10px !important;
      margin: 10px auto !important;
      cursor: pointer !important;
      font-weight: bold !important;
      font-size: 4vw !important;
      text-align: center !important;
      text-decoration: none !important;
      border: 2px solid #ffffff !important;
      white-space: nowrap !important;
      border-radius: 5px !important;
      width: auto !important;
      max-width: 90% !important;
      pointer-events: auto !important;
      font-family: 'Courier New', Courier, monospace !important;
    }

    .blink-text {
      display: inline-block !important;
    }

    .bypass-link {
      display: block !important;
      margin: 10px auto !important;
      font-size: 2.5vw !important;
      color: #ff4444 !important;
      text-decoration: underline !important;
      cursor: pointer !important;
      text-align: center !important;
      font-family: 'Courier New', Courier, monospace !important;
    }

    .bypass-link:hover {
      color: #ff8888 !important;
    }
  </style>
</head>
<body>
  <div id="terminal-container">
    <pre id="terminal">

   _____ ____ _______                        
  / ____|  _ \__   __|     /\                
 | |    | |_) | | |______ /  \   _ __  _ __  
 | |    |  _ <  | |______/ /\ \ | '_ \| '_ \ 
 | |____| |_) | | |     / ____ \| |_) | |_) |
  \_____|____/  |_|    /_/    \_\ .__/| .__/ 
                                | |   | |    
                                |_|   |_|    

     C B T - A p p  MTsN 11 Majalengka

------------------------------------------------------------
</pre>
  </div>

  <div id="countdown">Menyiapkan CBT-App...</div>
  <div id="footer">
    CBT-App MTsN 11 Majalengka v.4.2.3<br>
    &copy; 2026 Developed by ATA DevLabs
  </div>

  <script>
    // ── Variabel dari PHP/Blade ──────────────────────────────────────────────
    const isNewAndroidApp  = {{ $isNewAndroidApp  ? 'true' : 'false' }};
    const isDesktopVersion = {{ $isDesktopVersion ? 'true' : 'false' }};
    const isAppValid       = isNewAndroidApp || isDesktopVersion;
    const downloadLink     = "{{ $downloadLink }}";

    // ── DOM refs ─────────────────────────────────────────────────────────────
    const terminal  = document.getElementById('terminal');
    const countdown = document.getElementById('countdown');

    // ── Web Audio API: Beep Sintetik (100% Offline, dijamin berbunyi) ────────
    function playBeep(freq = 800, duration = 0.15, vol = 0.3) {
      try {
        const ctx        = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        const gainNode   = ctx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);

        oscillator.type            = 'square';
        oscillator.frequency.value = freq;
        gainNode.gain.setValueAtTime(vol, ctx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);

        oscillator.start(ctx.currentTime);
        oscillator.stop(ctx.currentTime + duration);
      } catch (e) { /* Abaikan jika AudioContext tidak tersedia */ }
    }

    // ── Langkah-langkah loading terminal ────────────────────────────────────
    const loadingSteps = [
      "Memuat aset sistem...",
      "Memeriksa koneksi jaringan...",
      "Memeriksa jenis browser...",
      "Memverifikasi versi resmi aplikasi...",
      "Aplikasi terverifikasi, mengunci aplikasi...",
      "Menghubungkan ke server pusat...",
      "Menginisialisasi server ujian...",
      "Melakukan sinkronisasi waktu server...",
      "Memeriksa periode waktu ujian...",
      "Menginisialisasi antarmuka pengguna...",
      "Verifikasi selesai. Menyiapkan antarmuka CBT-App..."
    ];

    let currentStep = 0;

    function appendLine(line) {
      terminal.appendChild(document.createTextNode(line + "\n"));
      terminal.scrollTop = terminal.scrollHeight;
    }

    function showLoadingBar(stepText, callback) {
      const barLength = 10;
      let fill = 0;
      const prefix = `> ${stepText} `;

      const barNode = document.createTextNode("");
      terminal.appendChild(barNode);
      terminal.appendChild(document.createTextNode("\n"));

      const interval = setInterval(() => {
        if (fill <= barLength) {
          barNode.nodeValue = prefix + "[" + "#".repeat(fill) + "-".repeat(barLength - fill) + "]";
          terminal.scrollTop = terminal.scrollHeight;
          fill++;
        } else {
          clearInterval(interval);
          playBeep();   // 🔊 Suara beep sintetik Web Audio API
          callback();
        }
      }, 40);
    }

    function showNextStep() {
      // ── Interseptor Langkah 4 (index 3): Verifikasi versi resmi ───────────
      if (currentStep === 3 && !isAppValid) {
        const step = loadingSteps[currentStep];
        showLoadingBar(step, () => {
          appendLine("");
          appendLine("[!] ERROR: VERIFIKASI GAGAL!");
          appendLine("[!] Perangkat tidak sesuai atau Aplikasi usang.");
          appendLine("[!] Membatalkan proses penguncian layar...");
          appendLine("");

          // Tombol unduh berkedip merah-putih → Play Store
          const link = document.createElement("button");
          link.className = "terminal-link";

          const blinkSpan = document.createElement("span");
          blinkSpan.className = "blink-text";
          blinkSpan.innerText = "UNDUH VERSI TERBARU DISINI";
          link.appendChild(blinkSpan);

          setInterval(() => {
            blinkSpan.style.visibility =
              (blinkSpan.style.visibility === 'hidden' ? 'visible' : 'hidden');
          }, 500);

          const handleUpdate = () => { window.location.href = downloadLink; };
          link.onclick      = handleUpdate;
          link.ontouchstart = handleUpdate;
          document.getElementById("terminal-container").appendChild(link);

          const statusLine = document.createElement("span");
          statusLine.className = "blink-text";
          statusLine.innerText = "\n[!] STATUS: MENUNGGU TINDAKAN USER...\n";
          terminal.appendChild(statusLine);

          // Opsi bypass risiko (tersembunyi, kecil)
          const bypass = document.createElement("div");
          bypass.className = "bypass-link";
          bypass.innerText = "Tetap lanjutkan ujian (Risiko Keamanan)";
          bypass.onclick = function () {
            appendLine("");
            appendLine("[!] PERINTAH DITERIMA: Melanjutkan secara paksa...");
            appendLine("[!] Memulai kembali urutan sistem...");
            currentStep++;
            showNextStep();
            bypass.remove();
            link.remove();
            statusLine.remove();
          };
          countdown.parentNode.insertBefore(bypass, countdown.nextSibling);

          appendLine("");
          countdown.innerText = "VERIFIKASI TERHENTI - PERLU PEMBARUAN";
          countdown.style.color = "#ff4444";

          // Beep peringatan berulang 3 kali
          playBeep(400, 0.3);
          setTimeout(() => playBeep(400, 0.3), 400);
          setTimeout(() => playBeep(400, 0.3), 800);
        });
        return;
      }

      if (currentStep < loadingSteps.length) {
        const step = loadingSteps[currentStep];
        showLoadingBar(step, () => {

          // ── Langkah 5 (index 4): Kunci layar via Android JS Bridge ────────
          if (currentStep === 4) {
            if (!isDesktopVersion && window.Android && window.Android.triggerLock) {
              window.Android.triggerLock();
            }
          }

          currentStep++;

          // Jeda lebih lama setelah screen pinning agar terasa mantap
          let delay = 100;
          if (currentStep === 5) delay = 1500;

          setTimeout(showNextStep, delay);
        });
      } else {
        startCountdown(5);
      }
    }

    function startCountdown(seconds) {
      let timeLeft = seconds;
      countdown.innerText = `CBT-App akan terbuka dalam ${timeLeft} detik...`;

      const interval = setInterval(() => {
        timeLeft--;
        countdown.innerText = `CBT-App akan terbuka dalam ${timeLeft} detik...`;

        if (timeLeft <= 0) {
          clearInterval(interval);
          window.location.href = '/portal';
        }
      }, 1000);
    }

    // ── Mulai proses setelah 500ms ────────────────────────────────────────────
    setTimeout(showNextStep, 500);
  </script>
</body>
</html>
