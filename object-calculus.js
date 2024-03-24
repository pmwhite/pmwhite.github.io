const fail = (i, message) => {
  throw `<span class="error-location">At character ${i}</span>: ${message}`;
};

const is_letter = c => (c !== undefined && /[a-z]/.test(c));

const skip_whitespace = state => {
  let c = state.s[state.i];
  while (c !== undefined && /\s/.test(c)) {
    state.i += 1;
    c = state.s[state.i];
  }
};

const parse_simple = state => {
  let c = state.s[state.i];
  if (is_letter(c)) {
    let current_name = ``;
    while (state.i < state.s.length) {
      c = state.s[state.i];
      if (is_letter(c)) {
        current_name = current_name.concat(c);
        state.i += 1;
      } else {
        break;
      }
    }
    return current_name;
  } else if (c === `$`) {
    state.i += 1;
    return `$`;
  } else if (c === `{`) {
    state.i += 1;
    skip_whitespace(state);
    let c = state.s[state.i];
    if (c === `}`) {
      state.i += 1;
      return [ `object` ];
    } else {
      let fields = [ `object`, parse_field(state) ];
      skip_whitespace(state);
      while (true) {
        let c = state.s[state.i];
        if (c === `,`) {
          state.i += 1;
          skip_whitespace(state);
          fields.push(parse_field(state));
          skip_whitespace(state);
        } else if (c === `}`) {
          state.i += 1;
          return fields;
        } else {
          fail(state.i, `expected <code>,</code> or <code>}</code>.`);
        }
      }
    }
  } else {
    fail(state.i, `expected name or <code>{</code>`);
  }
};

const parse_field = state => {
  const left = parse_complex(state);
  skip_whitespace(state);
  const c = state.s[state.i];
  if (c === `=`) {
    state.i += 1;
    skip_whitespace(state);
    const right = parse_complex(state);
    return [ `field`, left, right ];
  } else {
    fail(state.i, `expected <code>=</code>.`);
  }
};

const parse_complex = state => {
  let result = parse_simple(state);
  while (true) {
    const old_i = state.i;
    skip_whitespace(state);
    let c = state.s[state.i];
    if (c === `&`) {
      state.i += 1;
      skip_whitespace(state);
      result = [ `conjunction`, result, parse_simple(state) ];
    } else if (c === `.`) {
      state.i += 1;
      skip_whitespace(state);
      result = [ `access`, result, parse_simple(state) ];
    } else {
      // Restoring the index helps this function follow the convention of only
      // consuming characters that it uses, but it means that we might end up
      // skipping whitespace a lot more than necessary.
      state.i = old_i;
      break;
    }
  }
  return result;
};

const parse_object = string => {
  let state = { s: string, i: 0 };
  let result = parse_complex(state);
  if (state.i < state.s.length) {
    fail(state.i, `finished parsing before end of input.`);
  } else {
    return result;
  }
};

const fmt_string = (fmt, string) => { fmt.buf.push(string); };

const fmt_iter = (fmt, object) => {
  if (typeof object === `string`) {
    fmt_string(fmt, object);
  } else if (object.length === 1) {
    fmt_string(fmt, `{}`);
  } else {
    fmt_string(fmt, `{ `);
    for (let i = 1; i < object.length; i += 1) {
      if (i > 1) {
        fmt_string(fmt, `, `);
      }
      fmt_iter(fmt, object[i][1]);
      fmt_string(fmt, ` = `);
      fmt_iter(fmt, object[i][2]);
    }
    fmt_string(fmt, ` }`);
  }
};

const fmt_object = object => {
  const fmt = { buf: [] };
  fmt_iter(fmt, object);
  let string = fmt.buf.join(``);
  const lines = [];
  while (string.length >= 70) {
    lines.push(string.substring(0, 70));
    string = string.substring(70);
  }
  if (string.length > 0) {
    lines.push(string);
  }
  return lines.join(`\n`);
};

const sandbox_input = document.getElementById(`sandbox-input`);
const sandbox_message = document.getElementById(`sandbox-message`);
const update_message = () => {
  if (sandbox_input.value === ``) {
    sandbox_message.innerHTML = ``;
  } else {
    try {
      parse_object(sandbox_input.value);
      sandbox_message.innerHTML = `Valid expression`;
    } catch(exn) {
      sandbox_message.innerHTML = exn;
    }
  }
  sandbox_input.parentNode.dataset.replicatedValue = sandbox_input.value;
};
sandbox_input.addEventListener(`input`, update_message);
update_message();

for (const example of document.getElementsByClassName(`example`)) {
  const text = example.innerHTML.replace(/&amp;/g, `&`);
  try {
    parse_object(text);
  } catch(exn) {
    example.innerHTML += `<hr>`;
    example.innerHTML += exn;
  }
}
