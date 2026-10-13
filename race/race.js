// The racing game on a canvas that fills the screen, on the block game's
// browser implementation of X11, GLX, and OpenGL (./platform.js).
import { instantiate, runResumable, MemFS } from "./l8-runtime.js";
import { createPlatform } from "./platform.js";

const canvas = document.getElementById("screen");
const overlay = document.getElementById("overlay");
const message = document.getElementById("message");
const show = (html) => {
  message.innerHTML = html;
  overlay.hidden = false;
};

for (const name of ["gesturestart", "gesturechange", "dblclick"]) {
  document.addEventListener(name, (e) => e.preventDefault(), { passive: false });
}
for (const name of ["touchstart", "touchmove"]) {
  document.addEventListener(name, (e) => {
    if (!e.target.closest?.("a, button, .order")) e.preventDefault();
  }, { passive: false });
}
const touch = matchMedia("(pointer: coarse)").matches || navigator.maxTouchPoints > 0;
if (touch) document.body.classList.add("touch");

// ---- the others ----
// The room: everyone on the track now (see web/race-room/worker.js). The
// game says where its car is, and that goes to the room; the room says
// where the others' cars are, and that goes to the game, as key events
// whose key syms are numbers packed together (see net_key, in
// programs/race/net.l8, for how). The page keeps the list of who is here,
// and in a race, of who is ahead.
const NET_A = 2 ** 50, NET_B = 2 ** 51, NET_RACE = 2 ** 52;
const CAR_COLORS = ["#e6382e", "#2b6edb", "#fab847", "#54c2a8"];
const stored = (key) => {
  try {
    return localStorage.getItem(key);
  } catch {
    return null;
  }
};
const store = (key, value) => {
  try {
    localStorage.setItem(key, value);
  } catch {}
};
// A name: twelve letters at most, none of them unseen.
const tidyName = (text) => (text ?? "").replace(/[\p{C}]/gu, "").trim().slice(0, 12).trim();
// A time, as the game writes them: 1:07.42.
const clock = (ms) => {
  const cs = Math.round(ms / 10);
  return `${Math.floor(cs / 6000)}:${String(Math.floor(cs / 100) % 60).padStart(2, "0")}.${String(cs % 100).padStart(2, "0")}`;
};
const room = {
  socket: null,
  platform: null,
  // Me, and the others by their seats: of each, a name, the kind of car,
  // how far it has come (metres from the start line), and its time in the
  // race, if it has finished.
  me: { seat: -1, name: tidyName(stored("race.name")) || `Driver ${10 + Math.floor(Math.random() * 90)}`, kind: 0, far: 0, done: null },
  others: new Map(),
  // Whether a race has been called (it is, until the next).
  racing: false,
  list: document.getElementById("order"),
  call: document.getElementById("race"),
  // Where the rooms are: where the address says (?net=), or beside the
  // page where it is at home, or on the machine the page came from.
  join(platform, params) {
    this.platform = platform;
    const at = params.get("net") ?? (location.hostname.endsWith("trailingwhite.space") ? `wss://${location.host}/race/api` : `ws://${location.hostname}:8787`);
    this.url = `${at}/room/${(params.get("room") ?? "track").toLowerCase().replace(/[^a-z0-9-]/g, "").slice(0, 24) || "track"}`;
    this.call.addEventListener("click", () => this.ask());
    // (A tap on the list: another name.)
    this.list.addEventListener("click", () => {
      const name = tidyName(prompt("Your name on the list", this.me.name));
      if (!name) return;
      this.me.name = name;
      store("race.name", name);
      this.send(`name ${name}`);
      this.show();
    });
    setInterval(() => this.show(), 300);
    this.open();
  },
  open() {
    let socket;
    try {
      socket = new WebSocket(this.url);
    } catch {
      return;
    }
    socket.onopen = () => {
      this.socket = socket;
      this.send(`name ${this.me.name}`);
      this.show();
    };
    socket.onmessage = (e) => this.heard(String(e.data));
    // (No room, or the room lost: the others are gone, and it is tried
    // again in a while.)
    socket.onclose = () => {
      if (this.socket === socket) this.socket = null;
      for (const seat of [...this.others.keys()]) this.gone(seat);
      this.show();
      setTimeout(() => this.open(), 4000);
    };
    socket.onerror = () => socket.close();
  },
  send(text) {
    if (this.socket?.readyState === WebSocket.OPEN) this.socket.send(text);
  },
  // Call a race, for everyone here.
  ask() {
    this.send("race");
    canvas.focus();
  },
  // What the game wrote: where its car is, or that it has finished.
  wrote(line) {
    if (line.startsWith("L8AT ")) {
      const place = line.slice(5);
      const words = place.split(" ");
      this.me.kind = Number(words[6]);
      this.me.far = Number(words[7]);
      this.send(place);
      return true;
    }
    if (line.startsWith("L8DONE ")) {
      this.me.done = Number(line.slice(7));
      this.send(`done ${this.me.done}`);
      this.show();
      return true;
    }
    return false;
  },
  other(seat) {
    if (!this.others.has(seat)) this.others.set(seat, { seat, name: `Driver ${seat + 1}`, kind: 0, far: 0, done: null });
    return this.others.get(seat);
  },
  gone(seat) {
    this.platform.key(2, NET_B + seat * 2 ** 40 + 1);
    this.others.delete(seat);
    this.show();
  },
  heard(text) {
    const words = text.split(" ");
    if (words[0] === "you") {
      this.me.seat = Number(words[1]);
      return;
    }
    if (words[0] === "race") {
      // A race: everyone to the grid, in the order of their seats.
      const seats = [this.me.seat, ...this.others.keys()].sort((a, b) => a - b);
      const place = Math.max(seats.indexOf(this.me.seat), 0);
      const wait = Math.min(Math.max(Math.round(Number(words[1]) / 100), 0), 255);
      const laps = Math.min(Math.max(Number(words[2]) | 0, 1), 15);
      this.platform.key(2, NET_RACE + wait * 256 + laps * 16 + place);
      this.racing = true;
      for (const who of [this.me, ...this.others.values()]) who.done = null;
      this.show();
      return;
    }
    const seat = Number(words[0]);
    if (!(seat >= 0 && seat < 16)) return;
    if (words[1] === "gone") return this.gone(seat);
    if (words[1] === "name") {
      this.other(seat).name = tidyName(words.slice(2).join(" ")) || `Driver ${seat + 1}`;
      return this.show();
    }
    if (words[1] === "done") {
      this.other(seat).done = Number(words[2]);
      return this.show();
    }
    const [x, z, yaw, at, slip, speed, kind, far] = words.slice(1).map(Number);
    if (![x, z, yaw, at, slip, speed, kind, far].every(Number.isFinite)) return;
    const clamp = (v, lo, hi) => Math.min(Math.max(Math.round(v), lo), hi);
    const turn = 2 * Math.PI * 1000;
    const a = NET_A + seat * 2 ** 38 + clamp(kind, 0, 3) * 2 ** 36 + clamp(x + 131072, 0, 262143) * 2 ** 18 + clamp(z + 131072, 0, 262143);
    const b = NET_B + seat * 2 ** 40 + clamp(((((yaw % turn) + turn) % turn) / turn) * 4096, 0, 4095) * 2 ** 28 + clamp(at, 0, 4095) * 2 ** 16
      + clamp(slip / 10 + 64, 0, 127) * 2 ** 9 + clamp(speed / 25, 0, 255) * 2;
    this.platform.key(2, a);
    this.platform.key(2, b);
    const who = this.other(seat);
    who.kind = clamp(kind, 0, 3);
    who.far = far;
  },
  // The list: who is here; and in a race, in order: those who have
  // finished, by their times, then the rest, by how far they have come.
  show() {
    const online = this.socket !== null;
    this.call.hidden = !online;
    const all = online ? [this.me, ...this.others.values()] : [];
    if (this.racing) {
      all.sort((a, b) => (a.done !== null && b.done !== null ? a.done - b.done : a.done !== null ? -1 : b.done !== null ? 1 : b.far - a.far));
    } else all.sort((a, b) => a.seat - b.seat);
    // (Alone, and not racing, there is nothing to list.)
    const rows = all.length > 1 || this.racing ? all : [];
    while (this.list.children.length > rows.length) this.list.lastChild.remove();
    while (this.list.children.length < rows.length) {
      const row = document.createElement("li");
      for (const part of ["place", "dot", "name", "time"]) {
        const span = document.createElement("span");
        span.className = part;
        row.append(span);
      }
      this.list.append(row);
    }
    rows.forEach((who, k) => {
      const row = this.list.children[k];
      row.className = who === this.me ? "me" : "";
      row.children[0].textContent = this.racing ? String(k + 1) : "";
      row.children[1].style.background = CAR_COLORS[who.kind] ?? CAR_COLORS[0];
      row.children[2].textContent = who.name;
      row.children[3].textContent = this.racing && who.done !== null ? clock(who.done) : "";
    });
  },
};

