<?php
session_start();

if (!isset($_SESSION['board'])) {
    resetGame();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($_GET['action'] === 'play') {
        play($data['index'], $data['player']);
    }
} else {
    if ($_GET['action'] === 'getState') {
        echo json_encode([
            'board' => $_SESSION['board'],
            'currentPlayer' => $_SESSION['currentPlayer'],
            'gameActive' => $_SESSION['gameActive']
        ]);
    } elseif ($_GET['action'] === 'reset') {
        resetGame();
        echo json_encode(['success' => true]);
    }
}

function play($index, $player) {
    if ($_SESSION['board'][$index] !== '-' || !$_SESSION['gameActive']) {
        return;
    }

    $_SESSION['board'][$index] = $player;
    if (checkWinner()) {
        $_SESSION['gameActive'] = false;
        echo json_encode(['winner' => true]);
    } elseif (isDraw()) {
        $_SESSION['gameActive'] = false;
        echo json_encode(['draw' => true]);
    } else {
        $_SESSION['currentPlayer'] = $player === 'X' ? 'O' : 'X';
        echo json_encode(['success' => true]);
    }
}

function resetGame() {
    $_SESSION['board'] = array_fill(0, 9, '-');
    $_SESSION['currentPlayer'] = 'X';
    $_SESSION['gameActive'] = true;
}

function checkWinner() {
    $winningConditions = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8],
        [0, 4, 8], [2, 4, 6]
    ];

    foreach ($winningConditions as $condition) {
        [$a, $b, $c] = $condition;
        if ($_SESSION['board'][$a] !== '-' &&
            $_SESSION['board'][$a] === $_SESSION['board'][$b] &&
            $_SESSION['board'][$a] === $_SESSION['board'][$c]) {
            return true;
        }
    }

    return false;
}

function isDraw() {
    return !in_array('-', $_SESSION['board']);
}