<!DOCTYPE html>
<html>

<head>
    <title>Cek Ongkir</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
</head>

<body>
    <form id="ongkirForm">
   
        <label>Lokasi Tujuan:</label><br>
        <select id="destination" placeholder="Ketik kota atau provinsi..."></select>
        <br><br>

        <label>Berat (gram):</label><br>
        <input type="number" name="weight" id="weight" placeholder="Berat (gram)" required><br><br>

        <label>Kurir:</label><br>
        <select name="courier" id="courier" required>
            <option value="">Pilih Kurir</option>
            <option value="jne">JNE</option>
            <option value="tiki">TIKI</option>
            <option value="pos">POS Indonesia</option>
        </select><br><br>

        <button type="submit">Cek Ongkir</button>
    </form>

    <div id="result" style="margin-top: 20px;"></div>

    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const destinationSelect = document.getElementById('destination');
            const resultDiv = document.getElementById('result');

            // Inisialisasi Tom Select
            const tom = new TomSelect(destinationSelect, {
                valueField: 'id',         // ambil dari data.id
                labelField: 'label',      // tampilkan data.label
                searchField: ['label'],   // cari berdasarkan data.label
                load: function (query, callback) {
                    if (query.length < 3) return callback();

                    fetch(`/location?keyword=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.meta && data.meta.code === 200 && Array.isArray(data.data)) {
                                callback(data.data); // langsung kirim data ke Tom Select
                            } else {
                                callback();
                            }
                        })
                        .catch(() => callback());
                }
            });

            document.getElementById('ongkirForm').addEventListener('submit', function (event) {
                event.preventDefault();

                const origin = 31555; // ID kota asal
                const destination = destinationSelect.value;
                const weight = document.getElementById('weight').value;
                const courier = document.getElementById('courier').value;
          

                // Validasi input
                if (!destination || !weight || !courier) {
                    alert('Pastikan semua kolom terisi dengan benar!');
                    return;
                }

                fetch('/cost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        origin,
                        destination,
                        weight,
                        courier,
                       
                    
                    })
                })
                .then(response => response.json())
                .then(data => {
    resultDiv.innerHTML = ''; // Clear previous result
    console.log('Response dari server:', data);

    if (data.meta?.code === 200 && Array.isArray(data.data)) {
        data.data.forEach(service => {
            const value = service.cost;
            const etd = service.etd;
            const serviceName = service.service;

            const div = document.createElement('div');
            div.textContent = `${serviceName} : ${value} Rupiah (${etd} hari)`;
            resultDiv.appendChild(div);
        });
    } else {
        resultDiv.textContent = 'Gagal mendapatkan biaya pengiriman.';
        console.warn('Struktur data tidak sesuai:', data);
    }
})

            });
        });
    </script>
</body>

</html>
