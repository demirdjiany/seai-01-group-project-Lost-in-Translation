import { Api, ApiError } from "./api.js";
import { langMeta } from "./languages.js";
import { formatClock, sleep, burstConfetti } from "./utils.js";
import { RevealPlayer } from "./reveal.js";

const POLL_MS = 2000;
const TICK_MS = 250;
const FULL_CIRCUMFERENCE = 119.4;

const PANELS = ["idle", "generating", "open", "revealed"];

export class GameController {
  constructor(root) {
    this.root = root;
    this.banner = root.querySelector("#status-banner");

    this.panels = {};
    for (const name of PANELS) {
      this.panels[name] = root.querySelector(`#state-${name}`);
    }

    this.timerValue = root.querySelector("#timer-value");
    this.timerRing = root.querySelector("#timer-ring-fill");
    this.chainStrip = root.querySelector("#chain-strip");
    this.puzzleText = root.querySelector("#puzzle-text");
    this.guessForm = root.querySelector("#guess-form");
    this.guessInput = root.querySelector("#guess-input");
    this.guessSubmit = root.querySelector("#guess-submit");
    this.guessError = root.querySelector("#guess-error");
    this.guessFeedback = root.querySelector("#guess-feedback");
    this.hintBtn = root.querySelector("#btn-hint");
    this.hintCount = root.querySelector("#hint-count");
    this.hintList = root.querySelector("#hint-list");
    this.hintTemplate = document.getElementById("tmpl-hint-item");
    this.btnNewRound = root.querySelector("#btn-new-round");
    this.btnPlayAgain = root.querySelector("#btn-play-again");

    this.reveal = new RevealPlayer({
      trackEl: root.querySelector("#reveal-track"),
      summaryEl: root.querySelector("#reveal-summary"),
      seedEl: root.querySelector("#summary-seed"),
      finalEl: root.querySelector("#summary-final"),
      mangleEl: root.querySelector("#summary-mangle"),
      playerScoreEl: root.querySelector("#summary-player-score"),
      skipBtn: root.querySelector("#btn-skip-reveal"),
    });

    this.currentRoundId = null;
    this.currentStatus = null;
    this.clockOffset = 0;
    this.closesAt = null;
    this.opensAt = null;
    this.guessLocked = false;
    this.hintsRendered = 0;
    this.polling = false;
    this.playingReveal = false;
    this.playerScore = 0;
    this.creatingRound = false;

    this._bindEvents();
  }

  start() {
    this.polling = true;
    this._pollLoop();
    this._tickLoop();
  }

  stop() {
    this.polling = false;
  }

  _bindEvents() {
    this.guessForm.addEventListener("submit", (e) => {
      e.preventDefault();
      this._submitGuess();
    });

    this.hintBtn.addEventListener("click", () => this._requestHint());

    this.btnNewRound.addEventListener("click", () => this._newRound());
    this.btnPlayAgain.addEventListener("click", () => this._newRound());
  }

  async _pollLoop() {
    while (this.polling) {
      try {
        const state = await Api.getState();
        if (!this.creatingRound) {
          this._handleState(state);
        }
        this._hideBanner();
      } catch (err) {
        this._showBanner(
          err instanceof ApiError ? err.message : "Couldn't reach the server.",
        );
      }
      await sleep(POLL_MS);
    }
  }

  _tickLoop() {
    if (!this.polling) return;
    this._renderTimer();
    setTimeout(() => this._tickLoop(), TICK_MS);
  }

  _handleState(state) {
    const roundChanged = state.round_id !== this.currentRoundId;
    this.currentRoundId = state.round_id;
    this.currentStatus = state.status;
    this.clockOffset = state.server_time - Date.now() / 1000;
    this.opensAt = state.opens_at;
    this.closesAt = state.closes_at;

    if (roundChanged) {
      this._resetRoundUi();
    }

    this._showPanel(state.status);

    if (state.status === "open") {
      this.puzzleText.textContent = state.puzzle || "";
      this._renderChainStrip(state.chain_languages || []);
      this._renderHints(state.hints || []);
      this.hintCount.textContent = `${(state.hints || []).length} used`;
    }

    if (state.status === "revealed") {
      this._maybeStartReveal(state);
    }
  }

  _maybeStartReveal(state) {
    if (this.playingReveal || this.revealDone) return;
    this.playingReveal = true;
    this.btnPlayAgain.hidden = true;
    this.reveal.play(state.chain, state.mangle_score, this.playerScore).then(() => {
      this.playingReveal = false;
      this.revealDone = true;
      this.btnPlayAgain.hidden = false;
    });
  }

  _resetRoundUi() {
    this.guessLocked = false;
    this.playerScore = 0;
    this.revealDone = false;
    this.guessForm.reset();
    this.guessFeedback.hidden = true;
    this.guessError.hidden = true;
    this.hintList.innerHTML = "";
    this.hintsRendered = 0;
    this.guessInput.disabled = false;
    this.guessSubmit.disabled = false;
    this.hintBtn.disabled = false;
    this.hintBtn.hidden = false;
  }

  _showPanel(status) {
    for (const name of PANELS) {
      this.panels[name].hidden = name !== status;
    }
  }

