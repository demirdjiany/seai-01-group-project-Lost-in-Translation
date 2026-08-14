import { langMeta } from "./languages.js";
import { sleep, mangleTier } from "./utils.js";

const STEP_DELAY_MS = 1100;

export class RevealPlayer {
  constructor({ trackEl, summaryEl, seedEl, finalEl, mangleEl, playerScoreEl, skipBtn }) {
    this.trackEl = trackEl;
    this.summaryEl = summaryEl;
    this.seedEl = seedEl;
    this.finalEl = finalEl;
    this.mangleEl = mangleEl;
    this.playerScoreEl = playerScoreEl;
    this.skipBtn = skipBtn;
    this.skipped = false;
    this.skipBtn.addEventListener("click", () => {
      this.skipped = true;
    });
  }

  async play(chain, mangleScore, playerScore) {
    this.skipped = false;
    this.trackEl.innerHTML = "";
    this.summaryEl.hidden = true;
    const template = document.getElementById("tmpl-reveal-step");

    for (let i = 0; i < chain.length; i++) {
      const step = chain[i];
      const meta = langMeta(step.lang);
      const node = template.content.firstElementChild.cloneNode(true);
      node.querySelector(".reveal-step-flag").textContent = meta.flag;
      node.querySelector(".reveal-step-lang").textContent = meta.name;
      node.querySelector(".reveal-step-text").textContent = step.text;

      const deltaEl = node.querySelector(".reveal-step-delta");
      if (i === 0) {
        deltaEl.textContent = "start";
      } else {
        const delta = step.text.length - chain[i - 1].text.length;
        if (delta > 0) {
          deltaEl.textContent = `+${delta} chars`;
          deltaEl.classList.add("grew");
        } else if (delta < 0) {
          deltaEl.textContent = `${delta} chars`;
          deltaEl.classList.add("shrank");
        } else {
          deltaEl.textContent = "±0 chars";
        }
      }

      this.trackEl.appendChild(node);
      node.offsetHeight; // force a reflow so the transition below actually animates
      node.classList.add("is-visible");

      if (!this.skipped) {
        await sleep(STEP_DELAY_MS);
      }
    }

    this.seedEl.textContent = chain[0].text;
    this.finalEl.textContent = chain[chain.length - 1].text;
    this.mangleEl.textContent = Math.round(mangleScore);
    this.mangleEl.dataset.tier = mangleTier(mangleScore);
    this.playerScoreEl.textContent = Math.round(playerScore);
    this.summaryEl.hidden = false;
  }
}
