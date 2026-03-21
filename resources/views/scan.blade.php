<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Document Scanner') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-center">
            <div id="reader" style="width: 100%; max-width: 500px; margin: auto; border: 2px solid #ccc; border-radius: 10px;"></div>
            
            <p id="result" class="mt-4 text-green-600 font-bold"></p>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // This takes the QR text and redirects to your tracking route
            document.getElementById('result').innerText = "Scanning: " + decodedText;
            window.location.href = "/track-document/" + decodedText;
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { fps: 15, qrbox: {width: 250, height: 250} },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</x-app-layout>