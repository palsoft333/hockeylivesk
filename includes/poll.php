<?php
require_once 'db.php';

function fetchPoll($poll_id) {
    global $link;
    $stmt = $link->prepare("SELECT * FROM polls WHERE id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function fetchPollVotes($poll_id) {
    global $link;
    $stmt = $link->prepare("SELECT option_selected, COUNT(*) AS votes FROM poll_votes WHERE poll_id = ? GROUP BY option_selected");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function hasUserVoted($poll_id, $user_id) {
    global $link;
    $stmt = $link->prepare("SELECT * FROM poll_votes WHERE poll_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $poll_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['logged'])) {
    $poll_id = $_POST['poll_id'];
    $selected_option = $_POST['option'];
    $user_id = $_SESSION['logged'];

    if (!hasUserVoted($poll_id, $user_id)) {
        $stmt = $link->prepare("INSERT INTO poll_votes (poll_id, user_id, option_selected) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $poll_id, $user_id, $selected_option);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

function renderPoll($poll_id) {
    global $link;

    // Načítanie ankety a hlasov
    $poll = fetchPoll($poll_id);
    if (!$poll) {
        return "<p>Anketa nenájdená.</p>";
    }

    $options = json_decode($poll['options'], true);
    $votes = fetchPollVotes($poll_id);

    $total_votes = array_sum(array_column($votes, 'votes'));
    $user_voted = isset($_SESSION['logged']) ? hasUserVoted($poll_id, $_SESSION['logged']) : true;

    // Generovanie obsahu
    $content = '<div class="poll-container">';
    $content .= '<h4>' . htmlspecialchars($poll['question']) . '</h4>';

    if (!$user_voted && isset($_SESSION['logged'])) {
        $content .= '<form method="POST">';
        $content .= '<input type="hidden" name="poll_id" value="' . $poll_id . '">';
        foreach ($options as $option) {
            $content .= '<div class="custom-control custom-radio">';
            $content .= '<input class="custom-control-input" type="radio" name="option" value="' . htmlspecialchars($option) . '" id="' . htmlspecialchars($option) . '">';
            $content .= '<label class="custom-control-label" for="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</label>';
            $content .= '</div>';
        }
        $content .= '<button type="submit" class="btn btn-primary mt-3">Hlasovať</button>';
        $content .= '</form>';
    } else {
        $content .= '<div class="poll-results">';
        foreach ($options as $option) {
            $option_votes = 0;
            foreach ($votes as $vote) {
                if ($vote['option_selected'] === $option) {
                    $option_votes = $vote['votes'];
                    break;
                }
            }
            $percentage = $total_votes ? ($option_votes / $total_votes) * 100 : 0;
            $content .= '<div class="mb-2">';
            $content .= '<strong>' . htmlspecialchars($option) . ':</strong>';
            $content .= '<div class="progress">';
            $content .= '<div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%;" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">' . round($percentage, 2) . '%</div>';
            $content .= '</div>';
            $content .= '</div>';
        }
        $content .= '<p class="text-muted">Počet hlasov: ' . $total_votes . '</p>';
        $content .= '</div>';
    }

    $content .= '</div>';
    return $content;
}