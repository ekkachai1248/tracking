<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OwnTracks Real-Time Map</title>
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

    const customIcon = L.icon({
      iconUrl: "https://cdn-icons-png.flaticon.com/128/3061/3061696.png",
      iconSize: [32, 32],
      iconAnchor: [16, 32]
    });

    let polyline = null;
    let marker = null;

    function formatTime(timestamp) {
      const date = new Date(timestamp * 1000);
      return date.toLocaleString();
    }

    async function loadTracking() {
      try {
        const res = await fetch("http://199.21.175.112:1880/latest20");
        const points = await res.json();

        if (!points.length) return;

        const validPoints = points.filter(p => p.lat !== undefined && p.lon !== undefined);
        if (!validPoints.length) return;

        const latlngs = validPoints.map(p => [p.lat, p.lon]);

        // ลบเส้นและหมุดเก่า
        if (polyline) map.removeLayer(polyline);
        if (marker) map.removeLayer(marker);

        // วาดเส้น
        polyline = L.polyline(latlngs, { color: 'red' }).addTo(map);

        const latest = validPoints[0];
        const latestLatLng = [latest.lat, latest.lon];

        // หมุดตำแหน่งล่าสุด
        marker = L.marker(latestLatLng, { icon: customIcon }).addTo(map);

        marker.on('click', () => {
          const popupText = `
            🚗 <strong>รถ:</strong> ${latest.tid || "-"}<br>
            🕒 <strong>เวลา:</strong> ${formatTime(latest.tst)}<br>
            📍 <strong>พิกัด:</strong> ${latest.lat.toFixed(5)}, ${latest.lon.toFixed(5)}
          `;
          marker.bindPopup(popupText).openPopup();
          setTimeout(() => marker.closePopup(), 3000);
        });

        // เลื่อนแผนที่ไปกึ่งกลางตามตำแหน่งล่าสุด
        map.setView(latestLatLng, 16);

      } catch (err) {
        console.error("โหลดข้อมูลล้มเหลว:", err);
      }
    }

    loadTracking();
    setInterval(loadTracking, 5000); // โหลดใหม่ทุก 5 วินาที
  </script>
</body>
</html>
