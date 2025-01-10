const solution = document.getElementById('crossword-solution');
const crossword_reveal_button = document.getElementById('crossword-reveal-button');

const reveal_crossword_solution = () => {
  solution.classList.remove("crossword-hidden");
  crossword_reveal_button.remove();
};

let horizontal_order = [];
for (let r = 0; r < 10; r++) {
  for (let c = 0; c < 10; c++) {
    let square = document.getElementById(`r${r}c${c}`);
    if (square !== null) {
      horizontal_order.push(square);
    }
  }
}

let vertical_order = [];
for (let c = 0; c < 10; c++) {
  for (let r = 0; r < 10; r++) {
    let square = document.getElementById(`r${r}c${c}`);
    if (square !== null) {
      vertical_order.push(square);
    }
  }
}

let selected_square = horizontal_order[0];
let on_deck_square = horizontal_order[1];
let current_direction = 'horizontal';

const set_selected_square = (selected, on_deck) => {
  selected_square.classList.remove('crossword-selected-square');
  selected_square = selected;
  selected_square.classList.add('crossword-selected-square');

  on_deck_square.classList.remove('crossword-on-deck-square');
  on_deck_square = on_deck;
  on_deck_square.classList.add('crossword-on-deck-square');
};

set_selected_square(horizontal_order[0], horizontal_order[1]);

const move_forward_horizontally = () => {
  let horizontal_index = horizontal_order.indexOf(selected_square);
  let current_index = (horizontal_index + 1) % horizontal_order.length;
  let on_deck_index = (horizontal_index + 2) % horizontal_order.length;
  set_selected_square(horizontal_order[current_index], horizontal_order[on_deck_index]);
  current_direction = 'horizontal';
};

const move_backward_horizontally = () => {
  let horizontal_index = horizontal_order.indexOf(selected_square);
  let current_index = (horizontal_index - 1 + horizontal_order.length) % horizontal_order.length;
  let on_deck_index = horizontal_index;
  set_selected_square(horizontal_order[current_index], horizontal_order[on_deck_index]);
  current_direction = 'horizontal';
};

const move_forward_vertically = () => {
  let vertical_index = vertical_order.indexOf(selected_square);
  let current_index = (vertical_index + 1) % vertical_order.length;
  let on_deck_index = (vertical_index + 2) % vertical_order.length;
  set_selected_square(vertical_order[current_index], vertical_order[on_deck_index]);
  current_direction = 'vertical';
};

const move_backward_vertically = () => {
  let vertical_index = vertical_order.indexOf(selected_square);
  let current_index = (vertical_index - 1 + vertical_order.length) % vertical_order.length;
  let on_deck_index = vertical_index;
  set_selected_square(vertical_order[current_index], vertical_order[on_deck_index]);
  current_direction = 'vertical';
};

const set_selected_square_text = text => {
  let text_element = selected_square.querySelector('span.crossword-open-text');
  if (text_element === null) {
    text_element = document.createElement('span');
    text_element.classList.add('crossword-open-text');
    selected_square.appendChild(text_element);
  }
  text_element.innerHTML = text;
};

const handle_key = key => {
  switch (key) {
    case 'ArrowRight':
      move_forward_horizontally();
      break;
    case 'ArrowLeft':
      move_backward_horizontally();
      break;
    case 'ArrowDown':
      move_forward_vertically();
      break;
    case 'ArrowUp':
      move_backward_vertically();
      break;
    case 'Backspace':
      if (current_direction === 'horizontal') {
        move_backward_horizontally();
      } else if (current_direction === 'vertical') {
        move_backward_vertically();
      }
      set_selected_square_text('');
      break;
    case 'a':
    case 'A':
    case 'b':
    case 'B':
    case 'c':
    case 'C':
    case 'd':
    case 'D':
    case 'e':
    case 'E':
    case 'f':
    case 'F':
    case 'g':
    case 'G':
    case 'h':
    case 'H':
    case 'i':
    case 'I':
    case 'j':
    case 'J':
    case 'k':
    case 'K':
    case 'l':
    case 'L':
    case 'm':
    case 'M':
    case 'n':
    case 'N':
    case 'o':
    case 'O':
    case 'p':
    case 'P':
    case 'q':
    case 'Q':
    case 'r':
    case 'R':
    case 's':
    case 'S':
    case 't':
    case 'T':
    case 'u':
    case 'U':
    case 'v':
    case 'V':
    case 'w':
    case 'W':
    case 'x':
    case 'X':
    case 'y':
    case 'Y':
    case 'z':
    case 'Z':
      set_selected_square_text(key.toUpperCase());
      if (current_direction === 'horizontal') {
        move_forward_horizontally();
      } else if (current_direction === 'vertical') {
        move_forward_vertically();
      }
      break;
  }
}

document.addEventListener('keydown', event => handle_key(event.key));

for (const square of horizontal_order) {
  square.classList.add('cursor-pointer');
  square.addEventListener('click', () => {
    if (square === selected_square) {
      if (current_direction === 'horizontal') {
        current_direction = 'vertical';
      } else if (current_direction === 'vertical') {
        current_direction = 'horizontal';
      }
    }
    if (current_direction === 'vertical') {
      let current_index = vertical_order.indexOf(square);
      let on_deck_index = (current_index + 1) % vertical_order.length;
      set_selected_square(square, vertical_order[on_deck_index]);
    } else if (current_direction === 'horizontal') {
      let current_index = horizontal_order.indexOf(square);
      let on_deck_index = (current_index + 1) % horizontal_order.length;
      set_selected_square(square, horizontal_order[on_deck_index]);
    }
  });
}

for (let key of document.querySelectorAll('.crossword-keyboard>div')) {
  key.addEventListener('click', () => {
    handle_key(key.innerHTML);
  });
}
