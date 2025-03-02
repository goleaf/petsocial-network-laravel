<div>
    <h2>Friend Analytics</h2>
    <div class="analytics-container">
        <div class="stat-card">
            <h3>Total Friends</h3>
            <div class="stat-value">{{ $totalFriends }}</div>
        </div>
        
        <div class="stat-card">
            <h3>New Friends (Last 30 Days)</h3>
            <div class="stat-value">{{ $newFriends }}</div>
        </div>
        
        <div class="stat-card">
            <h3>Friend Requests</h3>
            <div class="stat-value">{{ $pendingRequests }}</div>
        </div>
    </div>
    
    <div class="chart-container">
        <h3>Friend Activity Over Time</h3>
        <div class="chart">
            <!-- Chart would go here -->
        </div>
    </div>
</div>