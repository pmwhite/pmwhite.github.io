const recipes =
  [
    { 
      name: "salsa",
      ingredients: [
        "tomato",
        "onion",
        "jalapeno pepper",
        "cilantro",
        "lime juice",
        "garlic"
      ]
    }
  ];

const recipe_list = document.getElementById("recipe-list");
const recipe_search_box = document.getElementById("recipe-search-box");

const fuzzy_match = (text, pattern) => {
  if (pattern.length === 0) return true;
  if (text.length === 0) return false;
  let pattern_index = 0;
  let text_index = 0;
  while (text_index < text.length) {
    if (text[text_index] === pattern[pattern_index]) {
      pattern_index++;
      if (pattern_index === pattern.length) {
        return true;
      }
    }
    text_index++;
  }
  return false;
};

const repopulate_recipe_list = () => {
  const query = recipe_search_box.value;
  const matching_recipes = recipes.filter(recipe => fuzzy_match(recipe.name, query));
  recipe_list.replaceChildren();
  for (const recipe of matching_recipes) {
    const item = document.createElement("li");
    const name_span = document.createElement("span");
    name_span.innerHTML = recipe.name;
    item.appendChild(name_span);
    const decrement_button = document.createElement("button");
    decrement_button.innerHTML = "-";
    item.appendChild(decrement_button);
    const amount_span = document.createElement("span");
    amount_span.innerHTML = "0";
    item.appendChild(amount_span);
    const increment_button = document.createElement("button");
    increment_button.innerHTML = "+";
    item.appendChild(increment_button);
    recipe_list.appendChild(item);
  }
};

recipe_search_box.addEventListener('input', () => {
  repopulate_recipe_list();
});

repopulate_recipe_list();
