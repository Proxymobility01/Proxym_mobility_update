<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS System</title>
    <style>
        :root {
            --primary: #DCDB32;
            --secondary: #101010;
            --tertiary: #F3F3F3;
            --background: #ffffff;
            --text: #101010;
            --sidebar: #F8F8F8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text);
        }

        .header {
            background-color: var(--secondary);
            color: var(--background);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo h1 {
            margin-left: 10px;
            font-size: 1.5rem;
            color: var(--background);
        }

        .logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--secondary);
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info span {
            margin-right: 15px;
        }

        .main-container {
            display: flex;
            height: calc(100vh - 60px);
        }

        .sidebar {
            width: 250px;
            background-color: var(--sidebar);
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar-item {
            padding: 15px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
        }

        .sidebar-item.active {
            background-color: var(--primary);
            color: var(--secondary);
            font-weight: bold;
        }

        .sidebar-item:hover:not(.active) {
            background-color: rgba(220, 219, 50, 0.1);
        }

        .sidebar-item i {
            margin-right: 10px;
        }

        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            border-bottom-color: var(--primary);
            font-weight: bold;
        }

        .search-bar {
            display: flex;
            margin-bottom: 20px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }

        .search-bar button {
            padding: 10px 15px;
            background-color: var(--primary);
            border: none;
            color: var(--secondary);
            font-weight: bold;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        .device-info {
            background-color: var(--tertiary);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .battery-status {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .battery-box {
            flex: 1;
            background-color: var(--tertiary);
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .soc-display {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }

        .soc-outer {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 6px solid #eee;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: conic-gradient(var(--primary) calc(var(--percentage) * 3.6deg), #eee 0deg);
        }

        .soc-value {
            font-size: 2rem;
            font-weight: bold;
        }


        @keyframes rotateCW {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes rotateCCW {
  from { transform: rotate(0deg); }
  to { transform: rotate(-360deg); }
}

.soc-outer.rotate-charging {
  animation: rotateCW 2s linear infinite;
}

.soc-outer.rotate-discharging {
  animation: rotateCCW 2s linear infinite;
}


        .battery-params {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .param-box {
            flex: 1;
            min-width: 200px;
            background-color: var(--tertiary);
            border-radius: 4px;
            padding: 15px;
        }

        .param-title {
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        .param-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--secondary);
        }

        .gauge-container {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            position: relative;
        }

        .gauge {
            width: 100%;
            height: 100%;
        }

        .gauge-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.2rem;
            font-weight: bold;
        }

        .gauge-unit {
            position: absolute;
            top: 65%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8rem;
        }

        .chart-container {
            background-color: var(--tertiary);
            border-radius: 4px;
            padding: 20px;
            height: 700px;
            margin-bottom: 20px;
        }

        .cell-voltages {
            background-color: var(--tertiary);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .cell-title {
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }

        .cell-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
        }

        .cell {
            background-color: var(--background);
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .cell.warning {
            background-color:rgb(102, 255, 217);
        }

        .cell.danger {
            background-color:rgba(255, 107, 107, 0.62);
            color: white;
        }

        .cell-number {
            font-size: 0.8rem;
            color: #777;
        }

        .cell-voltage {
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
            }

            .battery-status, .battery-params {
                flex-direction: column;
            }

            .cell-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
<div class="header">
        <div class="logo">
            <div class="logo-circle">BMS</div>
            <h1>Battery Management System</h1>
        </div>
        <div class="user-info">
            <span>Balance: 0.00 (Coins)</span>
            <span id="header-battery-id">ID: 613881628046</span>
        </div>
    </div>

    <div class="main-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-item active" data-role="home">
                <i>🏠</i> Liste des Batteries
            </div>
            <!-- Batteries will be dynamically loaded here -->
        </div>

        <div class="content">
            <div class="tabs">
                <div class="tab active">Monitor</div>
                <div class="tab">Control</div>
                <div class="tab">Historical Data</div>
            </div>

            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Enter device ID" value="613881628046">
                <button id="search-btn">Search</button>
                <button id="read-btn" style="margin-left: 10px; background-color: var(--secondary); color: var(--background);">Read</button>
            </div>

            <div class="device-info">
                <div id="device-id">ID: 613881628046</div>
                <div id="device-status">Device online</div>
                <div id="device-update-time">Update time: 2025-02-25 07:10:02</div>
            </div>

            <div class="battery-status">
                <div class="battery-box">
                    <h3>SOC</h3>
                    <div class="soc-display">
                        <div class="soc-outer" id="soc-circle" style="--percentage: 100;">
                            <div class="soc-value" id="soc-value">100%</div>
                        </div>
                    </div>
                    <div>
                        <div><strong>Battery status:</strong> <span id="battery-status" style="color: #2980b9;">Discharging</span></div>
                        <div><strong>SOE:</strong> <span id="soe">25.8 Ah</span></div>
                        <div><strong>Number of cycles:</strong> <span id="cycles">35</span> Time</div>
                    </div>
                </div>

                <div class="battery-box">
                    <h3>Voltage</h3>
                    <div class="gauge-container">
                        <svg class="gauge" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#EEEEEE" stroke-width="12" />
                            <circle id="voltage-gauge" cx="60" cy="60" r="54" fill="none" stroke="#2980b9" stroke-width="12" stroke-dasharray="339" stroke-dashoffset="110" transform="rotate(-90 60 60)" />
                        </svg>
                        <div class="gauge-value" id="voltage-value">78.5</div>
                        <div class="gauge-unit">V</div>
                    </div>
                </div>

                <div class="battery-box">
                    <h3>Current</h3>
                    <div class="gauge-container">
                        <svg class="gauge" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#EEEEEE" stroke-width="12" />
                            <circle id="current-gauge" cx="60" cy="60" r="54" fill="none" stroke="#27ae60" stroke-width="12" stroke-dasharray="339" stroke-dashoffset="300" transform="rotate(-90 60 60)" />
                        </svg>
                        <div class="gauge-value" id="current-value">0.441</div>
                        <div class="gauge-unit">A</div>
                    </div>
                </div>

                <div class="battery-box">
                    <h3>Power</h3>
                    <div class="gauge-container">
                        <svg class="gauge" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#EEEEEE" stroke-width="12" />
                            <circle id="power-gauge" cx="60" cy="60" r="54" fill="none" stroke="#f39c12" stroke-width="12" stroke-dasharray="339" stroke-dashoffset="320" transform="rotate(-90 60 60)" />
                        </svg>
                        <div class="gauge-value" id="power-value">0.035</div>
                        <div class="gauge-unit">kW</div>
                    </div>
                </div>
            </div>

            <div class="battery-params">
                <div class="param-box">
                    <div class="param-title">
                        <span>Battery type</span>
                        <span id="battery-type">LFP</span>
                    </div>
                    <div class="param-title">
                        <span>Strings</span>
                        <span id="battery-strings">24 strand</span>
                    </div>
                    <div class="param-title">
                        <span>Nominal Capacity</span>
                        <span id="nominal-capacity">50Ah</span>
                    </div>
                    <div class="param-title">
                        <span>Rated Current</span>
                        <span id="rated-current">80A</span>
                    </div>
                </div>

                <div class="param-box">
                    <div class="param-title">
                        <span>Charge Switch</span>
                        <span id="charge-switch" style="color: #27ae60;">Open</span>
                    </div>
                    <div class="param-title">
                        <span>Discharge Switch</span>
                        <span id="discharge-switch" style="color: #27ae60;">Open</span>
                    </div>
                    <div class="param-title">
                        <span>Charge current</span>
                        <span id="charge-current">0A</span>
                    </div>
                    <div class="param-title">
                        <span>Discharge current</span>
                        <span id="discharge-current">0.441A</span>
                    </div>
                </div>

                <div class="param-box">
                    <div class="param-title">
                        <span>Battery Temp1</span>
                        <span id="battery-temp1">30.6°C</span>
                    </div>
                    <div class="param-title">
                        <span>Battery Temp2</span>
                        <span id="battery-temp2">30.7°C</span>
                    </div>
                    <div class="param-title">
                        <span>Battery Temp3</span>
                        <span id="battery-temp3">32.1°C</span>
                    </div>
                    <div class="param-title">
                        <span>Battery Temp4</span>
                        <span id="battery-temp4">31.7°C</span>
                    </div>
                </div>
            </div>

            <div class="cell-voltages">
                <div class="cell-title">
                    <span>Battery cell string voltage</span>
                    <div>
                        <span>Max volt: <span id="max-volt">3.273</span> V</span>
                        <span style="margin-left: 20px;">Min volt: <span id="min-volt">3.268</span> V</span>
                        <span style="margin-left: 20px;">Max difference: <span id="max-diff">0.004</span> V</span>
                    </div>
                </div>
                <div class="cell-grid" id="cell-grid">
                    <!-- Cell voltages will be dynamically loaded here -->
                </div>
            </div>

            <div class="chart-container">
                <canvas id="batteryChart"></canvas>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Variable pour stocker la liste des batteries (sera chargée depuis l'API)
        let batteryList = [];
        
        // Current selected battery
        let currentBatteryId = "613881628046";
        let batteryChart = null;

        // Initialize the app
        document.addEventListener('DOMContentLoaded', function() {
            // Charger la liste des batteries depuis l'API
            fetchBatteryList().then(() => {
                initializeSidebar();
                setupEventListeners();
                loadBatteryData(currentBatteryId);
                // Set interval to periodically update data
                setInterval(() => loadBatteryData(currentBatteryId, true), 5000);
            }).catch(error => {
                console.error('Erreur lors du chargement des batteries:', error);
            });
        });

        // Initialize the sidebar with battery list
        function initializeSidebar() {
            const sidebar = document.getElementById('sidebar');
            // Clear sidebar except the home item
            const homeItem = sidebar.querySelector('[data-role="home"]');
            sidebar.innerHTML = '';
            sidebar.appendChild(homeItem);
            
            // Add battery items
            batteryList.forEach(battery => {
                const item = document.createElement('div');
                item.className = `sidebar-item ${battery.id === currentBatteryId ? 'active' : ''}`;
                item.setAttribute('data-battery-id', battery.id);
                item.textContent = ` ${battery.id}`;
                sidebar.appendChild(item);
            });
        }

        // Set up event listeners
        function setupEventListeners() {
            // Sidebar item clicks
            document.getElementById('sidebar').addEventListener('click', function(e) {
                const item = e.target.closest('.sidebar-item');
                if (!item) return;
                
                // Set all items as inactive
                document.querySelectorAll('.sidebar-item').forEach(el => {
                    el.classList.remove('active');
                });
                
                // Set clicked item as active
                item.classList.add('active');
                
                // If it's a battery item, load its data
                const batteryId = item.getAttribute('data-battery-id');
                if (batteryId) {
                    currentBatteryId = batteryId;
                    document.getElementById('search-input').value = batteryId;
                    loadBatteryData(batteryId);
                }
            });
            
            // Search button click
            document.getElementById('search-btn').addEventListener('click', function() {
                const batteryId = document.getElementById('search-input').value.trim();
                // Check if battery exists
                const exists = batteryList.some(b => b.id === batteryId);
                if (exists) {
                    currentBatteryId = batteryId;
                    loadBatteryData(batteryId);
                    // Update sidebar active state
                    document.querySelectorAll('.sidebar-item').forEach(el => {
                        el.classList.remove('active');
                        if (el.getAttribute('data-battery-id') === batteryId) {
                            el.classList.add('active');
                        }
                    });
                } else {
                    alert('Battery ID not found!');
                }
            });
            
            // Read button click (same as search but could have different functionality)
            document.getElementById('read-btn').addEventListener('click', function() {
                const batteryId = document.getElementById('search-input').value.trim();
                if (batteryList.some(b => b.id === batteryId)) {
                    currentBatteryId = batteryId;
                    loadBatteryData(batteryId);
                } else {
                    alert('Battery ID not found!');
                }
            });
            
            // Tab clicks
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelector('.tab.active').classList.remove('active');
                    this.classList.add('active');
                });
            });
        }

        // Load battery data from API
        function loadBatteryData(batteryId, isUpdate = false) {
            // Real AJAX call to the backend
            fetchBatteryData(batteryId).then(data => {
                updateBatteryUI(data);
            }).catch(error => {
                console.error('Error loading battery data:', error);
            });
        }

        // Update the UI with battery data
        function updateBatteryUI(data) {
            // Update header and device info
            document.getElementById('header-battery-id').textContent = `ID: ${data.id}`;
            document.getElementById('device-id').textContent = `ID: ${data.id}`;
            document.getElementById('device-status').textContent = `Device ${data.status}`;
            document.getElementById('device-update-time').textContent = `Update time: ${data.updatedAt}`;
            
            // Update SOC circle
            const socCircle = document.getElementById('soc-circle');
            socCircle.style.setProperty('--percentage', data.soc);
            document.getElementById('soc-value').textContent = `${data.soc}%`;
            
            // Update battery status info
            document.getElementById('battery-status').textContent = data.batteryStatus;
            document.getElementById('soe').textContent = data.soe;
            document.getElementById('cycles').textContent = data.cycles;
            
            // Update gauges
            // Voltage gauge (scale to 0-100% of expected range 0-100V)
            const voltageOffset = 339 - (339 * (data.voltage / 100));
            document.getElementById('voltage-gauge').setAttribute('stroke-dashoffset', voltageOffset);
            document.getElementById('voltage-value').textContent = data.voltage;
            
            // Current gauge (scale to 0-100% of expected range 0-1A)
            const currentOffset = 339 - (339 * (data.current / 1));
            document.getElementById('current-gauge').setAttribute('stroke-dashoffset', currentOffset);
            document.getElementById('current-value').textContent = data.current;
            
            // Power gauge (scale to 0-100% of expected range 0-0.1kW)
            const powerOffset = 339 - (339 * (data.power / 0.1));
            document.getElementById('power-gauge').setAttribute('stroke-dashoffset', powerOffset);
            document.getElementById('power-value').textContent = data.power;
            
            // Update battery parameters
            document.getElementById('battery-type').textContent = data.type;
            document.getElementById('battery-strings').textContent = data.strings;
            document.getElementById('nominal-capacity').textContent = data.nominalCapacity;
            document.getElementById('rated-current').textContent = data.ratedCurrent;
            
            document.getElementById('charge-switch').textContent = data.chargeSwitch;
            document.getElementById('discharge-switch').textContent = data.dischargeSwitch;
            document.getElementById('charge-current').textContent = data.chargeCurrent;
            document.getElementById('discharge-current').textContent = data.dischargeCurrent;
            
            document.getElementById('battery-temp1').textContent = `${data.temps[0].toFixed(1)}°C`;
            document.getElementById('battery-temp2').textContent = `${data.temps[1].toFixed(1)}°C`;
            document.getElementById('battery-temp3').textContent = `${data.temps[2].toFixed(1)}°C`;
            document.getElementById('battery-temp4').textContent = `${data.temps[3].toFixed(1)}°C`;
            
            // Update cell voltage stats
            document.getElementById('max-volt').textContent = data.maxVolt;
            document.getElementById('min-volt').textContent = data.minVolt;
            document.getElementById('max-diff').textContent = data.maxDiff;
            
            // Update cell voltage grid
            const cellGrid = document.getElementById('cell-grid');
            cellGrid.innerHTML = '';
            
            data.cellVoltages.forEach(cell => {
                const cellElement = document.createElement('div');
                cellElement.className = `cell ${cell.status}`;
                cellElement.innerHTML = `
                    <div class="cell-number">#${cell.number}</div>
                    <div class="cell-voltage">${cell.voltage}V</div>
                `;
                cellGrid.appendChild(cellElement);
            });
            
            // Update chart
            updateBatteryChart(data.chartData);
        }

        // Update the battery chart
        function updateBatteryChart(chartData) {
            const ctx = document.getElementById('batteryChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (batteryChart) {
                batteryChart.destroy();
            }
            
            // Create new chart
            batteryChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.timeLabels,
                    datasets: [
                        {
                            label: 'Battery Voltage (V)',
                            data: chartData.batteryVoltage,
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Remaining Capacity (%)',
                            data: chartData.remainingCapacity,
                            borderColor: '#DCDB32',
                            backgroundColor: 'rgba(220, 219, 50, 0.1)',
                            tension: 0.3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Discharge Current (A)',
                            data: chartData.dischargeCurrent,
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.3,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'Temperature (°C)',
                            data: chartData.temperature,
                            borderColor: '#f39c12',
                            backgroundColor: 'rgba(243, 156, 18, 0.1)',
                            tension: 0.3,
                            yAxisID: 'y2'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Voltage (V) / Capacity (%)'
                            },
                            min: 0,
                            max: 100
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Current (A)'
                            },
                            min: 0,
                            max: 1,
                            grid: {
                                drawOnChartArea: false,
                            }
                        },
                        y2: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Temperature (°C)'
                            },
                            min: 25,
                            max: 40,
                            grid: {
                                drawOnChartArea: false,
                            }
                        }
                    }
                }
            });
        }

        // AJAX functions for API communication
        function fetchBatteryData(batteryId) {
            return fetch(`/api/bms/battery/${batteryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                });
        }

        function fetchBatteryList() {
            return fetch('/api/bms/batteries')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    batteryList = data;
                    return data;
                });
        }

        // Helper functions pour les graphiques restent utiles même avec l'API réelle
        function getRandomValue(min, max) {
            return Math.random() * (max - min) + min;
        }

        function getRandomInt(min, max) {
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        function generateRandomDataArray(min, max, length) {
            const arr = [];
            for (let i = 0; i < length; i++) {
                arr.push(parseFloat(getRandomValue(min, max).toFixed(1)));
            }
            return arr;
        }

        function generateDecreasingArray(start, end, length) {
            const arr = [];
            const step = (start - end) / (length - 1);
            for (let i = 0; i < length; i++) {
                arr.push(parseFloat((start - step * i).toFixed(1)));
            }
            return arr;
        }

        function generateIncreasingArray(start, end, length) {
            const arr = [];
            const step = (end - start) / (length - 1);
            for (let i = 0; i < length; i++) {
                arr.push(parseFloat((start + step * i).toFixed(3)));
            }
            return arr;
        }
    </script>
</body>
</html>