<?php
// ส่วนหัวของหน้าเว็บ
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beacon</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>

<body>
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-custom">
    <a class="navbar-brand" href="#">Beacon</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="/">รายชื่อผู้ใช้</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/ball.php">บอลลูน</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/ball_user.php">คนที่ไม่อยู่หอพัก</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container">
    <h1 class="mb-4">รายชื่อผู้ใช้</h1>
    <div id="events" class="list-group"></div>
  </div>

  <script>
    async function fetchEvents() {
      try {
        const response = await fetch('/events');
        const events = await response.json();

        const groupedEvents = events.reduce((acc, event) => {
          if (!acc[event.user_id]) {
            acc[event.user_id] = {
              user_id: event.user_id,
              displayName: event.displayName,
              pictureUrl: event.pictureUrl,
              dms: [],
            };
          }
          acc[event.user_id].dms.push({
            dm: event.dm,
            latest: event.latest
          });
          return acc;
        }, {});

        const eventsDiv = document.getElementById('events');
        eventsDiv.innerHTML = '';

        for (const user of Object.values(groupedEvents)) {
          const status = await calculateStatus(user.user_id);

          const userDiv = document.createElement('div');
          userDiv.classList.add('list-group-item', 'list-group-item-action', 'd-flex', 'align-items-center');

          const dmMeaning = {
            "a0a3b32f6ed2": "ทางขึ้นบรรได",
            "a0a3b32fadf2": "ประตูทางเข้า"
          };

          const statusClass = status === "อยู่ในหอพัก" ? "status-in-dorm" : "status-out-dorm";

          userDiv.innerHTML = `
            <img src="${user.pictureUrl}" alt="รูปโปรไฟล์" class="profile-img">
            <div>
              <h5 class="mb-1">ชื่อผู้ใช้: ${user.displayName}</h5>
              <h5 class="mb-1">รหัสผู้ใช้: ${user.user_id}</h5>
              ${user.dms.map(dmInfo => `
                <p class="mb-1"><strong>DM:</strong> ${dmInfo.dm} (${dmMeaning[dmInfo.dm] || 'ไม่ทราบ'})</p>
                <p class="mb-1"><strong>เวลาที่ลงทะเบียน:</strong> ${new Date(dmInfo.latest).toLocaleString()}</p>
              `).join('')}
              <p class="mt-2 ${statusClass}">สถานะ: ${status}</p>
            </div>
          `;
          eventsDiv.appendChild(userDiv);
        }
      } catch (error) {
        console.error('เกิดข้อผิดพลาดในการดึงข้อมูลเหตุการณ์:', error);
      }
    }

    async function calculateStatus(userId) {
      try {
        const response = await fetch(`/calculate/${userId}`);
        if (response.ok) {
          const data = await response.json();

          // ถ้าสถานะเป็น "อยู่ในหอพัก" ให้เปลี่ยนเป็น "ไม่อยู่ในหอพัก"
          if (data.status === "อยู่ในหอพัก") {
            data.status = "ไม่อยู่ในหอพัก";
          } else if (data.status === "ไม่อยู่ในหอพัก") {
            data.status = "อยู่ในหอพัก";
          }

          return data.status;
        } else {
          return 'ไม่สามารถคำนวณสถานะได้';
        }
      } catch (error) {
        console.error('เกิดข้อผิดพลาดในการคำนวณสถานะ:', error);
        return 'ไม่สามารถคำนวณสถานะได้';
      }
    }

    fetchEvents();
  </script>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
