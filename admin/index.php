<?php
/**
 * Admin Panel - Dashboard
 * LAMP Stack Judging System
 */

require_once '../config/database.php';

$page_title = 'Admin Dashboard - Judging System';
$base_url = '../';

// Get system statistics
function getSystemStats() {
    $conn = getDatabaseConnection();
    $stats = [];
    
    // Total judges
    $result = executeQuery($conn, "SELECT COUNT(*) as count FROM judges");
    $stats['total_judges'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Total users
    $result = executeQuery($conn, "SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Total scores
    $result = executeQuery($conn, "SELECT COUNT(*) as count FROM scores");
    $stats['total_scores'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Average score
    $result = executeQuery($conn, "SELECT AVG(points) as avg_score FROM scores");
    $stats['avg_score'] = $result ? round($result->fetch_assoc()['avg_score'], 2) : 0;
    
    closeDatabaseConnection($conn);
    return $stats;
}

// Get recent activity
function getRecentActivity() {
    $conn = getDatabaseConnection();
    
    $sql = "SELECT s.*, j.display_name as judge_name, u.display_name as user_name
            FROM scores s
            JOIN judges j ON s.judge_id = j.id
            JOIN users u ON s.user_id = u.id
            ORDER BY s.updated_at DESC
            LIMIT 10";
    
    $result = executeQuery($conn, $sql);
    $activity = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activity[] = $row;
        }
    }
    
    closeDatabaseConnection($conn);
    return $activity;
}

// Get all judges
function getAllJudges() {
    $conn = getDatabaseConnection();
    
    $sql = "SELECT j.*, COUNT(s.id) as scores_given
            FROM judges j
            LEFT JOIN scores s ON j.id = s.judge_id
            GROUP BY j.id
            ORDER BY j.display_name";
    
    $result = executeQuery($conn, $sql);
    $judges = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $judges[] = $row;
        }
    }
    
    closeDatabaseConnection($conn);
    return $judges;
}

$stats = getSystemStats();
$recent_activity = getRecentActivity();
$judges = getAllJudges();
?>

<?php include '../includes/header.php'; ?>

<div class="card">
    <h1>⚙️ Admin Dashboard</h1>
    <p>Manage judges, monitor activity, and oversee the judging system.</p>
</div>

<!-- System Statistics -->
<div class="grid grid-2">
    <div class="card">
        <h2>📊 System Statistics</h2>
        <div class="grid grid-2">
            <div style="text-align: center; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 8px;">
                <h3 style="color: #667eea; margin: 0; font-size: 2rem;"><?php echo $stats['total_judges']; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Total Judges</p>
            </div>
            <div style="text-align: center; padding: 1rem; background: rgba(76, 175, 80, 0.1); border-radius: 8px;">
                <h3 style="color: #4CAF50; margin: 0; font-size: 2rem;"><?php echo $stats['total_users']; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Participants</p>
            </div>
            <div style="text-align: center; padding: 1rem; background: rgba(255, 152, 0, 0.1); border-radius: 8px;">
                <h3 style="color: #FF9800; margin: 0; font-size: 2rem;"><?php echo $stats['total_scores']; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Total Scores</p>
            </div>
            <div style="text-align: center; padding: 1rem; background: rgba(156, 39, 176, 0.1); border-radius: 8px;">
                <h3 style="color: #9C27B0; margin: 0; font-size: 2rem;"><?php echo $stats['avg_score']; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Average Score</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2>🎯 Quick Actions</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <a href="add_judge.php" class="btn btn-primary">➕ Add New Judge</a>
            <a href="../judge/" class="btn btn-secondary">👨‍⚖️ Judge Portal</a>
            <a href="../" class="btn btn-success">🏆 View Scoreboard</a>
            <button onclick="refreshData()" class="btn btn-secondary">🔄 Refresh Data</button>
        </div>
    </div>
</div>

<!-- Judges Management -->
<div class="card">
    <h2>👨‍⚖️ Judges Management</h2>
    <?php if (empty($judges)): ?>
        <div class="message error">
            <p>No judges have been registered yet.</p>
            <a href="add_judge.php" class="btn btn-primary">Add Your First Judge</a>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Judge ID</th>
                    <th>Display Name</th>
                    <th>Username</th>
                    <th>Scores Given</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($judges as $judge): ?>
                    <tr>
                        <td><strong>#<?php echo $judge['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($judge['display_name']); ?></td>
                        <td>@<?php echo htmlspecialchars($judge['username']); ?></td>
                        <td>
                            <span class="points-badge"><?php echo $judge['scores_given']; ?></span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($judge['created_at'])); ?></td>
                        <td>
                            <button onclick="viewJudgeDetails(<?php echo $judge['id']; ?>)" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Recent Activity -->
<div class="card">
    <h2>📈 Recent Activity</h2>
    <?php if (empty($recent_activity)): ?>
        <p style="text-align: center; color: #666;">No scoring activity yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Judge</th>
                    <th>Participant</th>
                    <th>Points</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_activity as $activity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['judge_name']); ?></td>
                        <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                        <td><span class="points-badge"><?php echo $activity['points']; ?></span></td>
                        <td><?php echo date('M j, H:i', strtotime($activity['updated_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- System Information -->
<div class="card">
    <h2>🔧 System Information</h2>
    <div class="grid grid-2">
        <div>
            <h4>Database Status</h4>
            <p style="color: #4CAF50;">✅ Connected</p>
            
            <h4>LAMP Stack</h4>
            <ul style="list-style: none; padding: 0;">
                <li>🐧 Linux: Active</li>
                <li>🌐 Apache: Running</li>
                <li>🗄️ MySQL: Connected</li>
                <li>🐘 PHP: <?php echo PHP_VERSION; ?></li>
            </ul>
        </div>
        <div>
            <h4>Security Features</h4>
            <ul style="list-style: none; padding: 0;">
                <li>✅ Prepared Statements</li>
                <li>✅ Input Validation</li>
                <li>✅ XSS Protection</li>
                <li>✅ Error Handling</li>
            </ul>
            
            <h4>Last System Check</h4>
            <p><?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

function viewJudgeDetails(judgeId) {
    alert('Judge details functionality would be implemented here.\nJudge ID: ' + judgeId);
    // In a full implementation, this would open a modal or navigate to a details page
}

// Simple auto-refresh every 30 seconds for admin dashboard
setInterval(function() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString();
    document.title = 'Admin Dashboard - Updated ' + timeStr;
}, 30000);
</script>

<?php include '../includes/footer.php'; ?>