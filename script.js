const board = document.getElementById('board');
const cells = document.querySelectorAll('.cell');
const message = document.getElementById('message');
const resetButton = document.getElementById('reset');
let currentPlayer = 'X';
let gameActive = true;

// Fetch initial game state from server
fetch('game.php?action=getState')
    .then(response => response.json())
    .then(data => {
        updateBoard(data.board);
        currentPlayer = data.currentPlayer;
        gameActive = data.gameActive;
    })
    .catch(error => console.error('Error fetching initial game state:', error));

cells.forEach(cell => cell.addEventListener('click', handleCellClick));
resetButton.addEventListener('click', resetGame);

function handleCellClick(e) {
    const clickedCellIndex = parseInt(e.target.getAttribute('data-index'));

    if (!gameActive || e.target.innerText !== '') {
        return;
    }

    e.target.innerText = currentPlayer;
    fetch('game.php?action=play', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ index: clickedCellIndex, player: currentPlayer })
    })
    .then(response => response.json())
    .then(data => {
        if (data.winner) {
            message.innerText = `Player ${currentPlayer} wins!`;
            gameActive = false;
        } else if (data.draw) {
            message.innerText = `It's a draw!`;
            gameActive = false;
        } else {
            currentPlayer = currentPlayer === 'X' ? 'O' : 'X';
        }
    })
    .catch(error => console.error('Error during move:', error));
}

function resetGame() {
    fetch('game.php?action=reset')
        .then(response => response.json())
        .then(() => {
            cells.forEach(cell => (cell.innerText = ''));
            message.innerText = '';
            currentPlayer = 'X';
            gameActive = true;
        })
        .catch(error => console.error('Error resetting game:', error));
}

function updateBoard(board) {
    cells.forEach((cell, index) => {
        cell.innerText = board[index] === '-' ? '' : board[index];
    });
}