  _renderChainStrip(codes) {
    if (this.chainStrip.childElementCount === codes.length) return;
    this.chainStrip.innerHTML = "";
    for (const code of codes) {
      const meta = langMeta(code);
      const pill = document.createElement("span");
      pill.className = "chain-pill";
      pill.textContent = `${meta.flag} ${code}`;
      this.chainStrip.appendChild(pill);
    }
  }

  _renderHints(hints) {
    if (hints.length === this.hintsRendered) return;
    this.hintList.innerHTML = "";
    for (const hint of hints) {
      const meta = langMeta(hint.lang);
      const node = this.hintTemplate.content.firstElementChild.cloneNode(true);
      node.querySelector(".hint-flag").textContent = meta.flag;
      node.querySelector(".hint-lang").textContent = hint.lang;
      node.querySelector(".hint-text").textContent = hint.text;
      this.hintList.appendChild(node);
    }
    this.hintsRendered = hints.length;
  }

  _renderTimer() {
    if (this.currentStatus !== "open" || this.closesAt == null) {
      return;
    }
    const now = Date.now() / 1000 + this.clockOffset;
    const remaining = this.closesAt - now;
    const total = this.closesAt - this.opensAt;
    this.timerValue.textContent = formatClock(remaining);

    const fraction = total > 0 ? Math.max(0, Math.min(1, remaining / total)) : 0;
    this.timerRing.style.strokeDashoffset = String(FULL_CIRCUMFERENCE * (1 - fraction));
    this.timerRing.style.stroke = fraction < 0.2 ? "var(--bad)" : "var(--accent)";
  }

  _validateGuess(raw) {
    const value = raw.trim();
    if (!value) return "Type something first.";
    if (value.length > 120) return "Keep it under 120 characters.";
    return null;
  }

  async _submitGuess() {
    const raw = this.guessInput.value;
    const error = this._validateGuess(raw);
    this.guessError.hidden = !error;
    if (error) {
      this.guessError.textContent = error;
      return;
    }

    this.guessSubmit.disabled = true;
    this.guessInput.disabled = true;
    try {
      const res = await Api.submitGuess(this.currentRoundId, raw.trim());
      if (!res.ok) {
        this._renderGuessFeedback("wrong", res.error || "Couldn't submit that guess.");
        return;
      }
      this._renderGuessFeedback(res.result, null, res.similarity, res.score);
      if (res.result === "correct") {
        this.playerScore = Number(res.score || 0);
        this.guessLocked = true;
        this.hintBtn.disabled = true;
        burstConfetti(this.guessSubmit);

        const state = await Api.getState();
        this._handleState(state);
      }
    } catch (err) {
      this._showBanner(err instanceof ApiError ? err.message : "Couldn't submit that guess.");
    } finally {
      this.guessSubmit.disabled = this.guessLocked;
      this.guessInput.disabled = this.guessLocked;
    }
  }

  _renderGuessFeedback(result, customMessage, similarity, score) {
    this.guessFeedback.hidden = false;
    this.guessFeedback.className = `guess-feedback ${result}`;
    if (customMessage) {
      this.guessFeedback.textContent = customMessage;
      return;
    }
    const pct = Math.round((similarity ?? 0) * 100);
    if (result === "correct") {
      this.guessFeedback.textContent = `Correct! ${pct}% match — ${score} points.`;
    } else if (result === "close") {
      this.guessFeedback.textContent = `Close — ${pct}% match. Try again.`;
    } else {
      this.guessFeedback.textContent = `Not quite — ${pct}% match.`;
    }
  }

  async _requestHint() {
    this.hintBtn.disabled = true;
    try {
      const res = await Api.requestHint(this.currentRoundId);
      if (!res.ok) {
        if (res.error === "no_more_hints") {
          this.hintBtn.hidden = true;
        } else {
          this._showBanner("Couldn't fetch a hint right now.");
          this.hintBtn.disabled = false;
        }
        return;
      }
      this.hintBtn.hidden = true;
    } catch (err) {
      this._showBanner(err instanceof ApiError ? err.message : "Couldn't fetch a hint right now.");
      this.hintBtn.disabled = false;
    }
  }

  async _newRound() {
    if (this.creatingRound) return;

    this.creatingRound = true;
    this.currentStatus = "generating";
    this._showPanel("generating");
    this.btnNewRound.disabled = true;
    this.btnPlayAgain.disabled = true;

    try {
      const response = await Api.newRound();

      if (!response.ok) {
        throw new ApiError(response.error || "Couldn't start a new round.", "round");
      }

      const state = await Api.getState();
      this._handleState(state);
    } catch (err) {
      this._showPanel("idle");
      this._showBanner(err instanceof ApiError ? err.message : "Couldn't start a new round.");
    } finally {
      this.creatingRound = false;
      this.btnNewRound.disabled = false;
      this.btnPlayAgain.disabled = false;
    }
  }

  _showBanner(message) {
    this.banner.hidden = false;
    this.banner.textContent = message;
    this.banner.classList.add("is-error");
  }

  _hideBanner() {
    if (!this.banner.classList.contains("is-error")) return;
    this.banner.hidden = true;
    this.banner.classList.remove("is-error");
  }
}
