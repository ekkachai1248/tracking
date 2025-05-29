<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Multi-Car Tracking</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #map { height: 100vh; margin: 0; padding: 0; }
    body { margin: 0; }
  </style>
</head>
<body>
  <div id="map"></div>

  <script>
    const map = L.map('map').setView([16.1663989, 103.174688], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const icon = L.icon({
      iconUrl: "https://cdn-icons-png.flaticon.com/128/3061/3061696.png",
      iconSize: [32, 32],
      iconAnchor: [16, 32]
    });

    const markers = {};

    function formatTime(timestamp) {
      const date = new Date(timestamp * 1000);
      return date.toLocaleString();
    }

    async function loadLatest() {
      try {
        const res = await fetch("http://199.21.175.112:1880/latest");
        const data = await res.json();

        data.forEach(point => {
          const latlng = [point.lat, point.lon];
          const popupText = `
            🚗 <strong>รถ:</strong> ${point.tid}<br>
            🕒 <strong>เวลา:</strong> ${formatTime(point.tst)}<br>
            📍 <strong>พิกัด:</strong> ${point.lat.toFixed(5)}, ${point.lon.toFixed(5)}
          `;

          if (!markers[point.tid]) {
            const marker = L.marker(latlng, { icon }).addTo(map);
            marker.bindPopup(""); // สร้าง popup เปล่าไว้ล่วงหน้า

            marker.on('click', () => {
              marker.setPopupContent(popupText).openPopup();
              setTimeout(() => marker.closePopup(), 3000);
            });

            markers[point.tid] = marker;
          } else {
            markers[point.tid].setLatLng(latlng);
          }
        });

      } catch (err) {
        console.error("โหลดข้อมูลผิดพลาด:", err);
      }
    }

    loadLatest();
    setInterval(loadLatest, 5000);
  </script>
</body>
</html>
