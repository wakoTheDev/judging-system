<?php
session_start();
require_once '../config/database.php';

$page_title = 'Judge Portal';
$mysqli = getDatabaseConnection();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $judge_name = trim($_POST['judge_name']);
    $password = $_POST['password'];

    if (!empty($judge_name) && !empty($password)) {
        $stmt = $mysqli->prepare("SELECT * FROM judges WHERE display_name = ? AND password = ?");
        $hashedPassword = md5($password);
        $stmt->bind_param("ss", $judge_name, $hashedPassword);
        $stmt->execute();
        $result = $stmt->get_result();
        $judge = $result->fetch_assoc();

        if ($judge) {
            $_SESSION['judge_id'] = $judge['id'];
            $_SESSION['judge_name'] = $judge['display_name'];
        } else {
            $error_message = 'Invalid judge name or password.';
        }

        $stmt->close();
    } else {
        $error_message = 'Please fill in all fields.';
    }
}

// Show login form if not logged in
if (!isset($_SESSION['judge_id'])) {
    include '../includes/header.php';
    ?>
    <div class="container">
        <div class="login-form">
            <h2>Judge Login</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="judge_name">Judge Name:</label>
                    <input type="text" id="judge_name" name="judge_name" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" value="1" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
    exit;
}

// Show judge dashboard
try {
    // Get participants
    $stmt = $mysqli->prepare("
        SELECT p.*, 
               COALESCE(AVG(s.points), 0) AS avg_score,
               COUNT(s.points) AS score_count
        FROM participants p
        LEFT JOIN scores s ON p.id = s.user_id
        GROUP BY p.id
        ORDER BY p.name
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $participants = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get recent scores
    $stmt = $mysqli->prepare("
        SELECT s.*, p.name AS participant_name, p.category
        FROM scores s
        JOIN participants p ON s.user_id = p.id
        WHERE s.judge_id = ?
        ORDER BY s.created_at DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $_SESSION['judge_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $recent_scores = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (mysqli_sql_exception $e) {
    $error_message = 'Database error: ' . $e->getMessage();
    $participants = [];
    $recent_scores = [];
}

include '../includes/header.php';
?>

<div class="container">
    <div class="judge-header">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['judge_name']); ?>!</h2>
        <div class="judge-actions">
            <a href="?logout=1" class="btn btn-secondary">Logout</a>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="participants-section">
            <h3>Participants to Score</h3>
            <div class="participants-list">
                <?php foreach ($participants as $participant): ?>
                    <div class="participant-card">
                        <div class="participant-info">
                            <h4><?php echo htmlspecialchars($participant['name']); ?></h4>
                            <p class="category"><?php echo htmlspecialchars($participant['category']); ?></p>
                            <p class="current-score">
                                Average Score: <?php echo number_format($participant['avg_score'], 1); ?>
                                (<?php echo $participant['score_count']; ?> scores)
                            </p>
                        </div>
                        <div class="participant-actions">
                            <a href="score_user.php?id=<?php echo $participant['id']; ?>" class="btn btn-primary">Score Participant</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($participants)): ?>
                    <p class="no-data">No participants found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="recent-scores-section">
            <h3>Your Recent Scores</h3>
            <div class="scores-list">
                <?php foreach ($recent_scores as $score): ?>
                    <div class="score-item">
                        <div class="score-details">
                            <strong><?php echo htmlspecialchars($score['participant_name']); ?></strong>
                            <span class="category"><?php echo htmlspecialchars($score['category']); ?></span>
                        </div>
                        <div class="score-value"><?php echo $score['score']; ?>/10</div>
                        <div class="score-date"><?php echo date('M j, H:i', strtotime($score['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($recent_scores)): ?>
                    <p class="no-data">No scores recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
<?php include '../css/judgeindex.css'; ?>
</style>

<?php include '../includes/footer.php'; ?>
