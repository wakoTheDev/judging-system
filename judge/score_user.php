<?php
session_start();
require_once '../config/database.php';

$page_title = 'Score Participant';

// Check if judge is logged in
if (!isset($_SESSION['judge_id'])) {
    header('Location: index.php');
    exit;
}

// Get participant ID
$participant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$participant_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get participant details
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$participant_id]);
    $participant = $stmt->fetch();
    
    if (!$participant) {
        $error_message = 'Participant not found';
        header('Location: index.php');
        exit;
    }
    
    // Check if judge has already scored this participant
    $stmt = $pdo->prepare("SELECT * FROM scores WHERE participant_id = ? AND judge_id = ?");
    $stmt->execute([$participant_id, $_SESSION['judge_id']]);
    $existing_score = $stmt->fetch();
    
    // Get participant's current scores
    $stmt = $pdo->prepare("
        SELECT s.*, j.judge_name 
        FROM scores s 
        JOIN judges j ON s.judge_id = j.id 
        WHERE s.participant_id = ? 
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$participant_id]);
    $all_scores = $stmt->fetchAll();
    
    // Calculate average score
    $avg_score = 0;
    if (count($all_scores) > 0) {
        $total = array_sum(array_column($all_scores, 'score'));
        $avg_score = $total / count($all_scores);
    }
    
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_POST && isset($_POST['submit_score'])) {
    $score = (int)$_POST['score'];
    $comments = trim($_POST['comments']);
    
    if ($score >= 1 && $score <= 10) {
        try {
            if ($existing_score) {
                // Update existing score
                $stmt = $pdo->prepare("
                    UPDATE scores 
                    SET score = ?, comments = ?, updated_at = NOW() 
                    WHERE participant_id = ? AND judge_id = ?
                ");
                $stmt->execute([$score, $comments, $participant_id, $_SESSION['judge_id']]);
                $success_message = 'Score updated successfully!';
            } else {
                // Insert new score
                $stmt = $pdo->prepare("
                    INSERT INTO scores (participant_id, judge_id, score, comments, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$participant_id, $_SESSION['judge_id'], $score, $comments]);
                $success_message = 'Score submitted successfully!';
            }
            
            // Refresh the page to show updated data
            header('Location: score_user.php?id=' . $participant_id . '&success=1');
            exit;
            
        } catch (PDOException $e) {
            $error_message = 'Error saving score: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Please select a valid score (1-10)';
    }
}

// Check for success message
if (isset($_GET['success'])) {
    $success_message = $existing_score ? 'Score updated successfully!' : 'Score submitted successfully!';
}

include '../includes/header.php';
?>

<div class="container">
    <div class="scoring-header">
        <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
        <h2>Score Participant</h2>
    </div>
    
    <div class="scoring-layout">
        <div class="participant-details">
            <div class="participant-card">
                <h3><?php echo htmlspecialchars($participant['name']); ?></h3>
                <div class="participant-meta">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($participant['category']); ?></p>
                    <p><strong>Current Average:</strong> <?php echo number_format($avg_score, 1); ?>/10</p>
                    <p><strong>Total Scores:</strong> <?php echo count($all_scores); ?></p>
                </div>
                
                <?php if ($participant['description']): ?>
                    <div class="participant-description">
                        <h4>Description:</h4>
                        <p><?php echo nl2br(htmlspecialchars($participant['description'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="existing-scores">
                <h4>Previous Scores</h4>
                <?php if (count($all_scores) > 0): ?>
                    <div class="scores-list">
                        <?php foreach ($all_scores as $score): ?>
                            <div class="score-item <?php echo ($score['judge_id'] == $_SESSION['judge_id']) ? 'own-score' : ''; ?>">
                                <div class="score-header">
                                    <strong><?php echo htmlspecialchars($score['judge_name']); ?></strong>
                                    <span class="score-value"><?php echo $score['score']; ?>/10</span>
                                </div>
                                <?php if ($score['comments']): ?>
                                    <div class="score-comments">
                                        <?php echo nl2br(htmlspecialchars($score['comments'])); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="score-date">
                                    <?php echo date('M j, Y H:i', strtotime($score['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-scores">No scores recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="scoring-form">
            <div class="form-card">
                <h3><?php echo $existing_score ? 'Update Your Score' : 'Submit Your Score'; ?></h3>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Score (1-10):</label>
                        <div class="score-buttons">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <input type="radio" id="score_<?php echo $i; ?>" name="score" value="<?php echo $i; ?>"
                                       <?php echo ($existing_score && $existing_score['score'] == $i) ? 'checked' : ''; ?>>
                                <label for="score_<?php echo $i; ?>" class="score-btn"><?php echo $i; ?></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comments">Comments (Optional):</label>
                        <textarea id="comments" name="comments" rows="4" placeholder="Add your feedback here..."><?php echo $existing_score ? htmlspecialchars($existing_score['comments']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="submit_score" class="btn btn-primary">
                            <?php echo $existing_score ? 'Update Score' : 'Submit Score'; ?>
                        </button>
                        
                        <?php if ($existing_score): ?>
                            <p class="last-scored">
                                Last scored: <?php echo date('M j, Y H:i', strtotime($existing_score['created_at'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
<?php include '../css/score.css'; ?>
</style>

<?php include '../includes/footer.php'; ?>