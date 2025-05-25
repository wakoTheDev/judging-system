<?php
/**
 * Public Scoreboard - Main Page
 * LAMP Stack Judging System
 */

require_once 'config/database.php';

$page_title = 'Public Scoreboard - Judging System';
$include_scoreboard_js = true;
$base_url = '';

// Get scoreboard data
function getScoreboardData() {
    $conn = getDatabaseConnection();
    
    $sql = "SELECT u.id, u.username, u.display_name, 
                   COALESCE(SUM(s.points), 0) as total_points,
                   COUNT(s.id) as judge_count
            FROM users u 
            LEFT JOIN scores s ON u.id = s.user_id 
            GROUP BY u.id, u.username, u.display_name 
            ORDER BY total_points DESC, u.display_name ASC";
    
    $result = executeQuery($conn, $sql);
    $scoreboard = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $scoreboard[] = $row;
        }
    }
    
    closeDatabaseConnection($conn);
    return $scoreboard;
}

$scoreboard_data = getScoreboardData();
?>

<?php include 'includes/header.php'; ?>

<div class="scoreboard-header">
    <h1>🏆 Live Scoreboard</h1>
    <p>Real-time scoring updates • Refreshes every 10 seconds</p>
</div>

<div class="card scoreboard-table">
    <?php if (empty($scoreboard_data)): ?>
        <div class="message error">
            <h3>No participants found</h3>
            <p>No users have been registered yet. Please contact the administrator to add participants.</p>
        </div>
    <?php else: ?>
        <table class="table" id="scoreboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Participant Name</th>
                    <th>Username</th>
                    <th>Total Points</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scoreboard_data as $index => $user): ?>
                    <?php 
                    $rank = $index + 1;
                    $rank_class = '';
                    if ($rank == 1) $rank_class = 'rank-1';
                    elseif ($rank == 2) $rank_class = 'rank-2';
                    elseif ($rank == 3) $rank_class = 'rank-3';
                    ?>
                    <tr class="<?php echo $rank_class; ?>">
                        <td><strong>#<?php echo $rank; ?></strong></td>
                        <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                        <td>@<?php echo htmlspecialchars($user['username']); ?></td>
                        <td><span class="points-badge"><?php echo $user['total_points']; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="text-align: center; margin-top: 2rem;">
            <p style="color: #666; font-size: 0.9rem;">
                📊 Total Participants: <?php echo count($scoreboard_data); ?> | 
                👨‍⚖️ Active Judges: 
                <?php
                $conn = getDatabaseConnection();
                $judge_count = executeQuery($conn, "SELECT COUNT(*) as count FROM judges");
                $judges = $judge_count ? $judge_count->fetch_assoc()['count'] : 0;
                echo $judges;
                closeDatabaseConnection($conn);
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<div class="grid grid-3">
    <div class="card">
        <h3>🎯 For Judges</h3>
        <p>Access the judge portal to score participants and manage your evaluations.</p>
        <a href="judge/" class="btn btn-primary">Judge Portal</a>
    </div>
    
    <div class="card">
        <h3>⚙️ Administration</h3>
        <p>Manage judges, participants, and system settings through the admin panel.</p>
        <a href="admin/" class="btn btn-secondary">Admin Panel</a>
    </div>
    
    <div class="card">
        <h3>📈 Live Updates</h3>
        <p>This scoreboard updates automatically every 10 seconds to show the latest scores.</p>
        <div style="margin-top: 1rem;">
            <small style="color: #666;">
                <strong>Features:</strong><br>
                • Real-time score updates<br>
                • Responsive design<br>
                • Ranking highlights<br>
                • Auto-refresh technology
            </small>
        </div>
    </div>
</div>

<div class="container">
    <h3>📋 How It Works</h3>
    <div class="grid grid-2">
        <div>
            <h4>1. Judge Registration</h4>
            <p>Administrators add qualified judges to the system through the admin panel. Each judge gets a unique identifier.</p>
        </div>
        <div>
            <h4>2. Participant Scoring</h4>
            <p>Judges access their portal to evaluate participants and assign points (1-100) based on established criteria.</p>
        </div>
        <div>
            <h4>3. Live Aggregation</h4>
            <p>Scores from all judges are automatically combined to calculate total points for each participant.</p>
        </div>
        <div>
            <h4>4. Real-time Display</h4>
            <p>This public scoreboard updates every 10 seconds, showing current rankings and highlighting top performers.</p>
        </div>
    </div>
</div>

<div class="container">
    <h3>🔧 Technical Implementation</h3>
    <p>This judging system is built using the <strong>LAMP Stack</strong>:</p>
    <ul style="list-style: none; padding: 0;">
        <li>🐧 <strong>Linux:</strong> Server operating system</li>
        <li>🌐 <strong>Apache:</strong> Web server for hosting</li>
        <li>🗄️ <strong>MySQL:</strong> Database for storing judges, users, and scores</li>
        <li>🐘 <strong>PHP:</strong> Server-side scripting for dynamic content</li>
        <li>⚡ <strong>JavaScript:</strong> Client-side updates and interactivity</li>
    </ul>
    
    <div style="margin-top: 1rem; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 8px;">
        <strong>🔐 Security Features:</strong> Prepared statements, input validation, XSS protection, error handling
    </div>
</div>

<?php include 'includes/footer.php'; ?>