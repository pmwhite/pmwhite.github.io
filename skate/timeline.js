// What the frames did, second by second, sent with each report of how they
// came (see reportFrames, in skate.js): so that why a device's frames are
// slow, or its picture small, can be read from its reports without asking
// anyone to try anything.
//
// For each second: how many beats the screen gave (its refresh rate, which
// a phone may change as it likes), how many frames the game drew, how many
// were late, the rung and the beats to a frame the pacing was at (see pace.js), whether a finger was down, what
// was showing (r riding, s a sheet, l a place being built, - nothing came:
// the page put away), and the game's own milliseconds a frame. With them:
// the gaps between beats and between frames, counted by the millisecond;
// what the pacing changed, and why, and what else happened, as events; and
// now and then what the graphics chip took over each part of a frame (see
// sampleGpu, in ../game/platform.js), by the rung it was at, and over each
// second's frame so timed. (Named so as not to be taken for the device's
// own words: its `gpu` and `touch`.)

// Seconds kept, the latest; gaps counted to (the last count is of those as
// long or longer); events kept, the first (how many of each were left out
// is told: leftOut); and how often a frame's parts
// are timed while he rides: a frame so timed is late (the chip is waited
// for), so often at first, while there is most to learn, and seldom after.
const SECONDS_MOST = 150;
const GAP_MOST = 50;
const EVENTS_MOST = 60;
const SAMPLE_EVERY = 5000;
const SAMPLES_SOON = 12;
const SAMPLE_LATER = 30000;

const round1 = (x) => Math.round(x * 10) / 10;

export function createTimeline() {
  let seconds = new Map();
  let beatGaps = new Array(GAP_MOST).fill(0);
  let frameGaps = new Array(GAP_MOST).fill(0);
  let events = [];
  let samples = {};
  let leftOut = { seconds: 0, events: 0 };
  let lastBeat = 0;
  let lastSample = 0;
  let kind = 0;
  let fingers = 0;
  const second = (now) => {
    const at = Math.floor(now / 1000);
    let s = seconds.get(at);
    if (!s) {
      s = { beats: 0, frames: 0, late: 0, rung: -1, aim: 0, touch: fingers > 0 ? 1 : 0, state: "-", work: 0, gpu: 0 };
      seconds.set(at, s);
      if (seconds.size > SECONDS_MOST) {
        seconds.delete(seconds.keys().next().value);
        leftOut.seconds++;
      }
    }
    return s;
  };
  const count = (gaps, gap) => gaps[Math.min(GAP_MOST - 1, Math.max(0, Math.floor(gap)))]++;
  addEventListener("pointerdown", () => (fingers++, (second(performance.now()).touch = 1)), { capture: true, passive: true });
  const lifted = () => (fingers = Math.max(0, fingers - 1));
  addEventListener("pointerup", lifted, { capture: true, passive: true });
  addEventListener("pointercancel", lifted, { capture: true, passive: true });
  return {
    // A beat of the screen came (a frame was asked for, drawn or not).
    beat(now) {
      const s = second(now);
      s.beats++;
      if (fingers > 0) s.touch = 1;
      if (lastBeat > 0 && now - lastBeat < 1000) count(beatGaps, now - lastBeat);
      lastBeat = now;
    },
    // A frame is to be drawn, `gap` after the one before: late or not, at
    // this rung and aim, with what is showing, and the game's milliseconds
    // on the frame before.
    frame(now, { gap, late, rung, aim, state, work }) {
      const s = second(now);
      s.frames++;
      if (late) s.late++;
      s.rung = rung;
      s.aim = aim;
      s.state = state;
      s.work += work;
      if (gap < 1000) count(frameGaps, gap);
    },
    // What is showing, when no frames are drawn (a place being built).
    showing(now, state) {
      second(now).state = state;
    },
    // Something happened: when (in seconds of the page's clock, as `from`
    // is), what, and anything else to say of it.
    event(now, what, ...more) {
      if (events.length < EVENTS_MOST) events.push([round1(now / 1000), what, ...more]);
      else leftOut.events++;
    },
    // Whether to time the next frame's parts now, and how ("targets" or
    // "parts", in turn); each time it says to, it is taken as done.
    sampleDue(now) {
      if (now - lastSample < (kind < SAMPLES_SOON ? SAMPLE_EVERY : SAMPLE_LATER)) return null;
      lastSample = now;
      return kind++ % 2 === 0 ? "targets" : "parts";
    },
    // A frame's parts, timed at this rung (see takeGpuSample).
    sampled(now, rung, { kind, drain, base, parts }) {
      const key = `${kind} ${rung}`;
      const k = (samples[key] ??= { n: 0, drain: 0, base: 0, parts: {} });
      k.n++;
      k.drain += drain;
      k.base += base;
      let waited = 0;
      for (const [part, [issue, wait]] of Object.entries(parts)) {
        const p = (k.parts[part] ??= [0, 0]);
        p[0] += issue;
        p[1] += wait;
        waited += wait;
      }
      if (kind === "targets") second(now).gpu = waited;
    },
    // All of it since the last, to be sent; and begun again.
    take(now) {
      const keys = [...seconds.keys()];
      const from = keys.length > 0 ? keys[0] : Math.floor(now / 1000);
      const to = keys.length > 0 ? keys[keys.length - 1] : from - 1;
      const each = [];
      for (let at = from; at <= to; at++) each.push(seconds.get(at) ?? { beats: 0, frames: 0, late: 0, rung: -1, aim: 0, touch: 0, state: "-", work: 0, gpu: 0 });
      const gpu = {};
      for (const [key, k] of Object.entries(samples)) {
        gpu[key] = { n: k.n, drain: round1(k.drain / k.n), base: round1(k.base / k.n), parts: Object.fromEntries(Object.entries(k.parts).map(([p, [issue, wait]]) => [p, [round1(issue / k.n), round1(wait / k.n)]])) };
      }
      const got = {
        // (The first second's, of the page's clock.)
        from,
        hz: each.map((s) => s.beats),
        drawn: each.map((s) => s.frames),
        late: each.map((s) => s.late),
        rungs: each.map((s) => s.rung),
        aims: each.map((s) => s.aim),
        fingers: each.map((s) => s.touch).join(""),
        showing: each.map((s) => s.state).join(""),
        work: each.map((s) => (s.frames > 0 ? round1(s.work / s.frames) : 0)),
        chipMs: each.map((s) => round1(s.gpu)),
        beatGaps,
        frameGaps,
        events,
        chip: gpu,
        leftOut,
      };
      seconds = new Map();
      beatGaps = new Array(GAP_MOST).fill(0);
      frameGaps = new Array(GAP_MOST).fill(0);
      events = [];
      samples = {};
      leftOut = { seconds: 0, events: 0 };
      return got;
    },
  };
}