async function main() {
  const params = new URLSearchParams(location.search);
  const module = await WebAssembly.compileStreaming(fetch("race.wasm"));
  const fs = new MemFS({});
  const decoder = new TextDecoder();
  // (What the game writes comes in pieces: a line at a time from here. A
  // line that says where the car is goes to the room.)
  let written = "";
  fs.onOutput = (_fd, data) => {
    written += decoder.decode(data);
    const lines = written.split("\n");
    written = lines.pop();
    for (const line of lines) {
      if (!room.wrote(line) && line.trim()) console.log(line.trimEnd());
    }
  };
  const sys = fs.sys(["race"]);
  // The car last driven here is the one to start in (the game's L8_CAR),
  // unless the address says which.
  const CARS = 4;
  let car = Number(params.get("L8_CAR") ?? stored("race.car") ?? 0);
  if (!(car >= 0 && car < CARS)) car = 0;
  params.set("L8_CAR", String(car));
  // (There is a room to tell where the car is.)
  params.set("L8_NET", "1");
  sys.env = [...params].filter(([k]) => k.startsWith("L8_")).map(([k, v]) => `${k}=${v}`);
  const env = Object.fromEntries([...params].filter(([k]) => k.startsWith("L8_")));
  const platform = createPlatform(canvas, { log: (text) => console.warn(text), checkErrors: params.has("glcheck"), scale: 1, zoom: 0.85, env, pointers: true, maxTall: 2.4, maxPixels: 2.2e6 });
  // The steering panel and the pedals, placed over the game's view where
  // the game reads them: these fractions match programs/race/touch.l8.
  const STEER_LEFT = 0.04, STEER_RIGHT = 0.47, PANEL_TOP = 0.52, PANEL_BOTTOM = 0.8, PEDAL_SPLIT = 0.72;
  const steerPanel = document.querySelector(".panel.steer");
  const brakePanel = document.querySelector(".panel.brake");
  const gasPanel = document.querySelector(".panel.gas");
  const placePads = () => {
    const r = canvas.getBoundingClientRect();
    const place = (el, left, right) => {
      el.style.left = `${r.left + r.width * left}px`;
      el.style.width = `${r.width * (right - left)}px`;
      el.style.top = `${r.top + r.height * PANEL_TOP}px`;
      el.style.height = `${r.height * (PANEL_BOTTOM - PANEL_TOP)}px`;
    };
    place(steerPanel, STEER_LEFT, STEER_RIGHT);
    place(brakePanel, 1 - STEER_RIGHT, PEDAL_SPLIT - 0.01);
    place(gasPanel, PEDAL_SPLIT + 0.01, 1 - STEER_LEFT);
  };
  new ResizeObserver(placePads).observe(canvas);
  addEventListener("resize", placePads);
  placePads();
  window.l8Game = platform;
  room.join(platform, params);
  const { instance, mem } = await instantiate(module, sys, platform.imports);
  platform.attach(instance, mem);
  // Start over: the game's R key. The next car: its keys 1 to 4. (The
  // keyboard's own 1 to 4 and Tab are remembered the same way.)
  const press = (sym) => {
    platform.key(2, sym);
    platform.key(3, sym);
    canvas.focus();
  };
  const choose = (k) => {
    car = k;
    store("race.car", String(car));
  };
  document.getElementById("again").addEventListener("click", () => press(114));
  document.getElementById("car").addEventListener("click", () => {
    choose((car + 1) % CARS);
    press(49 + car);
  });
  canvas.addEventListener("keydown", (e) => {
    if (e.ctrlKey || e.metaKey || e.repeat) return;
    // (Enter calls a race.)
    if (e.key === "Enter") room.ask();
    if (e.key >= "1" && e.key <= String(CARS)) choose(Number(e.key) - 1);
    if (e.key === "Tab") choose((car + 1) % CARS);
  });
  show(touch ? "Tap to start" : "Click to start");
  canvas.addEventListener("focus", () => (overlay.hidden = true));
  canvas.addEventListener("blur", () => {
    if (!touch) show("Click to continue");
  });
  addEventListener("pointerdown", (e) => {
    if (e.target.closest?.("a")) return;
    canvas.focus();
    overlay.hidden = true;
  });
  canvas.focus();
  const status = await runResumable(instance, () => platform.nextFrame());
  show(`The game exited${status ? ` with status ${status}` : ""}.<br><a href="">Play again</a>`);
}

main().catch((e) => {
  console.error(e);
  show(`The game stopped: ${e.message}`);
});
