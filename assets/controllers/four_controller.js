import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["board", "modalContainer", "modalMessage", "resetButton"];
    static values = {
        moveUrl: String
    };

    RED_TURN = 1;
    YELLOW_TURN = 2;

    pieces = [
    0, 0, 0, 0, 0, 0, 0,
    0, 0, 0, 0, 0, 0, 0,
    0, 0, 0, 0, 0, 0, 0,
    0, 0, 0, 0, 0, 0, 0,
    0, 0, 0, 0, 0, 0, 0,
    0, 0, 0, 0, 0, 0, 0,
    ];

    playerTurn = this.RED_TURN;
    hoverColumn = -1;
    animating = false;

    connect()
    {
        this.resetButtonTarget.addEventListener("click", () => {
            location.reload();
        });

        this.createBoard();
    }

    createBoard()
    {
        this.boardTarget.innerHTML = "";

        for (let i = 0; i < 42; i++) {
            let cell = document.createElement("div");
            cell.className = "relative flex shadow-[inset_0_4px_8px_rgba(0,0,0,0.6)] before:absolute before:content-[''] before:inset-0 before:bg-[radial-gradient(circle,transparent_65%,rgba(30,64,175,1)_65%)] before:z-[10] bg-blue-800/10";
            cell.dataset.index = i % 7;
            this.boardTarget.appendChild(cell);

            cell.addEventListener("mouseenter", () => {
                this.onMouseEnteredColumn(i % 7);
            });

            cell.addEventListener("click", () => {
                if (!this.animating) {
                    this.onColumnClicked(i % 7);
                }
            });
        }
    }

    onColumnClicked(column)
    {
        let availableRow = this.pieces.filter((_, index) => index % 7 === column).lastIndexOf(0);
        if (availableRow === -1) {
          // no space in the column
            return;
        }

        this.pieces[(availableRow * 7) + column] = this.playerTurn;
        let boardIndex = (availableRow * 7) + column;

        // Send move to backend
        if (this.hasMoveUrlValue) {
            fetch(this.moveUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    column: column,
                    boardIndex: boardIndex,
                    playerNumber: this.playerTurn,
                    allPieces: this.pieces // Send all pieces as requested
                }),
            });
        }

        let cell = this.boardTarget.children[boardIndex];

        let piece = document.createElement("div");
        let pieceClasses = "rounded-full grow m-[10%] z-20 shadow-[inset_0_4px_6px_rgba(0,0,0,0.4),_inset_0_-4px_6px_rgba(255,255,255,0.2),_0_6px_10px_rgba(0,0,0,0.5)]";
        if (this.playerTurn === this.RED_TURN) {
            pieceClasses += " bg-gradient-to-br from-red-600 to-red-800";
        } else {
            pieceClasses += " bg-gradient-to-br from-yellow-300 to-yellow-500";
        }
        piece.className = pieceClasses;
        piece.dataset.placed = true;
        piece.dataset.player = this.playerTurn;
        cell.appendChild(piece);

        let unplacedPiece = document.querySelector("[data-placed='false']");
        let unplacedY = unplacedPiece.getBoundingClientRect().y;
        let placedY = piece.getBoundingClientRect().y;
        let yDiff = unplacedY - placedY;

        this.animating = true;
        this.removeUnplacedPiece();
        let animation = piece.animate(
            [
            { transform: `translateY(${yDiff}px)`, offset: 0 },
            { transform: `translateY(0px)`, offset: 0.6 },
            { transform: `translateY(${yDiff / 20}px)`, offset: 0.8 },
            { transform: `translateY(0px)`, offset: 0.95 }
            ],
            {
                duration: 1200,
                easing: "linear",
                iterations: 1
            }
        );
        animation.addEventListener('finish', () => this.checkGameWinOrDraw());
    }

    checkGameWinOrDraw()
    {
        this.animating = false;

      // check if game is a draw
        if (!this.pieces.includes(0)) {
          // game is a draw
            this.modalContainerTarget.classList.remove('hidden');
            this.modalMessageTarget.textContent = "Draw";
            return;
        }

        // check if the current player has won
        if (this.hasPlayerWon(this.playerTurn, this.pieces)) {
          // current player has won
            this.modalContainerTarget.classList.remove('hidden');
            this.modalMessageTarget.textContent = `${this.playerTurn === this.RED_TURN ? "rot" : "gelb"} hat gewonnen!`;
            this.modalMessageTarget.dataset.winner = this.playerTurn;
            return; // Stop further execution if game is won
        }

        this.playerTurn = (this.playerTurn === this.RED_TURN) ? this.YELLOW_TURN : this.RED_TURN;

      // update hovering piece
        this.updateHover();


    }

    updateHover()
    {
        this.removeUnplacedPiece();

      // add piece
        if (this.pieces[this.hoverColumn] === 0) {
            let cell = this.boardTarget.children[this.hoverColumn];
            let piece = document.createElement("div");
            let pieceClasses = "rounded-full grow m-[10%] -translate-y-[110%] z-20 shadow-[inset_0_4px_6px_rgba(0,0,0,0.4),_inset_0_-4px_6px_rgba(255,255,255,0.2),0_10px_15px_rgba(0,0,0,0.4)] transition-transform duration-200";
            if (this.playerTurn === this.RED_TURN) {
                pieceClasses += " bg-gradient-to-br from-red-600/60 to-red-800/60";
            } else {
                pieceClasses += " bg-gradient-to-br from-yellow-300/60 to-yellow-500/60";
            }
            piece.className = pieceClasses;
            piece.dataset.placed = false;
            piece.dataset.player = this.playerTurn;
            cell.appendChild(piece);
        }
    }

    removeUnplacedPiece()
    {
        let unplacedPiece = document.querySelector("[data-placed='false']");
        if (unplacedPiece) {
            unplacedPiece.parentElement.removeChild(unplacedPiece);
        }
    }

    onMouseEnteredColumn(column)
    {
        this.hoverColumn = column;
        if (!this.animating) {
            this.updateHover();
        }
    }

    hasPlayerWon(playerTurn, pieces)
    {
        for (let index = 0; index < 42; index++) {
          // check horiztonal win starting at index
            if (
            index % 7 < 4 &&
            pieces[index] === playerTurn &&
            pieces[index + 1] === playerTurn &&
            pieces[index + 2] === playerTurn &&
            pieces[index + 3] === playerTurn
            ) {
                return true;
            }

          // check vertical win starting at index
            if (
            index < 21 &&
            pieces[index] === playerTurn &&
            pieces[index + 7] === playerTurn &&
            pieces[index + 14] === playerTurn &&
            pieces[index + 21] === playerTurn
            ) {
                return true;
            }

          // check diagonal win starting at index
            if (
            index % 7 < 4 &&
            index < 18 &&
            pieces[index] === playerTurn &&
            pieces[index + 8] === playerTurn &&
            pieces[index + 16] === playerTurn &&
            pieces[index + 24] === playerTurn
            ) {
                return true;
            }

          // check diagonal win (other direction) starting at index
            if (
            index % 7 >= 3 &&
            index < 21 &&
            pieces[index] === playerTurn &&
            pieces[index + 6] === playerTurn &&
            pieces[index + 12] === playerTurn &&
            pieces[index + 18] === playerTurn
            ) {
                return true;
            }
        }

        console.log('no winner');
        return false;
    }
}
