<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Latest Location of Each Vehicle</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #map { height: 100vh; width: 100vw; margin: 0; padding: 0; }
    body { margin: 0; }
  </style>
</head>
<body>
  <div id="map"></div>

  <script>
    const map = L.map('map').setView([16.1663989, 103.174688], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

const iconColors = {
  "C1": "3061/3061696",     // ดำ
  "71": "2891/2891491",     // แดง
  "C2": "2891/2891493",     // น้ำเงิน
  "C3": "2891/2891497",     // เขียว
  "C4": "2891/2891495",     // ม่วง
  "C5": "2891/2891496",     // ส้ม
  "C6": "2891/2891492",     // ฟ้า
  "C7": "2891/2891494",     // ชมพู
  "C8": "2891/2891499",     // เทาเข้ม
  "C9": "2891/2891498",     // ทอง
  "default": "3061/3061696" // ดำ (ค่าเริ่มต้น)
};


    function createIcon(tid) {
      const colorPath = iconColors[tid] || iconColors.default;
      return L.icon({
        iconUrl: `https://cdn-icons-png.flaticon.com/128/${colorPath}.png`,
        iconSize: [32, 32],
        iconAnchor: [16, 32]
      });
    }

    const markers = {};

    function formatTime(timestamp) {
      const date = new Date(timestamp * 1000);
      return date.toLocaleString();
    }

    async function loadLatestPoints() {
      try {
        const res = await fetch("http://199.21.175.112:1880/latest");
        const points = await res.json();

        if (!Array.isArray(points)) return;

        points.forEach(point => {
          if (!point.lat || !point.lon || !point.tid) return;

          const tid = point.tid;
          const latlng = [point.lat, point.lon];
          const icon = createIcon(tid);
          const popupContent = `
            🚗 <strong>รถ:</strong> ${tid}<br>
            🕒 <strong>เวลา:</strong> ${formatTime(point.tst)}<br>
            📍 <strong>พิกัด:</strong> ${point.lat.toFixed(5)}, ${point.lon.toFixed(5)}
          `;

          if (!markers[tid]) {
            const marker = L.marker(latlng, { icon }).addTo(map);
            marker.bindPopup("");

            marker.on('click', () => {
              marker.setPopupContent(popupContent).openPopup();
              setTimeout(() => marker.closePopup(), 3000);
            });

            markers[tid] = marker;
            map.setView(latlng, 16); // กึ่งกลางเมื่อมีคันใหม่เพิ่มเข้ามา
          } else {
            markers[tid].setLatLng(latlng);
            map.setView(latlng, 16); // กึ่งกลางไปยังตำแหน่งล่าสุดของแต่ละคัน
          }
        });

      } catch (err) {
        console.error("โหลดข้อมูลล้มเหลว:", err);
      }
    }

    loadLatestPoints();
    setInterval(loadLatestPoints, 5000);
  </script>
</body>
</html>
