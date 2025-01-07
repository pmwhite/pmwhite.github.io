const solution = document.getElementById('crossword-solution');
const crossword_reveal_button = document.getElementById('crossword-reveal-button');

const reveal_crossword_solution = () => {
  solution.classList.remove("crossword-hidden");
  crossword_reveal_button.remove();
};
